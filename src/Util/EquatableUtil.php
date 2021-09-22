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
final class EquatableUtil
{
    /**
     * Checks whether 2 lists are equals, using the Equatable semantic to compare objects if possible.
     *
     * @param list<mixed> $list1
     * @param list<mixed> $list2
     */
    public static function listEquals(array $list1, array $list2): bool
    {
        if (\count($list1) !== \count($list2)) {
            return false;
        }

        foreach ($list1 as $i => $item1) {
            $item2 = $list2[$i];

            if ($item1 === $item2) {
                continue;
            }

            if ($item1 instanceof Equatable && $item2 instanceof Equatable && $item1->equals($item2)) {
                continue;
            }

            return false;
        }

        return true;
    }
}
