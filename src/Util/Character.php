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
     * Returns whether $character is an ASCII whitespace character.
     */
    public static function isWhitespace(?string $character): bool
    {
        return $character === ' ' || $character === "\t" || $character === "\n" || $character === "\r" || $character === "\f";
    }

    /**
     * Returns whether $character is a space or a tab character.
     */
    public static function isSpaceOrTab(?string $character): bool
    {
        return $character === ' ' || $character === "\t";
    }

    /**
     * Returns whether $character is an ASCII newline character.
     */
    public static function isNewline(?string $character): bool
    {
        return $character === "\n" || $character === "\r" || $character === "\f";
    }

    /**
     * Returns whether $character is a letter.
     */
    public static function isAlphabetic(string $character): bool
    {
        $charCode = \ord($character);

        return ($charCode >= \ord('a') && $charCode <= \ord('z')) || ($charCode >= \ord('A') && $charCode <= \ord('Z'));
    }

    /**
     * Returns whether $character is a digit.
     */
    public static function isDigit(string $character): bool
    {
        $charCode = \ord($character);

        return $charCode >= \ord('0') && $charCode <= \ord('9');
    }

    /**
     * Returns whether $character is legal as the start of a Sass identifier.
     */
    public static function isNameStart(string $character): bool
    {
        return $character === '_' || self::isAlphabetic($character) || \ord($character) >= 0x80;
    }

    /**
     * Returns whether $character is legal in the body of a Sass identifier.
     */
    public static function isName(string $character): bool
    {
        return self::isNameStart($character) || self::isDigit($character) || $character === '-';
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

    /**
     * Assumes that $character is a left-hand brace-like character, and returns
     * the right-hand version.
     */
    public static function opposite(string $character): string
    {
        switch ($character) {
            case '(':
                return ')';

            case '{':
                return '}';

            case '[':
                return ']';

            default:
                throw new \InvalidArgumentException(sprintf('Expected a brace character. Got "%s"', $character));
        }
    }
}
