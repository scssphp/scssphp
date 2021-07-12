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

use ScssPhp\ScssPhp\Exception\SassScriptException;

final class SassFunction extends Value
{
    // TODO find a better representation of functions, as names won't be unique anymore once modules enter in the equation.
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

    public function assertFunction(?string $name = null): SassFunction
    {
        return $this;
    }

    public function toCssString(): string
    {
        throw new SassScriptException("$this is not a valid CSS value.");
    }

    public function __toString(): string
    {
        return 'get-function("' . $this->name . '")';
    }

    public function equals(Value $other): bool
    {
        return $other instanceof SassFunction && $this->name === $other->name;
    }
}
