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
 * An interface that describes a segment of source text with additional context.
 *
 * @internal
 */
interface SourceSpanWithContext extends SourceSpan
{
    /**
     * Text around the span, which includes the line containing this span.
     */
    public function getContext(): string;

    public function subspan(int $start, ?int $end = null): SourceSpanWithContext;
}
