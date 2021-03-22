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

namespace ScssPhp\ScssPhp\Ast\Sass\SupportsCondition;

use ScssPhp\ScssPhp\Ast\Sass\Expression;
use ScssPhp\ScssPhp\Ast\Sass\SupportsCondition;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;

/**
 * A condition that selects for browsers where a given declaration is
 * supported.
 *
 * @internal
 */
final class SupportsDeclaration implements SupportsCondition
{
    /**
     * The name of the declaration being tested.
     *
     * @var Expression
     * @readonly
     */
    private $name;

    /**
     * The value of the declaration being tested.
     *
     * @var Expression
     * @readonly
     */
    private $value;

    /**
     * @var FileSpan
     * @readonly
     */
    private $span;

    public function __construct(Expression $name, Expression $value, FileSpan $span)
    {
        $this->name = $name;
        $this->value = $value;
        $this->span = $span;
    }

    public function getName(): Expression
    {
        return $this->name;
    }

    public function getValue(): Expression
    {
        return $this->value;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }
}
