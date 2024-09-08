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

namespace ScssPhp\ScssPhp\SourceSpan\Highlighter;

/**
 * A single line of the source file being highlighted.
 *
 * @internal
 */
final class Line
{
    /**
     * All highlights that cover any portion of this line, in source span order.
     *
     * This is populated after the initial line is created.
     *
     * @var list<Highlight>
     */
    public array $highlights = [];

    /**
     * The URL of the source file in which this line appears.
     *
     * For lines created from spans without an explicit URL, this is an opaque
     * object that differs between lines that come from different spans.
     */
    public readonly object $url;


    /**
     * @param int $number The O-based line number in the source file
     */
    public function __construct(
        public readonly string $text,
        public readonly int $number,
        object $url,
    ) {
        $this->url = $url;
    }
}
