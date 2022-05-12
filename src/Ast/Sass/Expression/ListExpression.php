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
use ScssPhp\ScssPhp\Value\ListSeparator;
use ScssPhp\ScssPhp\Visitor\ExpressionVisitor;

/**
 * A list literal.
 *
 * @internal
 */
final class ListExpression implements Expression
{
    /**
     * @var Expression[]
     * @readonly
     */
    private $contents;

    /**
     * @var ListSeparator::*
     * @readonly
     */
    private $separator;

    /**
     * @var FileSpan
     * @readonly
     */
    private $span;

    /**
     * @var bool
     * @readonly
     */
    private $brackets;

    /**
     * ListExpression constructor.
     *
     * @param Expression[] $contents
     * @param ListSeparator::* $separator
     */
    public function __construct(array $contents, string $separator, FileSpan $span, bool $brackets = false)
    {
        $this->contents = $contents;
        $this->separator = $separator;
        $this->span = $span;
        $this->brackets = $brackets;
    }

    /**
     * @return Expression[]
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * @return ListSeparator::*
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function hasBrackets(): bool
    {
        return $this->brackets;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitListExpression($this);
    }

    public function __toString(): string
    {
        $buffer = '';
        if ($this->hasBrackets()) {
            $buffer .= '[';
        }

        $buffer .= implode($this->separator === ListSeparator::COMMA ? ', ' : ' ', array_map(function ($element) {
            return $this->elementNeedsParens($element) ? "($element)" : (string) $element;
        }, $this->contents));

        if ($this->hasBrackets()) {
            $buffer .= ']';
        }

        return $buffer;
    }

    /**
     * Returns whether $expression, contained in $this, needs parentheses when
     * printed as Sass source.
     */
    private function elementNeedsParens(Expression $expression): bool
    {
        if ($expression instanceof ListExpression) {
            if (\count($expression->contents) < 2) {
                return false;
            }

            if ($expression->brackets) {
                return false;
            }

            return $this->separator === ListSeparator::COMMA ? $expression->separator === ListSeparator::COMMA : $expression->separator !== ListSeparator::UNDECIDED;
        }

        if ($this->separator !== ListSeparator::SPACE) {
            return false;
        }

        if ($expression instanceof UnaryOperationExpression) {
            return $expression->getOperator() === UnaryOperator::PLUS || $expression->getOperator() === UnaryOperator::MINUS;
        }

        return false;
    }
}
