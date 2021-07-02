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
use ScssPhp\ScssPhp\Node\Number;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Visitor\ExpressionVisitor;

/**
 * An expression that directly embeds a value.
 *
 * This is never constructed by the parser. It's only used when ASTs are
 * constructed dynamically, as for the `call()` function.
 *
 * @internal
 */
final class ValueExpression implements Expression
{
    private $value;
    private $span;

    // TODO use Value as type for the value once implemented
    /**
     * @param array|Number $value
     */
    public function __construct($value, FileSpan $span)
    {
        $this->value = $value;
        $this->span = $span;
    }

    /**
     * @return array|Number
     */
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
        return $visitor->visitValueExpression($this);
    }
}
