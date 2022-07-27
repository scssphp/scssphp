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

use ScssPhp\ScssPhp\Serializer\Serializer;
use ScssPhp\ScssPhp\Util\Equatable;
use ScssPhp\ScssPhp\Visitor\SelectorVisitor;
use ScssPhp\ScssPhp\Warn;

/**
 * A node in the abstract syntax tree for a selector.
 *
 * This selector tree is mostly plain CSS, but also may contain a
 * {@see ParentSelector} or a {@see PlaceholderSelector}.
 *
 * Selectors have structural equality semantics.
 */
abstract class Selector implements Equatable
{
    /**
     * Whether this selector, and complex selectors containing it, should not be
     * emitted.
     */
    public function isInvisible(): bool
    {
        return $this->accept(new IsInvisibleVisitor(true));
    }

    /**
     * Whether this selector would be invisible even if it didn't have bogus
     * combinators.
     */
    public function isInvisibleOtherThanBogusCombinators(): bool
    {
        return $this->accept(new IsInvisibleVisitor(false));
    }

    /**
     * Whether this selector is not valid CSS.
     *
     * This includes both selectors that are useful exclusively for build-time
     * nesting (`> .foo)` and selectors with invalid combinators that are still
     * supported for backwards-compatibility reasons (`.foo + ~ .bar`).
     */
    public function isBogus(): bool
    {
        return $this->accept(new IsBogusVisitor(true));
    }

    /**
     * Whether this selector is bogus other than having a leading combinator.
     */
    public function isBogusOtherThanLeadingCombinator(): bool
    {
        return $this->accept(new IsBogusVisitor(false));
    }

    /**
     * Whether this is a useless selector (that is, it's bogus _and_ it can't be
     * transformed into valid CSS by `@extend` or nesting).
     */
    public function isUseless(): bool
    {
        return $this->accept(new IsUselessVisitor());
    }

    /**
     * Prints a warning if $this is a bogus selector.
     *
     * This may only be called from within a custom Sass function. This will
     * throw a {@see SassScriptException} in a future major version.
     */
    public function assertNotBogus(?string $name = null): void
    {
        if (!$this->isBogus()) {
            return;
        }

        Warn::deprecation(($name === null ? '' : "\$$name: ") . "$this is not valid CSS.\nThis will be an error in Dart Sass 2.0.0.\n\nMore info: https://sass-lang.com/d/bogus-combinators");
    }

    /**
     * Calls the appropriate visit method on $visitor.
     *
     * @template T
     *
     * @param SelectorVisitor<T> $visitor
     *
     * @return T
     *
     * @internal
     */
    abstract public function accept(SelectorVisitor $visitor);

    final public function __toString(): string
    {
        return Serializer::serializeSelector($this, true);
    }
}
