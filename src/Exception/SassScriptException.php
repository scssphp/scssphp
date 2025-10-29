<?php

namespace ScssPhp\ScssPhp\Exception;

use SourceSpan\FileSpan;

/**
 * An exception thrown by SassScript.
 *
 * This class does not implement SassException on purpose, as it should
 * never be returned to the outside code. The compilation will catch it
 * and replace it with a SassException reporting the location of the
 * error.
 *
 * @phpstan-sealed MultiSpanSassScriptException
 */
class SassScriptException extends \Exception
{
    /**
     * Creates a SassScriptException with support for an argument name.
     *
     * This helper ensures a consistent handling of argument names in the
     * error message, without duplicating it.
     *
     * @param string|null $name The argument name, without $
     */
    public static function forArgument(string $message, ?string $name = null, ?\Throwable $previous = null): SassScriptException
    {
        $varDisplay = !\is_null($name) ? "\${$name}: " : '';

        return new self($varDisplay . $message, 0, $previous);
    }

    /**
     * Converts this to a {@see SassException} with the given $span.
     *
     * @internal
     */
    public function withSpan(FileSpan $span): SassException
    {
        return new SimpleSassException($this->message, $span, $this);
    }
}
