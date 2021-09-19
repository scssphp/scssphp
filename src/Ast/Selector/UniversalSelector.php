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

use ScssPhp\ScssPhp\Visitor\SelectorVisitor;

/**
 * Matches any element in the given namespace.
 */
final class UniversalSelector extends SimpleSelector
{
    /**
     * The selector namespace.
     *
     * If this is `null`, this matches all elements in the default namespace. If
     * it's the empty string, this matches all elements that aren't in any
     * namespace. If it's `*`, this matches all elements in any namespace.
     * Otherwise, it matches all elements in the given namespace.
     *
     * @var string|null
     * @readonly
     */
    private $namespace;

    public function __construct(?string $namespace = null)
    {
        $this->namespace = $namespace;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getMinSpecificity(): int
    {
        return 0;
    }

    public function accept(SelectorVisitor $visitor)
    {
        return $visitor->visitUniversalSelector($this);
    }

    public function equals(object $other): bool
    {
        return $other instanceof UniversalSelector && $other->namespace === $this->namespace;
    }
}
