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

use ScssPhp\ScssPhp\Base\Range;
use ScssPhp\ScssPhp\Exception\RangeException;

/**
 * Utilty functions
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 */
class Util
{
    /**
     * Asserts that `value` falls within `range` (inclusive), leaving
     * room for slight floating-point errors.
     *
     * @param string                    $name  The name of the value. Used in the error message.
     * @param \ScssPhp\ScssPhp\Base\Range $range Range of values.
     * @param array                     $value The value to check.
     * @param string                    $unit  The unit of the value. Used in error reporting.
     *
     * @return mixed `value` adjusted to fall within range, if it was outside by a floating-point margin.
     *
     * @throws \ScssPhp\ScssPhp\Exception\RangeException
     */
    public static function checkRange($name, Range $range, $value, $unit = '')
    {
        $val = $value[1];
        $grace = new Range(-0.00001, 0.00001);

        if (! \is_numeric($val)) {
            throw new RangeException("$name {$val} is not a number.");
        }

        if ($range->includes($val)) {
            return $val;
        }

        if ($grace->includes($val - $range->first)) {
            return $range->first;
        }

        if ($grace->includes($val - $range->last)) {
            return $range->last;
        }

        throw new RangeException("$name {$val} must be between {$range->first} and {$range->last}$unit");
    }

    /**
     * Encode URI component
     *
     * @param string $string
     *
     * @return string
     */
    public static function encodeURIComponent($string)
    {
        $revert = ['%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')'];

        return strtr(rawurlencode($string), $revert);
    }

    /**
     * mb_chr() wrapper
     *
     * @param integer $code
     *
     * @return string
     */
    public static function mbChr($code)
    {
        // Use the native implementation if available.
        if (\function_exists('mb_chr')) {
            return mb_chr($code, 'UTF-8');
        }

        if (0x80 > $code %= 0x200000) {
            $s = \chr($code);
        } elseif (0x800 > $code) {
            $s = \chr(0xC0 | $code >> 6) . \chr(0x80 | $code & 0x3F);
        } elseif (0x10000 > $code) {
            $s = \chr(0xE0 | $code >> 12) . \chr(0x80 | $code >> 6 & 0x3F) . \chr(0x80 | $code & 0x3F);
        } else {
            $s = \chr(0xF0 | $code >> 18) . \chr(0x80 | $code >> 12 & 0x3F)
                . \chr(0x80 | $code >> 6 & 0x3F) . \chr(0x80 | $code & 0x3F);
        }

        return $s;
    }
}
