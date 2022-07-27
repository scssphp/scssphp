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

use ScssPhp\ScssPhp\Visitor\EveryCssVisitor;

/**
 * The visitor used to implement {@see CssNode::isInvisible}
 *
 * @internal
 */
final class IsInvisibleVisitor extends EveryCssVisitor
{
    /**
     * Whether to consider selectors with bogus combinators invisible.
     *
     * @var bool
     * @readonly
     */
    private $includeBogus;

    /**
     * Whether to consider comments invisible.
     *
     * @var bool
     * @readonly
     */
    private $includeComments;

    public function __construct(bool $includeBogus, bool $includeComments)
    {
        $this->includeBogus = $includeBogus;
        $this->includeComments = $includeComments;
    }

    public function visitCssAtRule($node): bool
    {
        // An unknown at-rule is never invisible. Because we don't know the semantics
        // of unknown rules, we can't guarantee that (for example) `@foo {}` isn't
        // meaningful.
        return false;
    }

    public function visitCssComment($node): bool
    {
        return $this->includeComments && !$node->isPreserved();
    }

    public function visitCssStyleRule($node): bool
    {
        return ($this->includeBogus ? $node->getSelector()->getValue()->isInvisible() : $node->getSelector()->getValue()->isInvisibleOtherThanBogusCombinators()) || parent::visitCssStyleRule($node);
    }
}
