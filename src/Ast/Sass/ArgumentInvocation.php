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
 * A set of arguments passed in to a function or mixin.
 *
 * @internal
 */
final class ArgumentInvocation implements SassNode
{
    /**
     * @var Expression[]
     * @phpstan-var list<Expression>
     */
    private $positional;

    /**
     * @var array<string, Expression>
     */
    private $named;

    /**
     * @var Expression|null
     */
    private $rest;

    /**
     * @var Expression|null
     */
    private $keywordRest;
    private $span;

    /**
     * @param Expression[]              $positional
     * @param array<string, Expression> $named
     * @param FileSpan                  $span
     * @param Expression|null           $rest
     * @param Expression|null           $keywordRest
     *
     * @phpstan-param list<Expression> $positional
     */
    public function __construct(array $positional, array $named, FileSpan $span, ?Expression $rest = null, ?Expression $keywordRest = null)
    {
        assert($keywordRest === null || $rest !== null);

        $this->positional = $positional;
        $this->named = $named;
        $this->rest = $rest;
        $this->keywordRest = $keywordRest;
        $this->span = $span;
    }

    public static function createEmpty(FileSpan $span): ArgumentInvocation
    {
        return new self([], [], $span);
    }

    public function isEmpty(): bool
    {
        return \count($this->positional) === 0 && \count($this->named) === 0 && $this->rest === null;
    }

    /**
     * @return Expression[]
     * @phpstan-return list<Expression>
     */
    public function getPositional(): array
    {
        return $this->positional;
    }

    /**
     * @return array<string, Expression>
     */
    public function getNamed(): array
    {
        return $this->named;
    }

    public function getRest(): ?Expression
    {
        return $this->rest;
    }

    public function getKeywordRest(): ?Expression
    {
        return $this->keywordRest;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }
}
