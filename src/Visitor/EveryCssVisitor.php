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

namespace ScssPhp\ScssPhp\Visitor;

/**
 * A visitor that visits each statements in a CSS AST and returns `true` if all
 * of the individual methods return `true`.
 *
 * Each method returns `false` by default.
 *
 * @template-implements CssVisitor<bool>
 * @internal
 */
abstract class EveryCssVisitor implements CssVisitor
{
    public function visitCssAtRule($node): bool
    {
        foreach ($node->getChildren() as $child) {
            if (!$child->accept($this)) {
                return false;
            }
        }

        return true;
    }

    public function visitCssComment($node): bool
    {
        return false;
    }

    public function visitCssDeclaration($node): bool
    {
        return false;
    }

    public function visitCssImport($node): bool
    {
        return false;
    }

    public function visitCssKeyframeBlock($node): bool
    {
        foreach ($node->getChildren() as $child) {
            if (!$child->accept($this)) {
                return false;
            }
        }

        return true;
    }

    public function visitCssMediaRule($node): bool
    {
        foreach ($node->getChildren() as $child) {
            if (!$child->accept($this)) {
                return false;
            }
        }

        return true;
    }

    public function visitCssStyleRule($node): bool
    {
        foreach ($node->getChildren() as $child) {
            if (!$child->accept($this)) {
                return false;
            }
        }

        return true;
    }

    public function visitCssStylesheet($node): bool
    {
        foreach ($node->getChildren() as $child) {
            if (!$child->accept($this)) {
                return false;
            }
        }

        return true;
    }

    public function visitCssSupportsRule($node): bool
    {
        foreach ($node->getChildren() as $child) {
            if (!$child->accept($this)) {
                return false;
            }
        }

        return true;
    }
}
