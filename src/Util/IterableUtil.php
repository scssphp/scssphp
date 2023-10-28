<?php

/**
 * SCSSPHP
 *
 * @copyright 2018-2020 Anthon Pang
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Util;

/**
 * @internal
 */
final class IterableUtil
{
    /**
     * @template T
     *
     * @param iterable<T>       $list
     * @param callable(T): bool $callback
     */
    public static function any(iterable $list, callable $callback): bool
    {
        foreach ($list as $item) {
            if ($callback($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template T
     *
     * @param iterable<T>       $list
     * @param callable(T): bool $callback
     */
    public static function every(iterable $list, callable $callback): bool
    {
        foreach ($list as $item) {
            if (!$callback($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the first `T` returned by $callback for an element of $iterable,
     * or `null` if it returns `null` for every element.
     *
     * @template T
     * @template E
     * @param iterable<E> $iterable
     * @param callable(E): (T|null) $callback
     *
     * @return T|null
     */
    public static function search(iterable $iterable, callable $callback)
    {
        foreach ($iterable as $element) {
            $value = $callback($element);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }
}
