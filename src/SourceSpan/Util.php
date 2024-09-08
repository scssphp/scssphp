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

namespace ScssPhp\ScssPhp\SourceSpan;

use League\Uri\Contracts\UriInterface;

/**
 * @internal
 */
final class Util
{
    /**
     * Finds a line in $context containing $text at the specified column.
     *
     * Returns the index in $context where that line begins, or null if none
     * exists.
     */
    public static function findLineStart(string $context, string $text, int $column): ?int
    {
        // If the text is empty, we just want to find the first line that has at least
        // [column] characters.
        if ($text === '') {
            $beginningOfLine = 0;

            while (true) {
                $index = strpos($context, "\n", $beginningOfLine);

                if ($index === false) {
                    return \strlen($context) - $beginningOfLine >= $column ? $beginningOfLine : null;
                }

                if ($index - $beginningOfLine >= $column) {
                    return $beginningOfLine;
                }

                $beginningOfLine = $index + 1;
            }
        }

        $index = strpos($context, $text);

        while ($index !== false) {
            // Start looking before $index in case $text starts with a newline.
            $lineStart = $index === 0 ? 0 : Util::lastIndexOf($context, "\n", $index - 1) + 1;
            $textColumn = $index - $lineStart;

            if ($column === $textColumn) {
                return $lineStart;
            }

            $index = strpos($context, $text, $index + 1);
        }

        return null;
    }

    /**
     * Returns a two-element list containing the start and end locations of the
     * span from $start bytes (inclusive) to $end bytes (exclusive)
     * after the beginning of $span.
     *
     * @return array{SourceLocation, SourceLocation}
     */
    public static function subspanLocations(SourceSpan $span, int $start, ?int $end = null): array
    {
        $text = $span->getText();
        $startLocation = $span->getStart();
        $line = $startLocation->getLine();
        $column = $startLocation->getColumn();

        // Adjust $line and $column as necessary if the character at $i in $text
        // is a newline.
        $consumeCodePoint = function (int $i) use ($text, &$line, &$column) {
            $codeUnit = $text[$i];

            if (
                $codeUnit === "\n" ||
                // A carriage return counts as a newline, but only if it's not
                // followed by a line feed.
                ($codeUnit === "\r" && ($i + 1 === \strlen($text) || $text[$i + 1] !== "\n"))
            ) {
                $line += 1;
                $column = 0;
            } else {
                $column += 1;
            }
        };

        for ($i = 0; $i < $start; $i++) {
            $consumeCodePoint($i);
        }

        $newStartLocation = new SimpleSourceLocation($startLocation->getOffset() + $start, $span->getSourceUrl(), $line, $column);

        if ($end === null || $end === $span->getLength()) {
            $newEndLocation = $span->getEnd();
        } elseif ($end === $start) {
            $newEndLocation = $newStartLocation;
        } else {
            for ($i = $start; $i < $end; $i++) {
                $consumeCodePoint($i);
            }

            $newEndLocation = new SimpleSourceLocation($startLocation->getOffset() + $end, $span->getSourceUrl(), $line, $column);
        }

        return [$newStartLocation, $newEndLocation];
    }

    /**
     * The starting position of the last match $needle in this string.
     *
     * Finds a match of $needle by searching backward starting at $start.
     * Returns -1 if $needle could not be found in this string.
     * If $start is omitted, search starts from the end of the string.
     */
    public static function lastIndexOf(string $string, string $needle, ?int $start = null): int
    {
        if ($start === null || $start === \strlen($string)) {
            $position = strrpos($string, $needle);
        } else {
            if ($start < 0) {
                throw new \InvalidArgumentException("Start must be a non-negative integer");
            }

            if ($start > \strlen($string)) {
                throw new \InvalidArgumentException("Start must not be greater than the length of the string");
            }

            $position = strrpos($string, $needle, $start - \strlen($string));
        }

        return $position === false ? -1 : $position;
    }

    /**
     * Returns the text of the string from $start to $end (exclusive).
     *
     * If $end isn't passed, it defaults to the end of the string.
     */
    public static function substring(string $text, int $start, ?int $end = null): string
    {
        if ($end === null) {
            return substr($text, $start);
        }

        if ($end < $start) {
            $length = 0;
        } else {
            $length = $end - $start;
        }

        return substr($text, $start, $length);
    }

    public static function isSameUrl(?UriInterface $url1, ?UriInterface $url2): bool
    {
        if ($url1 === null) {
            return $url2 === null;
        }

        if ($url2 === null) {
            return false;
        }

        return (string) $url1 === (string) $url2;
    }
}
