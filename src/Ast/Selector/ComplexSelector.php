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

use ScssPhp\ScssPhp\Util\EquatableUtil;
use ScssPhp\ScssPhp\Visitor\SelectorVisitor;

class ComplexSelector extends Selector
{
    /**
     * The components of this selector.
     *
     * This is never empty.
     *
     * Descendant combinators aren't explicitly represented here. If two
     * {@see CompoundSelector}s are adjacent to one another, there's an implicit
     * descendant combinator between them.
     *
     * It's possible for multiple {@see Combinator}s to be adjacent to one another.
     * This isn't valid CSS, but Sass supports it for CSS hack purposes.
     *
     * @var list<CompoundSelector|string>
     * @phpstan-var list<CompoundSelector|Combinator::*>
     * @readonly
     */
    private $components;

    /**
     * Whether a line break should be emitted *before* this selector.
     *
     * @var bool
     * @readonly
     */
    private $lineBreak;

    /**
     * @var int|null
     */
    private $minSpecificity;

    /**
     * @var int|null
     */
    private $maxSpecificity;

    /**
     * @param list<CompoundSelector|string> $components
     * @param bool                          $lineBreak
     *
     * @phpstan-param list<CompoundSelector|Combinator::*> $components
     */
    public function __construct(array $components, bool $lineBreak = false)
    {
        if ($components === []) {
            throw new \InvalidArgumentException('components may not be empty.');
        }

        $this->components = $components;
        $this->lineBreak = $lineBreak;
    }

    /**
     * @return list<CompoundSelector|string>
     * @phpstan-return list<CompoundSelector|Combinator::*>
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    public function getLineBreak(): bool
    {
        return $this->lineBreak;
    }

    public function getMinSpecificity(): int
    {
        if ($this->minSpecificity === null) {
            $this->computeSpecificity();
            assert($this->minSpecificity !== null);
        }

        return $this->minSpecificity;
    }

    public function getMaxSpecificity(): int
    {
        if ($this->maxSpecificity === null) {
            $this->computeSpecificity();
            assert($this->maxSpecificity !== null);
        }

        return $this->maxSpecificity;
    }

    public function isInvisible(): bool
    {
        foreach ($this->components as $component) {
            if ($component instanceof CompoundSelector && $component->isInvisible()) {
                return true;
            }
        }

        return false;
    }

    public function accept(SelectorVisitor $visitor)
    {
        return $visitor->visitComplexSelector($this);
    }

    public function equals(object $other): bool
    {
        if (!$other instanceof ComplexSelector) {
            return false;
        }

        return EquatableUtil::listEquals($this->components, $other->components);
    }

    /**
     * Computes {@see minSpecificity} and {@see maxSpecificity}.
     */
    private function computeSpecificity(): void
    {
        $minSpecificity = 0;
        $maxSpecificity = 0;

        foreach ($this->components as $component) {
            if ($component instanceof CompoundSelector) {
                $minSpecificity += $component->getMinSpecificity();
                $maxSpecificity += $component->getMaxSpecificity();
            }
        }

        $this->minSpecificity = $minSpecificity;
        $this->maxSpecificity = $maxSpecificity;
    }
}
