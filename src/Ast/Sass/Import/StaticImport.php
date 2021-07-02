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

namespace ScssPhp\ScssPhp\Ast\Sass\Import;

use ScssPhp\ScssPhp\Ast\Sass\Import;
use ScssPhp\ScssPhp\Ast\Sass\Interpolation;
use ScssPhp\ScssPhp\Ast\Sass\SupportsCondition;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Visitor\ExpressionVisitor;

/**
 * An import that produces a plain CSS `@import` rule.
 *
 * @internal
 */
final class StaticImport implements Import
{
    /**
     * The URL for this import.
     *
     * This already contains quotes.
     *
     * @var Interpolation
     */
    private $url;
    private $span;
    private $supports;
    private $media;

    public function __construct(Interpolation $url, FileSpan $span, ?SupportsCondition $supports = null, ?Interpolation $media = null)
    {
        $this->url = $url;
        $this->span = $span;
        $this->supports = $supports;
        $this->media = $media;
    }

    public function getUrl(): Interpolation
    {
        return $this->url;
    }

    public function getSupports(): ?SupportsCondition
    {
        return $this->supports;
    }

    public function getMedia(): ?Interpolation
    {
        return $this->media;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }
}
