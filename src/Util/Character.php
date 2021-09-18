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

namespace ScssPhp\ScssPhp\Util;

/**
 * @internal
 */
final class Character
{
    /**
     * Returns whether $character is a digit.
     */
    public static function isDigit(string $character): bool
    {
        $charCode = \ord($character);

        return $charCode >= \ord('0') && $charCode <= \ord('9');
    }

    /**
     * Returns whether $character is a hexadecimal digit.
     */
    public static function isHex(string $character): bool
    {
        if (self::isDigit($character)) {
            return true;
        }

        $charCode = \ord($character);

        if ($charCode >= \ord('a') && $charCode <= \ord('f')) {
            return true;
        }

        if ($charCode >= \ord('A') && $charCode <= \ord('F')) {
            return true;
        }

        return false;
    }
}
