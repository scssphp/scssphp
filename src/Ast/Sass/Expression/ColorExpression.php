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
 * A color literal.
 *
 * @internal
 */
final class ColorExpression implements Expression
{
    private $value;
    private $span;

    // TODO use SassColor as type for the value once implemented
    public function __construct($value, FileSpan $span)
    {
        $this->value = $value;
        $this->span = $span;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function accepts(ExpressionVisitor $visitor)
    {
        return $visitor->visitColorExpression($this);
    }
}
