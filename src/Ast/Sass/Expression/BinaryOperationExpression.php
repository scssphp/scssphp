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
 * A binary operator, as in `1 + 2` or `$this and $other`.
 *
 * @internal
 */
final class BinaryOperationExpression implements Expression
{
    /**
     * @var BinaryOperator::*
     * @readonly
     */
    private $operator;

    /**
     * @var Expression
     * @readonly
     */
    private $left;

    /**
     * @var Expression
     * @readonly
     */
    private $right;

    /**
     * Whether this is a dividedBy operation that may be interpreted as slash-separated numbers.
     *
     * @var bool
     */
    private $allowsSlash = false;

    /**
     * @param BinaryOperator::* $operator
     */
    public function __construct(string $operator, Expression $left, Expression $right)
    {
        $this->operator = $operator;
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * Creates a dividedBy operation that may be interpreted as slash-separated numbers.
     */
    public static function slash(Expression $left, Expression $right): self
    {
        $operation = new self(BinaryOperator::DIVIDED_BY, $left, $right);
        $operation->allowsSlash = true;

        return $operation;
    }

    /**
     * @return BinaryOperator::*
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getLeft(): Expression
    {
        return $this->left;
    }

    public function getRight(): Expression
    {
        return $this->right;
    }

    public function allowsSlash(): bool
    {
        return $this->allowsSlash;
    }

    public function getSpan(): FileSpan
    {
        $left = $this->left;

        while ($left instanceof BinaryOperationExpression) {
            $left = $left->left;
        }

        $right = $this->right;

        while ($right instanceof BinaryOperationExpression) {
            $right = $right->right;
        }

        $leftSpan = $left->getSpan();
        $rightSpan = $right->getSpan();

        return $leftSpan->expand($rightSpan);
    }

    public function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitBinaryOperationExpression($this);
    }

    public function __toString(): string
    {
        $buffer = '';

        $leftNeedsParens = $this->left instanceof BinaryOperationExpression && BinaryOperator::getPrecedence($this->left->getOperator()) < BinaryOperator::getPrecedence($this->operator);
        if ($leftNeedsParens) {
            $buffer .= '(';
        }
        $buffer .= $this->left;
        if ($leftNeedsParens) {
            $buffer .= ')';
        }

        $buffer .= ' ';
        $buffer .= $this->operator;
        $buffer .= ' ';

        $rightNeedsParens = $this->right instanceof BinaryOperationExpression && BinaryOperator::getPrecedence($this->right->getOperator()) <= BinaryOperator::getPrecedence($this->operator);
        if ($rightNeedsParens) {
            $buffer .= '(';
        }
        $buffer .= $this->right;
        if ($rightNeedsParens) {
            $buffer .= ')';
        }

        return $buffer;
    }
}
