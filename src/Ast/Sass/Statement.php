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

use ScssPhp\ScssPhp\Visitor\StatementVisitor;

/**
 * A statement in a Sass syntax tree.
 *
 * @internal
 */
interface Statement extends SassNode
{
    /**
     * @param StatementVisitor $visitor
     *
     * @return mixed
     *
     * @phpstan-template T
     * @phpstan-param StatementVisitor<T> $visitor
     * @phpstan-return T
     */
    public function accepts(StatementVisitor $visitor);
}
