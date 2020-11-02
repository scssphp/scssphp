<?php

namespace ScssPhp\ScssPhp\Exception;

/**
 * Internal exception thrown in places not having access to reporting the location.
 *
 * This class does not implement SassException on purpose, as it should never be returned to the outside code.
 *
 * @internal
 */
class SassScriptException extends \Exception
{
    /**
     * Creates a SassScriptException with support for an argument name.
     *
     * This helper ensures a consistent handling of argument names in the
     * error message, without duplicating it.
     *
     * @param string      $message
     * @param string|null $name    The argument name, without $
     *
     * @return SassScriptException
     */
    public static function forArgument($message, $name = null)
    {
        $varDisplay = !\is_null($name) ? "\${$name}: " : '';

        return new self($varDisplay . $message);
    }
}
