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

namespace ScssPhp\ScssPhp;

final class Warn
{
    /**
     * @var callable|null
     * @phpstan-var (callable(string, ?Deprecation): void)|null
     */
    private static $callback;

    /**
     * Prints a warning message associated with the current `@import` or function call.
     *
     * This may only be called within a custom function or importer callback.
     *
     * @param string $message
     *
     * @return void
     */
    public static function warning(string $message): void
    {
        self::reportWarning($message, null);
    }

    /**
     * Prints a deprecation warning message associated with the current `@import` or function call.
     *
     * This may only be called within a custom function or importer callback.
     *
     * @param string $message
     *
     * @return void
     */
    public static function deprecation(string $message): void
    {
        self::reportWarning($message, Deprecation::userAuthored);
    }

    public static function forDeprecation(string $message, Deprecation $deprecation): void
    {
        self::reportWarning($message, $deprecation);
    }

    /**
     * @param callable|null $callback
     *
     * @return callable|null The previous warn callback
     *
     * @phpstan-param (callable(string, ?Deprecation): void)|null $callback
     *
     * @phpstan-return (callable(string, ?Deprecation): void)|null
     *
     * @internal
     */
    public static function setCallback(callable $callback = null): ?callable
    {
        $previousCallback = self::$callback;
        self::$callback = $callback;

        return $previousCallback;
    }

    private static function reportWarning(string $message, ?Deprecation $deprecation): void
    {
        if (self::$callback === null) {
            throw new \BadMethodCallException('The warning Reporter may only be called within a custom function or importer callback.');
        }

        \call_user_func(self::$callback, $message, $deprecation);
    }
}
