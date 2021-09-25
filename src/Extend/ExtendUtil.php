<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Extend;

use ScssPhp\ScssPhp\Ast\Selector\Combinator;
use ScssPhp\ScssPhp\Ast\Selector\ComplexSelector;
use ScssPhp\ScssPhp\Ast\Selector\CompoundSelector;
use ScssPhp\ScssPhp\Ast\Selector\IDSelector;
use ScssPhp\ScssPhp\Ast\Selector\PlaceholderSelector;
use ScssPhp\ScssPhp\Ast\Selector\PseudoSelector;
use ScssPhp\ScssPhp\Ast\Selector\SelectorList;
use ScssPhp\ScssPhp\Ast\Selector\SimpleSelector;
use ScssPhp\ScssPhp\Ast\Selector\TypeSelector;
use ScssPhp\ScssPhp\Util\EquatableUtil;

/**
 * @internal
 */
final class ExtendUtil
{
    /**
     * Names of pseudo selectors that take selectors as arguments, and that are
     * subselectors of their arguments.
     *
     * For example, `.foo` is a superselector of `:matches(.foo)`.
     */
    private const SUBSELECTOR_PSEUDOS = [
        'is',
        'matches',
        'any',
        'nth-child',
        'nth-last-child',
    ];

    /**
     * Returns whether $list1 is a superselector of $list2.
     *
     * That is, whether $list1 matches every element that $list2 matches, as well
     * as possibly additional elements.
     *
     * @param list<ComplexSelector> $list1
     * @param list<ComplexSelector> $list2
     *
     * @return bool
     */
    public static function listIsSuperselector(array $list1, array $list2): bool
    {
        foreach ($list2 as $complex1) {
            foreach ($list1 as $complex2) {
                if ($complex2->isSuperselector($complex1)) {
                    continue 2;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Like {@see complexIsSuperselector}, but compares $complex1 and $complex2 as
     * though they shared an implicit base {@see SimpleSelector}.
     *
     * For example, `B` is not normally a superselector of `B A`, since it doesn't
     * match elements that match `A`. However, it *is* a parent superselector,
     * since `B X` is a superselector of `B A X`.
     *
     * @param list<CompoundSelector|string> $complex1
     * @param list<CompoundSelector|string> $complex2
     *
     * @return bool
     *
     * @phpstan-param list<CompoundSelector|Combinator::*> $complex1
     * @phpstan-param list<CompoundSelector|Combinator::*> $complex2
     */
    public static function complexIsParentSuperselector(array $complex1, array $complex2): bool
    {
        if (\is_string($complex1[0])) {
            return false;
        }
        if (\is_string($complex2[0])) {
            return false;
        }

        if (\count($complex1) > \count($complex2)) {
            return false;
        }

        $base = new CompoundSelector([new PlaceholderSelector('<temp>')]);
        $complex1[] = $base;
        $complex2[] = $base;

        return self::complexIsSuperselector($complex1, $complex2);
    }

    /**
     * Returns whether $complex1 is a superselector of $complex2.
     *
     * That is, whether $complex1 matches every element that $complex2 matches, as well
     * as possibly additional elements.
     *
     * @param list<CompoundSelector|string> $complex1
     * @param list<CompoundSelector|string> $complex2
     *
     * @return bool
     *
     * @phpstan-param list<CompoundSelector|Combinator::*> $complex1
     * @phpstan-param list<CompoundSelector|Combinator::*> $complex2
     */
    public static function complexIsSuperselector(array $complex1, array $complex2): bool
    {
        // Selectors with trailing operators are neither superselectors nor
        // subselectors.
        if (\is_string($complex1[\count($complex1) - 1])) {
            return false;
        }
        if (\is_string($complex2[\count($complex2) - 1])) {
            return false;
        }

        $i1 = 0;
        $i2 = 0;

        while (true) {
            $remaining1 = \count($complex1) - $i1;
            $remaining2 = \count($complex2) - $i2;

            if ($remaining1 === 0 || $remaining2 === 0) {
                return false;
            }

            // More complex selectors are never superselectors of less complex ones.
            if ($remaining1 > $remaining2) {
                return false;
            }

            // Selectors with leading operators are neither superselectors nor
            // subselectors.
            if (\is_string($complex1[$i1])) {
                return false;
            }
            if (\is_string($complex2[$i2])) {
                return false;
            }

            $compound1 = $complex1[$i1];

            if ($remaining1 === 1) {
                return self::compoundIsSuperselector($compound1, $complex2[\count($complex2) - 1], array_slice($complex2, $i2, -1));
            }

            // Find the first index where `complex2.sublist(i2, afterSuperselector)` is
            // a subselector of $compound1. We stop before the superselector would
            // encompass all of $complex2 because we know $complex1 has more than one
            // element, and consuming all of $complex2 wouldn't leave anything for the
            // rest of $complex1 to match.
            $afterSuperselector = $i2 + 1;
            for (; $afterSuperselector < \count($complex2); $afterSuperselector++) {
                $compound2 = $complex2[$afterSuperselector - 1];

                if ($compound2 instanceof CompoundSelector) {
                    if (self::compoundIsSuperselector($compound1, $compound2, array_slice($complex2, $i2 + 1, max(0, ($afterSuperselector - 1) - ($i2 + 1))))) {
                        break;
                    }
                }
            }

            if ($afterSuperselector === \count($complex2)) {
                return false;
            }

            $combinator1 = $complex1[$i1 + 1];
            $combinator2 = $complex2[$afterSuperselector];

            if (\is_string($combinator1)) { // Combinator
                if (!\is_string($combinator2)) {
                    return false;
                }

                // `.foo ~ .bar` is a superselector of `.foo + .bar`, but otherwise the
                // combinators must match.
                if ($combinator1 === Combinator::FOLLOWING_SIBLING) {
                    if ($combinator2 === Combinator::CHILD) {
                        return false;
                    }
                } elseif ($combinator1 !== $combinator2) {
                    return false;
                }

                // `.foo > .baz` is not a superselector of `.foo > .bar > .baz` or
                // `.foo > .bar .baz`, despite the fact that `.baz` is a superselector of
                // `.bar > .baz` and `.bar .baz`. Same goes for `+` and `~`.
                if ($remaining1 === 3 && $remaining2 > 3) {
                    return false;
                }

                $i1 += 2;
                $i2 = $afterSuperselector + 1;
            } elseif (\is_string($combinator2)) {
                if ($combinator2 !== Combinator::CHILD) {
                    return false;
                }

                $i1++;
                $i2 = $afterSuperselector + 1;
            } else {
                $i1++;
                $i2 = $afterSuperselector;
            }
        }
    }

    /**
     * Returns whether $compound1 is a superselector of $compound2.
     *
     * That is, whether $compound1 matches every element that $compound2 matches, as well
     * as possibly additional elements.
     *
     * If $parents is passed, it represents the parents of $compound2. This is
     * relevant for pseudo selectors with selector arguments, where we may need to
     * know if the parent selectors in the selector argument match $parents.
     *
     * @param CompoundSelector                   $compound1
     * @param CompoundSelector                   $compound2
     * @param list<CompoundSelector|string>|null $parents
     *
     * @return bool
     *
     * @phpstan-param list<CompoundSelector|Combinator::*>|null $parents
     */
    public static function compoundIsSuperselector(CompoundSelector $compound1, CompoundSelector $compound2, ?array $parents = null): bool
    {
        // Every selector in `$compound1->getComponents()` must have a matching selector in
        // `$compound2->getComponents()`.
        foreach ($compound1->getComponents() as $simple1) {
            if ($simple1 instanceof PseudoSelector && $simple1->getSelector() !== null) {
                if (!self::selectorPseudoIsSuperselector($simple1, $compound2, $parents)) {
                    return false;
                }
            } elseif (!self::simpleIsSuperselectorOfCompound($simple1, $compound2)) {
                return false;
            }
        }

        // $compound1 can't be a superselector of a selector with non-selector
        // pseudo-elements that $compound2 doesn't share.
        foreach ($compound2->getComponents() as $simple2) {
            if ($simple2 instanceof PseudoSelector && $simple2->isElement() && $simple2->getSelector() === null && !self::simpleIsSuperselectorOfCompound($simple2, $compound1)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns whether $simple is a superselector of $compound.
     *
     * That is, whether $simple matches every element that $compound matches, as
     * well as possibly additional elements.
     */
    private static function simpleIsSuperselectorOfCompound(SimpleSelector $simple, CompoundSelector $compound): bool
    {
        foreach ($compound->getComponents() as $theirSimple) {
            if ($simple->equals($theirSimple)) {
                return true;
            }

            // Some selector pseudoclasses can match normal selectors.
            if (!$theirSimple instanceof PseudoSelector) {
                continue;
            }
            $selector = $theirSimple->getSelector();
            if ($selector === null) {
                continue;
            }
            if (!\in_array($theirSimple->getNormalizedName(), self::SUBSELECTOR_PSEUDOS, true)) {
                return false;
            }

            foreach ($selector->getComponents() as $complex) {
                if (\count($complex->getComponents()) !== 1) {
                    continue 2;
                }

                $innerCompound = $complex->getComponents()[0];
                assert($innerCompound instanceof CompoundSelector);

                if (!EquatableUtil::listContains($innerCompound->getComponents(), $simple)) {
                    continue 2;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Returns whether $pseudo1 is a superselector of $compound2.
     *
     * That is, whether $pseudo1 matches every element that $compound2 matches, as well
     * as possibly additional elements.
     *
     * This assumes that $pseudo1's `selector` argument is not `null`.
     *
     * If $parents is passed, it represents the parents of $compound2. This is
     * relevant for pseudo selectors with selector arguments, where we may need to
     * know if the parent selectors in the selector argument match $parents.
     *
     * @phpstan-param list<CompoundSelector|Combinator::*>|null $parents
     */
    private static function selectorPseudoIsSuperselector(PseudoSelector $pseudo1, CompoundSelector $compound2, ?array $parents): bool
    {
        $selector1 = $pseudo1->getSelector();

        if ($selector1 === null) {
            throw new \InvalidArgumentException("Selector $pseudo1 must have a selector argument.");
        }

        switch ($pseudo1->getNormalizedName()) {
            case 'is':
            case 'matches':
            case 'any':
                $selectors = self::selectorPseudoArgs($compound2, $pseudo1->getName());

                foreach ($selectors as $selector2) {
                    if ($selector1->isSuperselector($selector2)) {
                        return true;
                    }
                }

                $compoundWithParents = $parents;
                $compoundWithParents[] = $compound2;

                foreach ($selector1->getComponents() as $complex1) {
                    if (self::complexIsSuperselector($complex1->getComponents(), $compoundWithParents)) {
                        return true;
                    }
                }

                return false;

            case 'has':
            case 'host':
            case 'host-context':
                $selectors = self::selectorPseudoArgs($compound2, $pseudo1->getName());

                foreach ($selectors as $selector2) {
                    if ($selector1->isSuperselector($selector2)) {
                        return true;
                    }
                }

                return false;

            case 'slotted':
                $selectors = self::selectorPseudoArgs($compound2, $pseudo1->getName(), false);

                foreach ($selectors as $selector2) {
                    if ($selector1->isSuperselector($selector2)) {
                        return true;
                    }
                }

                return false;

            case 'not':
                foreach ($selector1->getComponents() as $complex) {
                    foreach ($compound2->getComponents() as $simple2) {
                        if ($simple2 instanceof TypeSelector) {
                            $compound1 = $complex->getLastComponent();

                            if (!$compound1 instanceof CompoundSelector) {
                                continue;
                            }

                            foreach ($compound1->getComponents() as $simple1) {
                                if ($simple1 instanceof TypeSelector && !$simple1->equals($simple2)) {
                                    continue 3;
                                }
                            }
                        } elseif ($simple2 instanceof IDSelector) {
                            $compound1 = $complex->getLastComponent();

                            if (!$compound1 instanceof CompoundSelector) {
                                continue;
                            }

                            foreach ($compound1->getComponents() as $simple1) {
                                if ($simple1 instanceof IDSelector && !$simple1->equals($simple2)) {
                                    continue 3;
                                }
                            }
                        } elseif ($simple2 instanceof PseudoSelector && $simple2->getName() === $pseudo1->getName()) {
                            $selector2 = $simple2->getSelector();
                            if ($selector2 === null) {
                                continue;
                            }

                            if (self::listIsSuperselector($selector2->getComponents(), [$complex])) {
                                continue 2;
                            }
                        }
                    }

                    return false;
                }

                return true;

            case 'current':
                $selectors = self::selectorPseudoArgs($compound2, $pseudo1->getName());

                foreach ($selectors as $selector2) {
                    if ($selector1->equals($selector2)) {
                        return true;
                    }
                }

                return false;

            case 'nth-child':
            case 'nth-last-child':
                foreach ($compound2->getComponents() as $pseudo2) {
                    if (!$pseudo2 instanceof PseudoSelector) {
                        continue;
                    }

                    if ($pseudo2->getName() !== $pseudo1->getName()) {
                        continue;
                    }

                    if ($pseudo2->getArgument() !== $pseudo1->getArgument()) {
                        continue;
                    }

                    $selector2 = $pseudo2->getSelector();

                    if ($selector2 === null) {
                        continue;
                    }

                    if ($selector1->isSuperselector($selector2)) {
                        return true;
                    }
                }

                return false;

            default:
                throw new \LogicException('unreachache');
        }
    }

    /**
     * Returns all the selector arguments of pseudo selectors in $compound with
     * the given $name.
     *
     * @return SelectorList[]
     */
    private static function selectorPseudoArgs(CompoundSelector $compound, string $name, bool $isClass = true): array
    {
        $selectors = [];

        foreach ($compound->getComponents() as $simple) {
            if (!$simple instanceof PseudoSelector) {
                continue;
            }

            if ($simple->isClass() !== $isClass || $simple->getName() !== $name) {
                continue;
            }

            if ($simple->getSelector() === null) {
                continue;
            }

            $selectors[] = $simple->getSelector();
        }

        return $selectors;
    }
}
