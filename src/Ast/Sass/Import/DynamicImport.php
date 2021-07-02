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
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Visitor\ExpressionVisitor;

/**
 * An import that will load a Sass file at runtime.
 *
 * @internal
 */
final class DynamicImport implements Import
{
    /**
     * The URI of the file to import.
     *
     * If this is relative, it's relative to the containing file.
     *
     * @var string
     */
    private $url;
    private $span;

    public function __construct(string $url, FileSpan $span)
    {
        $this->url = $url;
        $this->span = $span;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }
}
