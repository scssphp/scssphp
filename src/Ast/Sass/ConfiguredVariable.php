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

use ScssPhp\ScssPhp\SourceSpan\FileSpan;

/**
 * A variable configured by a `with` clause in a `@use` or `@forward` rule.
 *
 * @internal
 */
final class ConfiguredVariable implements SassNode
{
    private $name;
    private $expression;
    private $span;
    private $guarded;

    public function __construct(string $name, Expression $expression, FileSpan $span, bool $guarded = false)
    {
        $this->name = $name;
        $this->expression = $expression;
        $this->span = $span;
        $this->guarded = $guarded;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExpression(): Expression
    {
        return $this->expression;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function isGuarded(): bool
    {
        return $this->guarded;
    }
}
