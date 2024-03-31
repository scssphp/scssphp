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

namespace ScssPhp\ScssPhp\Value;

use ScssPhp\ScssPhp\Visitor\ValueVisitor;

/**
 * A SassScript mixin reference.
 *
 * A mixin reference captures a mixin from the local environment so that
 * it may be passed between modules.
 */
final class SassMixin extends Value
{
    // TODO find a better representation of mixins, as names won't be unique anymore once modules enter in the equation.
    private $name;

    /**
     * @internal
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @internal
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @internal
     */
    public function accept(ValueVisitor $visitor)
    {
        return $visitor->visitMixin($this);
    }

    public function assertMixin(?string $name = null): SassMixin
    {
        return $this;
    }

    public function equals(object $other): bool
    {
        return $other instanceof SassMixin && $this->name === $other->name;
    }
}
