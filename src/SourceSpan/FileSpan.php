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
interface FileSpan extends SourceSpanWithContext
{
    public function getFile(): SourceFile;

    public function getStart(): FileLocation;

    public function getEnd(): FileLocation;

    public function expand(FileSpan $other): FileSpan;

    /**
     * Return a span from $start bytes (inclusive) to $end bytes
     * (exclusive) after the beginning of this span
     */
    public function subspan(int $start, ?int $end = null): FileSpan;
}
