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

namespace ScssPhp\ScssPhp\Ast\Sass\Expression;

use ScssPhp\ScssPhp\Ast\Sass\Expression;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Visitor\ExpressionVisitor;

/**
 * A map literal.
 *
 * @internal
 */
final class MapExpression implements Expression
{
    /**
     * @phpstan-var list<array{Expression, Expression}>
     */
    private $pairs;
    private $span;

    /**
     * @phpstan-param list<array{Expression, Expression}> $pairs
     */
    public function __construct(array $pairs, FileSpan $span)
    {
        $this->pairs = $pairs;
        $this->span = $span;
    }

    /**
     * @phpstan-return list<array{Expression, Expression}>
     */
    public function getPairs(): array
    {
        return $this->pairs;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function accepts(ExpressionVisitor $visitor)
    {
        return $visitor->visitMapExpression($this);
    }
}
