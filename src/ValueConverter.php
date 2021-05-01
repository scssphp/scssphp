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

use ScssPhp\ScssPhp\Node\Number;

final class ValueConverter
{
    // Prevent instantiating it
    private function __construct()
    {
    }

    /**
     * Parses a value from a Scss source string.
     *
     * The returned value is guaranteed to be supported by the
     * Compiler methods for registering custom variables. No other
     * guarantee about it is provided. It should be considered
     * opaque values by the caller.
     *
     * @param string $source
     *
     * @return mixed
     */
    public static function parseValue($source)
    {
        $parser = new Parser(__CLASS__);

        if (!$parser->parseValue($source, $value)) {
            throw new \InvalidArgumentException(sprintf('Invalid value source "%s".', $source));
        }

        return $value;
    }

    /**
     * Converts a PHP value to a Sass value
     *
     * The returned value is guaranteed to be supported by the
     * Compiler methods for registering custom variables. No other
     * guarantee about it is provided. It should be considered
     * opaque values by the caller.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function fromPhp($value)
    {
        if ($value instanceof Number) {
            return $value;
        }

        if (is_array($value) && isset($value[0]) && \in_array($value[0], [Type::T_NULL, Type::T_COLOR, Type::T_KEYWORD, Type::T_LIST, Type::T_MAP, Type::T_STRING])) {
            return $value;
        }

        if ($value === null) {
            return Compiler::$null;
        }

        if ($value === true) {
            return Compiler::$true;
        }

        if ($value === false) {
            return Compiler::$false;
        }

        if ($value === '') {
            return Compiler::$false;
        }

        if (\is_int($value) || \is_float($value)) {
            return new Number($value, '');
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('Cannot convert the value of type "%s" to a Sass value.', gettype($value)));
        }

        if (\is_numeric($value)) {
            return new Number(floatval($value), '');
        }

        // hexa color?
        if (preg_match('/^#([0-9a-f]+)$/i', $value, $m)) {
            $nofValues = \strlen($m[1]);

            if (\in_array($nofValues, [3, 4, 6, 8])) {
                $nbChannels = 3;
                $color      = [];
                $num        = hexdec($m[1]);

                switch ($nofValues) {
                    case 4:
                        $nbChannels = 4;
                    // then continuing with the case 3:
                    case 3:
                        for ($i = 0; $i < $nbChannels; $i++) {
                            $t = $num & 0xf;
                            array_unshift($color, $t << 4 | $t);
                            $num >>= 4;
                        }

                        break;

                    case 8:
                        $nbChannels = 4;
                    // then continuing with the case 6:
                    case 6:
                        for ($i = 0; $i < $nbChannels; $i++) {
                            array_unshift($color, $num & 0xff);
                            $num >>= 8;
                        }

                        break;
                }

                if ($nbChannels === 4) {
                    if ($color[3] === 255) {
                        $color[3] = 1; // fully opaque
                    } else {
                        $color[3] = round($color[3] / 255, Number::PRECISION);
                    }
                }

                array_unshift($color, Type::T_COLOR);

                return $color;
            }
        }

        if ($rgba = Colors::colorNameToRGBa(strtolower($value))) {
            return isset($rgba[3])
                ? [Type::T_COLOR, $rgba[0], $rgba[1], $rgba[2], $rgba[3]]
                : [Type::T_COLOR, $rgba[0], $rgba[1], $rgba[2]];
        }

        return [Type::T_KEYWORD, $value];
    }
}
