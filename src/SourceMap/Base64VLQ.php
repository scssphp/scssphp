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

namespace ScssPhp\ScssPhp\SourceMap;

/**
 * Base64 VLQ encoding as defined by the Source Map v3 specification.
 *
 * @internal
 */
final class Base64VLQ
{
    private const GROUP_BITS = 5;

    private const GROUP_MASK = 0b11111;

    private const CONTINUATION_FLAG = 0b100000;

    /**
     * Encodes a signed integer as a Base64 VLQ string.
     */
    public static function encode(int $value): string
    {
        $magnitude = $value < 0 ? -$value : $value;

        // The first group carries four magnitude bits above the sign bit.
        $group = (($magnitude & 0b1111) << 1) | ($value < 0 ? 1 : 0);
        $magnitude >>= 4;

        $encoded = '';

        while ($magnitude > 0) {
            $encoded .= Base64::encode($group | self::CONTINUATION_FLAG);
            $group = $magnitude & self::GROUP_MASK;
            $magnitude >>= self::GROUP_BITS;
        }

        return $encoded . Base64::encode($group);
    }
}
