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
 * An argument declared as part of an {@see ArgumentDeclaration}.
 *
 * @internal
 */
final class Argument implements SassNode
{
    private $name;
    private $defaultValue;
    private $span;

    public function __construct(string $name, FileSpan $span, ?Expression $defaultValue = null)
    {
        $this->name = $name;
        $this->defaultValue = $defaultValue;
        $this->span = $span;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDefaultValue(): ?Expression
    {
        return $this->defaultValue;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }
}
