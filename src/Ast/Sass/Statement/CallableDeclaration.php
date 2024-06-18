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

use ScssPhp\ScssPhp\Ast\Sass\ArgumentDeclaration;
use ScssPhp\ScssPhp\Ast\Sass\Statement;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;

/**
 * An abstract class for callables (functions or mixins) that are declared in
 * user code.
 *
 * @extends ParentStatement<Statement[]>
 *
 * @internal
 */
abstract class CallableDeclaration extends ParentStatement
{
    private readonly string $name;

    private readonly ArgumentDeclaration $arguments;

    private readonly ?SilentComment $comment;

    private readonly FileSpan $span;

    /**
     * @param Statement[] $children
     */
    public function __construct(string $name, ArgumentDeclaration $arguments, FileSpan $span, array $children, ?SilentComment $comment = null)
    {
        $this->name = $name;
        $this->arguments = $arguments;
        $this->comment = $comment;
        $this->span = $span;
        parent::__construct($children);
    }

    /**
     * The name of this callable, with underscores converted to hyphens.
     */
    final public function getName(): string
    {
        return $this->name;
    }

    final public function getArguments(): ArgumentDeclaration
    {
        return $this->arguments;
    }

    final public function getComment(): ?SilentComment
    {
        return $this->comment;
    }

    final public function getSpan(): FileSpan
    {
        return $this->span;
    }
}
