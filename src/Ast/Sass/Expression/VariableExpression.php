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

use ScssPhp\ScssPhp\Ast\Sass\ArgumentInvocation;
use ScssPhp\ScssPhp\Ast\Sass\Expression;
use ScssPhp\ScssPhp\Ast\Sass\Interpolation;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Visitor\ExpressionVisitor;

/**
 * A Sass variable.
 *
 * @internal
 */
final class VariableExpression implements Expression
{
    /**
     * The name of this variable, with underscores converted to hyphens.
     *
     * @var string
     */
    private $name;

    /**
     * The namespace of the variable being referenced, or `null` if it's
     * referenced without a namespace.
     *
     * @var string|null
     */
    private $namespace;
    private $span;

    public function __construct(string $name, FileSpan $span, ?string $namespace = null)
    {
        $this->span = $span;
        $this->name = $name;
        $this->namespace = $namespace;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function accepts(ExpressionVisitor $visitor)
    {
        return $visitor->visitVariableExpression($this);
    }
}
