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

namespace ScssPhp\ScssPhp\Ast\Sass\Statement;

use ScssPhp\ScssPhp\Ast\Sass\ArgumentInvocation;
use ScssPhp\ScssPhp\Ast\Sass\CallableInvocation;
use ScssPhp\ScssPhp\Ast\Sass\Statement;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Visitor\StatementVisitor;

/**
 * A mixin invocation.
 *
 * @internal
 */
final class IncludeRule implements Statement, CallableInvocation
{
    private $namespace;
    private $name;
    private $arguments;
    private $content;
    private $span;

    public function __construct(string $name, ArgumentInvocation $arguments, FileSpan $span, ?string $namespace = null,?ContentBlock $content = null)
    {
        $this->name = $name;
        $this->arguments = $arguments;
        $this->span = $span;
        $this->namespace = $namespace;
        $this->content = $content;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): ArgumentInvocation
    {
        return $this->arguments;
    }

    public function getContent(): ?ContentBlock
    {
        return $this->content;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function accepts(StatementVisitor $visitor)
    {
        return $visitor->visitIncludeRule($this);
    }
}
