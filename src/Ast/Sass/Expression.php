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

namespace ScssPhp\ScssPhp\Ast\Sass;

use ScssPhp\ScssPhp\Visitor\ExpressionVisitor;

/**
 * A SassScript expression in a Sass syntax tree.
 *
 * @internal
 */
interface Expression extends SassNode
{
    /**
     * @param ExpressionVisitor $visitor
     *
     * @return mixed
     *
     * @phpstan-template T
     * @phpstan-param ExpressionVisitor<T> $visitor
     * @phpstan-return T
     */
    public function accepts(ExpressionVisitor $visitor);
}
