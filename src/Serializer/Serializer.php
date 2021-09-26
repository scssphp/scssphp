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

namespace ScssPhp\ScssPhp\Serializer;

use ScssPhp\ScssPhp\Ast\Selector\Selector;
use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Value\Value;

/**
 * @internal
 */
final class Serializer
{
    /**
     * Converts $value to a CSS string.
     *
     * If $inspect is `true`, this will emit an unambiguous representation of the
     * source structure. Note however that, although this will be valid SCSS, it
     * may not be valid CSS. If $inspect is `false` and $value can't be
     * represented in plain CSS, throws a {@see SassScriptException}.
     *
     * If $quote is `false`, quoted strings are emitted without quotes.
     */
    public static function serializeValue(Value $value, bool $inspect = false, bool $quote = true): string
    {
        $visitor = new SerializeVisitor($inspect, $quote);
        $value->accept($visitor);

        return (string) $visitor->getBuffer();
    }

    /**
     * Converts $selector to a CSS string.
     *
     * If $inspect is `true`, this will emit an unambiguous representation of the
     * source structure. Note however that, although this will be valid SCSS, it
     * may not be valid CSS. If $inspect is `false` and $selector can't be
     * represented in plain CSS, throws a {@see SassScriptException}.
     */
    public static function serializeSelector(Selector $selector, bool $inspect = false): string
    {
        $visitor = new SerializeVisitor($inspect);
        $selector->accept($visitor); // TODO

        return (string) $visitor->getBuffer();
    }
}
