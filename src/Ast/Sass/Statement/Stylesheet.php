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

namespace ScssPhp\ScssPhp\Ast\Sass\Statement;

use ScssPhp\ScssPhp\Ast\Sass\Statement;
use ScssPhp\ScssPhp\Ast\Sass\SupportsCondition;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Visitor\StatementVisitor;

/**
 * A Sass stylesheet.
 *
 * This is the root Sass node. It contains top-level statements.
 *
 * @extends ParentStatement<Statement[]>
 *
 * @internal
 */
final class Stylesheet extends ParentStatement
{
    /**
     * @var bool
     * @readonly
     */
    private $plainCss;

    /**
     * @var FileSpan
     * @readonly
     */
    private $span;

    /**
     * @param Statement[] $children
     */
    public function __construct(array $children, FileSpan $span, bool $plainCss = false)
    {
        $this->span = $span;
        $this->plainCss = $plainCss;
        parent::__construct($children);
    }

    public function isPlainCss(): bool
    {
        return $this->plainCss;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function accepts(StatementVisitor $visitor)
    {
        return $visitor->visitStylesheet($this);
    }
}
