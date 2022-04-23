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

namespace ScssPhp\ScssPhp\Ast\Css;

use ScssPhp\ScssPhp\SourceSpan\FileSpan;

/**
 * A modifiable version of {@see CssStylesheet} for use in the evaluation step.
 *
 * @internal
 */
final class ModifiableCssStylesheet extends ModifiableCssParentNode implements CssStylesheet
{
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;

    /**
     * @param FileSpan $span
     * @param list<ModifiableCssNode> $children
     */
    public function __construct(FileSpan $span, array $children = [])
    {
        parent::__construct($children);
        $this->span = $span;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function accept($visitor)
    {
        return $visitor->visitCssStylesheet($this);
    }

    /**
     * @phpstan-return ModifiableCssStylesheet
     */
    public function copyWithoutChildren(): ModifiableCssParentNode
    {
        return new ModifiableCssStylesheet($this->span);
    }
}
