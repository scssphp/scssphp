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
interface FileSpan
{
    public function getFile(): SourceFile;

    public function getSourceUrl(): ?UriInterface;

    public function getLength(): int;

    public function getStart(): FileLocation;

    public function getEnd(): FileLocation;

    public function getText(): string;

    public function expand(FileSpan $other): FileSpan;

    /**
     * Formats $message in a human-friendly way associated with this span.
     *
     * @param string $message
     *
     * @return string
     */
    public function message(string $message): string;

    /**
     * Like {@see message}, but also highlights $secondarySpans to provide
     * the user with additional context.
     *
     * Each span takes a label ($label for this span, and the keys of the
     * $secondarySpans map for the secondary spans) that's used to indicate to
     * the user what that particular span represents.
     *
     * @throws \InvalidArgumentException if any secondary span has a different source URL than this span.
     *
     * @param array<string, FileSpan> $secondarySpans
     */
    public function messageMultiple(string $message, string $label, array $secondarySpans): string;

    /**
     * Prints the text associated with this span in a user-friendly way.
     *
     * This is identical to {@see message}, except that it doesn't print the file
     * name, line number, column number, or message.
     */
    public function highlight(): string;

    /**
     * Like {@see highlight}, but also highlights $secondarySpans to provide
     * the user with additional context.
     *
     * Each span takes a label ($label for this span, and the keys of the
     * $secondarySpans map for the secondary spans) that's used to indicate to
     * the user what that particular span represents.
     *
     * @throws \InvalidArgumentException if any secondary span has a different source URL than this span.
     *
     * @param array<string, FileSpan> $secondarySpans
     */
    public function highlightMultiple(string $label, array $secondarySpans): string;

    /**
     * Return a span from $start bytes (inclusive) to $end bytes
     * (exclusive) after the beginning of this span
     */
    public function subspan(int $start, ?int $end = null): FileSpan;
}
