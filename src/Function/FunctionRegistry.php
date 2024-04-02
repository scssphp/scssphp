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

namespace ScssPhp\ScssPhp\Function;

use ScssPhp\ScssPhp\SassCallable\BuiltInCallable;
use ScssPhp\ScssPhp\Value\Value;

/**
 * @internal
 */
class FunctionRegistry
{
    /**
     * @var array<string, array{overloads: array<string, callable(list<Value>): Value>, url: string}>
     */
    private const BUILTIN_FUNCTIONS = [
        // sass:meta
        'feature-exists' => ['overloads' => ['$feature' => [MetaFunctions::class, 'featureExists']], 'url' => 'sass:meta'],
        'inspect' => ['overloads' => ['$value' => [MetaFunctions::class, 'inspect']], 'url' => 'sass:meta'],
        'type-of' => ['overloads' => ['$value' => [MetaFunctions::class, 'typeof']], 'url' => 'sass:meta'],
    ];

    public static function has(string $name): bool
    {
        return isset(self::BUILTIN_FUNCTIONS[$name]);
    }

    public static function get(string $name): BuiltInCallable
    {
        if (!isset(self::BUILTIN_FUNCTIONS[$name])) {
            throw new \InvalidArgumentException("There is no builtin function named $name.");
        }

        return BuiltInCallable::overloadedFunction($name, self::BUILTIN_FUNCTIONS[$name]['overloads'], self::BUILTIN_FUNCTIONS[$name]['url']);
    }
}
