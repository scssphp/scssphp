<?php

namespace ScssPhp\ScssPhp\Util;

use ScssPhp\ScssPhp\Value\SassNumber;

/**
 * @internal
 */
class SerializationUtil
{
    /**
     * Formats a number for output with at most the Sass number precision
     *
     * @param int|float $number
     *
     * @return string
     */
    public static function serializeNumber($number): string
    {
        if (is_nan($number)) {
            return 'NaN';
        }

        if ($number === INF) {
            return 'Infinity';
        }

        if ($number === -INF) {
            return '-Infinity';
        }

        $int = NumberUtil::fuzzyAsInt($number);

        if ($int !== null) {
            return (string) $int;
        }

        $output = number_format($number, SassNumber::PRECISION, '.', '');

        return rtrim(rtrim($output, '0'), '.');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function serializeQuotedString(string $string): string
    {
        $includesDoubleQuote = false !== strpos($string, '"');
        $includesSingleQuote = false !== strpos($string, '\'');
        $forceDoubleQuotes = $includesSingleQuote && $includesDoubleQuote;
        $quote = $forceDoubleQuotes || !$includesDoubleQuote ? '"' : "'";

        $offset = 0;
        $length = \strlen($string);
        $escaped = '';

        // Write newline characters and unprintable ASCII characters as escapes.
        while ($offset < $length) {
            $unescapedPartLength = strcspn($string, "\0\x1\x2\x3\x4\x5\x6\x7\x8\xA\xB\xC\xD\xE\xF\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\\", $offset);

            if ($unescapedPartLength > 0) {
                $escaped .= substr($string, $offset, $unescapedPartLength);
                $offset += $unescapedPartLength;
            }

            if ($offset >= $length) {
                break;
            }

            $char = $string[$offset];

            ++$offset;

            if ($char === '\\') {
                $escaped .= '\\\\';
                continue;
            }

            $escaped .= '\\'.dechex(ord($char));

            if ($offset >= $length) {
                break;
            }

            $nextChar = $string[$offset];
            $nextCharCode = ord($nextChar);

            // If the character following our escape sequence is a space, a tab or an hex char, we need to add a space in the escape sequence to end it. In other cases, it is optional.
            if ($nextChar === ' ' || $nextChar === "\t" || ($nextCharCode >= \ord('0') && $nextCharCode <= \ord('9')) || ($nextCharCode >= \ord('a') && $nextCharCode <= \ord('f')) || ($nextCharCode >= \ord('A') && $nextCharCode <= \ord('F'))) {
                $escaped .= ' ';
            }
        }

        if ($forceDoubleQuotes) {
            $escaped = str_replace('"', '\\"', $escaped);
        }

        return $quote . $escaped . $quote;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function serializeUnquotedString(string $string): string
    {
        $buffer = '';
        $afterNewline = false;
        $length = \strlen($string);

        for ($i = 0; $i < $length; ++$i) {
            $char = $string[$i];

            switch ($char) {
                case "\n":
                    $buffer .= ' ';
                    $afterNewline = true;
                    break;

                case ' ':
                    if (!$afterNewline) {
                        $buffer .= ' ';
                    }
                    break;

                default:
                    $buffer .= $char;
                    $afterNewline = false;
                    break;
            }
        }

        return $buffer;
    }
}
