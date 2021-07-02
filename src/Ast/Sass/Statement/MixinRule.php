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
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Visitor\StatementVisitor;

/**
 * A mixin declaration.
 *
 * This declares a mixin that's invoked using `@include`.
 *
 * @internal
 */
final class MixinRule extends CallableDeclaration
{
    /**
     * Whether the mixin contains a `@content` rule.
     *
     * @var bool
     */
    private $content;

    /**
     * Creates a MixinRule.
     *
     * It's important that the caller passes $hasContent if the mixin
     * recursively contains a `@content` rule. Otherwise, invoking this mixin
     * won't work correctly.
     */
    public function __construct(string $name, ArgumentDeclaration $arguments, FileSpan $span, array $children, bool $hasContent = false, ?SilentComment $comment = null)
    {
        $this->content = $hasContent;
        parent::__construct($name, $arguments, $span, $children, $comment);
    }

    public function hasContent(): bool
    {
        return $this->content;
    }

    public function accepts(StatementVisitor $visitor)
    {
        return $visitor->visitMixinRule($this);
    }
}
