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
class ErrorUtil
{
    /**
     * @throws \OutOfRangeException
     */
    public static function checkIntInInterval(int $value, int $minValue, int $maxValue, ?string $name = null): void
    {
        if ($value < $minValue || $value > $maxValue) {
            $nameDisplay = $name ? " $name" : '';

            throw new \OutOfRangeException("Invalid value:$nameDisplay must be between $minValue and $maxValue: $value.");
        }
    }
}
