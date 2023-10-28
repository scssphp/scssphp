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

namespace ScssPhp\ScssPhp\Ast\Selector;

use ScssPhp\ScssPhp\Ast\Css\CssValue;
use ScssPhp\ScssPhp\Exception\SassFormatException;
use ScssPhp\ScssPhp\Exception\SassRuntimeException;
use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Extend\ExtendUtil;
use ScssPhp\ScssPhp\Logger\LoggerInterface;
use ScssPhp\ScssPhp\Parser\InterpolationMap;
use ScssPhp\ScssPhp\Parser\SelectorParser;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Util\EquatableUtil;
use ScssPhp\ScssPhp\Util\ListUtil;
use ScssPhp\ScssPhp\Value\ListSeparator;
use ScssPhp\ScssPhp\Value\SassList;
use ScssPhp\ScssPhp\Value\SassString;
use ScssPhp\ScssPhp\Visitor\SelectorVisitor;

/**
 * A selector list.
 *
 * A selector list is composed of {@see ComplexSelector}s. It matches any element
 * that matches any of the component selectors.
 *
 * @internal
 */
final class SelectorList extends Selector
{
    /**
     * The components of this selector.
     *
     * This is never empty.
     *
     * @var list<ComplexSelector>
     */
    private readonly array $components;

    /**
     * Parses a selector list from $contents.
     *
     * If passed, $url is the name of the file from which $contents comes.
     * $allowParent and $allowPlaceholder control whether {@see ParentSelector}s or
     * {@see PlaceholderSelector}s are allowed in this selector, respectively.
     *
     * @throws SassFormatException if parsing fails.
     */
    public static function parse(string $contents, ?LoggerInterface $logger = null, ?InterpolationMap $interpolationMap = null, ?string $url = null, bool $allowParent = true, bool $allowPlaceholder = true): SelectorList
    {
        return (new SelectorParser($contents, $logger, $url, $allowParent, $interpolationMap, $allowPlaceholder))->parse();
    }

    /**
     * @param list<ComplexSelector> $components
     */
    public function __construct(array $components, FileSpan $span)
    {
        if ($components === []) {
            throw new \InvalidArgumentException('components may not be empty.');
        }

        $this->components = $components;
        parent::__construct($span);
    }

    /**
     * @return list<ComplexSelector>
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * Returns a SassScript list that represents this selector.
     *
     * This has the same format as a list returned by `selector-parse()`.
     */
    public function asSassList(): SassList
    {
        return new SassList(array_map(static function (ComplexSelector $complex) {
            $result = [];
            foreach ($complex->getLeadingCombinators() as $combinator) {
                $result[] = new SassString($combinator, false);
            }
            foreach ($complex->getComponents() as $component) {
                $result[] = new SassString((string) $component->getSelector(), false);

                foreach ($component->getCombinators() as $combinator) {
                    $result[] = new SassString($combinator, false);
                }
            }

            return new SassList($result, ListSeparator::SPACE);
        }, $this->components), ListSeparator::COMMA);
    }

    public function accept(SelectorVisitor $visitor)
    {
        return $visitor->visitSelectorList($this);
    }

    /**
     * Returns a {@see SelectorList} that matches only elements that are matched by
     * both this and $other.
     *
     * If no such list can be produced, returns `null`.
     */
    public function unify(SelectorList $other): ?SelectorList
    {
        $contents = [];

        foreach ($this->components as $complex1) {
            foreach ($other->components as $complex2) {
                $unified = ExtendUtil::unifyComplex([$complex1, $complex2], $complex1->getSpan());

                if ($unified === null) {
                    continue;
                }

                foreach ($unified as $complex) {
                    $contents[] = $complex;
                }
            }
        }

        return \count($contents) === 0 ? null : new SelectorList($contents, $this->getSpan());
    }

    /**
     * Returns a new list with all {@see ParentSelector}s replaced with $parent.
     *
     * If $implicitParent is true, this treats [ComplexSelector]s that don't
     * contain an explicit {@see ParentSelector} as though they began with one.
     *
     * The given $parent may be `null`, indicating that this has no parents. If
     * so, this list is returned as-is if it doesn't contain any explicit
     * {@see ParentSelector}s. If it does, this throws a {@see SassScriptException}.
     */
    public function resolveParentSelectors(?SelectorList $parent, bool $implicitParent = true): SelectorList
    {
        if ($parent === null) {
            $parentSelector = $this->accept(new ParentSelectorVisitor());
            if ($parentSelector === null) {
                return $this;
            }

            throw new SassRuntimeException('Top-level selectors may not contain the parent selector "&".', $parentSelector->getSpan());
        }

        return new SelectorList(ListUtil::flattenVertically(array_map(function (ComplexSelector $complex) use ($parent, $implicitParent) {
            if (!self::containsParentSelector($complex)) {
                if (!$implicitParent) {
                    return [$complex];
                }

                return array_map(fn(ComplexSelector $parentComplex) => $parentComplex->concatenate($complex, $complex->getSpan()), $parent->getComponents());
            }

            /** @var list<ComplexSelector> $newComplexes */
            $newComplexes = [];

            foreach ($complex->getComponents() as $component) {
                $resolved = self::resolveParentSelectorsCompound($component, $parent);
                if ($resolved === null) {
                    if (\count($newComplexes) === 0) {
                        $newComplexes[] = new ComplexSelector($complex->getLeadingCombinators(), [$component], $complex->getSpan(), false);
                    } else {
                        $newComplexes = array_map(fn ($newComplex) => $newComplex->withAdditionalComponent($component, $complex->getSpan()), $newComplexes);
                    }
                } elseif (\count($newComplexes) === 0) {
                    $newComplexes = $resolved;
                } else {
                    $previousComplexes = $newComplexes;
                    $newComplexes = [];

                    foreach ($previousComplexes as $newComplex) {
                        foreach ($resolved as $resolvedComplex) {
                            $newComplexes[] = $newComplex->concatenate($resolvedComplex, $newComplex->getSpan());
                        }
                    }
                }
            }

            return $newComplexes;
        }, $this->components)), $this->getSpan());
    }

    /**
     * Whether this is a superselector of $other.
     *
     * That is, whether this matches every element that $other matches, as well
     * as possibly additional elements.
     */
    public function isSuperselector(SelectorList $other): bool
    {
        return ExtendUtil::listIsSuperselector($this->components, $other->components);
    }

    public function equals(object $other): bool
    {
        return $other instanceof SelectorList && EquatableUtil::listEquals($this->components, $other->components);
    }

    /**
     * Returns a new selector list based on $component with all
     * {@see ParentSelector}s replaced with $parent.
     *
     * Returns `null` if $component doesn't contain any {@see ParentSelector}s.
     *
     * @return list<ComplexSelector>|null
     */
    private static function resolveParentSelectorsCompound(ComplexSelectorComponent $component, SelectorList $parent): ?array
    {
        $simples = $component->getSelector()->getComponents();
        $containsSelectorPseudo = false;
        foreach ($simples as $simple) {
            if (!$simple instanceof PseudoSelector) {
                continue;
            }
            $selector = $simple->getSelector();

            if ($selector !== null && self::containsParentSelector($selector)) {
                $containsSelectorPseudo = true;
                break;
            }
        }

        if (!$containsSelectorPseudo && !$simples[0] instanceof ParentSelector) {
            return null;
        }

        if ($containsSelectorPseudo) {
            $resolvedSimples = array_map(function (SimpleSelector $simple) use ($parent): SimpleSelector {
                if (!$simple instanceof PseudoSelector) {
                    return $simple;
                }

                $selector = $simple->getSelector();
                if ($selector === null) {
                    return $simple;
                }
                if (!self::containsParentSelector($selector)) {
                    return $simple;
                }

                return $simple->withSelector($selector->resolveParentSelectors($parent, false));
            }, $simples);
        } else {
            $resolvedSimples = $simples;
        }

        $parentSelector = $simples[0];

        // TODO add the span on exceptions in those 2 ifs

        if (!$parentSelector instanceof ParentSelector) {
            return [
                new ComplexSelector([], [
                    new ComplexSelectorComponent(
                        new CompoundSelector($resolvedSimples, $component->getSelector()->getSpan()),
                        $component->getCombinators(),
                        $component->getSpan()
                    ),
                ], $component->getSpan()),
            ];
        }

        if (\count($simples) === 1 && $parentSelector->getSuffix() === null) {
            return $parent->withAdditionalCombinators($component->getCombinators())->getComponents();
        }

        return array_map(function (ComplexSelector $complex) use ($parentSelector, $resolvedSimples, $component) {
            // TODO add the span on exceptions in this callback
            $lastComponent = $complex->getLastComponent();

            if (\count($lastComponent->getCombinators()) !== 0) {
                throw new SassScriptException("Parent \"$complex\" is incompatible with this selector.");
            }

            $suffix = $parentSelector->getSuffix();
            $lastSimples = $lastComponent->getSelector()->getComponents();

            if ($suffix !== null) {
                $last = new CompoundSelector(array_merge(
                    ListUtil::exceptLast($lastSimples),
                    [ListUtil::last($lastSimples)->addSuffix($suffix)],
                    array_slice($resolvedSimples, 1)
                ), $component->getSelector()->getSpan());
            } else {
                $last = new CompoundSelector(array_merge($lastSimples, array_slice($resolvedSimples, 1)), $component->getSelector()->getSpan());
            }

            $components = ListUtil::exceptLast($complex->getComponents());
            $components[] = new ComplexSelectorComponent($last, $component->getCombinators(), $component->getSpan());

            return new ComplexSelector($complex->getLeadingCombinators(), $components, $component->getSpan(), $complex->getLineBreak());
        }, $parent->getComponents());
    }

    /**
     * Returns a copy of `this` with $combinators added to the end of each
     * complex selector in {@see components}].
     *
     * @param list<CssValue<Combinator>> $combinators
     */
    public function withAdditionalCombinators(array $combinators): SelectorList
    {
        if ($combinators === []) {
            return $this;
        }

        return new SelectorList(array_map(fn(ComplexSelector $complex) => $complex->withAdditionalCombinators($combinators), $this->components), $this->getSpan());
    }

    /**
     * Returns whether $selector recursively contains a parent selector.
     */
    private static function containsParentSelector(Selector $selector): bool
    {
        return $selector->accept(new ParentSelectorVisitor()) !== null;
    }
}
