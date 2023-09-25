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

/**
 * @internal
 */
interface FileSpan
{
    public function getFile(): SourceFile;

    public function getSourceUrl(): ?string;

    public function getLength(): int;

    public function getStart(): SourceLocation;

    public function getEnd(): SourceLocation;

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
     * Return a span from $start bytes (inclusive) to $end bytes
     * (exclusive) after the beginning of this span
     */
    public function subspan(int $start, ?int $end = null): FileSpan;
}
