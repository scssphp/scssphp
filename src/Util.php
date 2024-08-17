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

use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;
use ScssPhp\ScssPhp\Base\Range;
use ScssPhp\ScssPhp\Exception\RangeException;
use ScssPhp\ScssPhp\Node\Number;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\StackTrace\Frame;
use ScssPhp\ScssPhp\Util\StringUtil;

/**
 * Utility functions
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 *
 * @internal
 */
final class Util
{
    /**
     * Returns $string with every line indented $indentation spaces.
     */
    public static function indent(string $string, int $indentation): string
    {
        return implode("\n", array_map(function ($line) use ($indentation) {
            return str_repeat(' ', $indentation) . $line;
        }, explode("\n", $string)));
    }

    /**
     * Asserts that `value` falls within `range` (inclusive), leaving
     * room for slight floating-point errors.
     *
     * @param string       $name  The name of the value. Used in the error message.
     * @param Range        $range Range of values.
     * @param array|Number $value The value to check.
     * @param string       $unit  The unit of the value. Used in error reporting.
     *
     * @return mixed `value` adjusted to fall within range, if it was outside by a floating-point margin.
     *
     * @throws RangeException
     */
    public static function checkRange(string $name, Range $range, $value, string $unit = '')
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
     */
    public static function encodeURIComponent(string $string): string
    {
        $revert = ['%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')'];

        return strtr(rawurlencode($string), $revert);
    }

    public static function frameForSpan(FileSpan $span, string $member, ?UriInterface $url = null): Frame
    {
        return new Frame(
            $url ?? $span->getSourceUrl() ?? Uri::new('-'),
            $span->getStart()->getLine() + 1,
            $span->getStart()->getColumn() + 1,
            $member
        );
    }

    /**
     * Returns the variable name (including the leading `$`) from a $span that
     * covers a variable declaration, which includes the variable name as well as
     * the colon and expression following it.
     *
     * This isn't particularly efficient, and should only be used for error
     * messages.
     */
    public static function declarationName(FileSpan $span): string
    {
        $text = $span->getText();
        $pos = strpos($text, ':');

        return StringUtil::trimAsciiRight(substr($text, 0, $pos === false ? null : $pos));
    }

    /**
     * Returns $name without a vendor prefix.
     *
     * If $name has no vendor prefix, it's returned as-is.
     */
    public static function unvendor(string $name): string
    {
        $length = \strlen($name);

        if ($length < 2) {
            return $name;
        }

        if ($name[0] !== '-') {
            return $name;
        }

        if ($name[1] === '-') {
            return $name;
        }

        for ($i = 2; $i < $length; $i++) {
            if ($name[$i] === '-') {
                return substr($name, $i + 1);
            }
        }

        return $name;
    }

    /**
     * mb_chr() wrapper
     */
    public static function mbChr(int $code): string
    {
        return mb_chr($code, 'UTF-8');
    }

    /**
     * mb_ord() wrapper
     */
    public static function mbOrd(string $string): int
    {
        return mb_ord($string, 'UTF-8');
    }

    /**
     * mb_strlen() wrapper
     */
    public static function mbStrlen(string $string): int
    {
        return mb_strlen($string, 'UTF-8');
    }

    /**
     * mb_substr() wrapper
     */
    public static function mbSubstr(string $string, int $start, ?int $length = null): string
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * mb_strpos wrapper
     */
    public static function mbStrpos(string $haystack, string $needle, int $offset = 0): int|false
    {
        return mb_strpos($haystack, $needle, $offset, 'UTF-8');
    }

    /**
     * Like {@see \SplObjectStorage::addAll()}, but for two-layer maps.
     *
     * This avoids copying inner maps from $source if possible.
     *
     * @template K1 of object
     * @template K2 of object
     * @template V
     * @template Inner of \SplObjectStorage<K2, V>
     *
     * @param \SplObjectStorage<K1, Inner> $destination
     * @param \SplObjectStorage<K1, Inner> $source
     */
    public static function mapAddAll2(\SplObjectStorage $destination, \SplObjectStorage $source): void
    {
        foreach ($source as $key) {
            $inner = $source->getInfo();

            $innerDestination = $destination[$key] ?? null;

            if ($innerDestination !== null) {
                $innerDestination->addAll($inner);
            } else {
                $destination[$key] = $inner;
            }
        }
    }
}
