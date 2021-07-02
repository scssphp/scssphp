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
use ScssPhp\ScssPhp\Ast\Sass\CallableInvocation;
use ScssPhp\ScssPhp\Ast\Sass\Expression;
use ScssPhp\ScssPhp\Ast\Sass\Interpolation;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Visitor\ExpressionVisitor;

/**
 * A function invocation.
 *
 * This may be a plain CSS function or a Sass function.
 *
 * @internal
 */
final class FunctionExpression implements Expression, CallableInvocation
{
    /**
     * The name of the function being invoked.
     *
     * Underscores aren't converted to hyphens in this name *unless* $namespace
     * is non-`null`, since otherwise it could be a plain CSS function call.
     *
     * If this is interpolated, the function will be interpreted as plain CSS,
     * even if it has the same name as a Sass function.
     *
     * @var Interpolation
     */
    private $name;

    /**
     * The arguments to pass to the function.
     *
     * @var ArgumentInvocation
     */
    private $arguments;
    /**
     * The namespace of the function being invoked, or `null` if it's invoked
     * without a namespace.
     *
     * @var string|null
     */
    private $namespace;
    private $span;

    public function __construct(Interpolation $name, ArgumentInvocation $arguments, FileSpan $span, ?string $namespace = null)
    {
        $this->span = $span;
        $this->name = $name;
        $this->arguments = $arguments;
        $this->namespace = $namespace;
    }

    public function getName(): Interpolation
    {
        return $this->name;
    }

    public function getArguments(): ArgumentInvocation
    {
        return $this->arguments;
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
        return $visitor->visitFunctionExpression($this);
    }
}
