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

use ScssPhp\ScssPhp\Util\Equatable;

/**
 * A component of a {@see ComplexSelector}.
 *
 * This a {@see CompoundSelector} with one or more trailing {@see Combinator}s.
 *
 * @internal
 */
final class ComplexSelectorComponent implements Equatable
{
    /**
     * This component's compound selector.
     *
     * @var CompoundSelector
     * @readonly
     */
    private $selector;

    /**
     * This selector's combinators.
     *
     * If this is empty, that indicates that it has an implicit descendent
     * combinator. If it's more than one element, that means it's invalid CSS;
     * however, we still support this for backwards-compatibility purposes.
     *
     * @var list<string>
     * @phpstan-var list<Combinator::*>
     * @readonly
     */
    private $combinators;

    /**
     * @param CompoundSelector $selector
     * @param list<string>     $combinators
     *
     * @phpstan-param list<Combinator::*> $combinators
     */
    public function __construct(CompoundSelector $selector, array $combinators)
    {
        $this->selector = $selector;
        $this->combinators = $combinators;
    }

    public function getSelector(): CompoundSelector
    {
        return $this->selector;
    }

    /**
     * @return list<string>
     * @phpstan-return list<Combinator::*>
     */
    public function getCombinators(): array
    {
        return $this->combinators;
    }

    public function equals(object $other): bool
    {
        return $other instanceof ComplexSelectorComponent && $this->selector->equals($other->selector) && $this->combinators === $other->combinators;
    }

    /**
     * Returns a copy of $this with $combinators added to the end of
     * `$this->combinators`.
     *
     * @param list<string> $combinators
     *
     * @return ComplexSelectorComponent
     *
     * @phpstan-param list<Combinator::*> $combinators
     */
    public function withAdditionalCombinators(array $combinators): ComplexSelectorComponent
    {
        if ($combinators === []) {
            return $this;
        }

        return new ComplexSelectorComponent($this->selector, array_merge($this->combinators, $combinators));
    }
}
