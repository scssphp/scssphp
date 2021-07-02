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

use ScssPhp\ScssPhp\Ast\Sass\Import\DynamicImport;
use ScssPhp\ScssPhp\Ast\Sass\Statement;

/**
 * A {@see Statement} that can have child statements.
 *
 * This has a generic parameter so that its subclasses can choose whether or
 * not their children lists are nullable.
 *
 * @phpstan-template T
 *
 * @internal
 */
abstract class ParentStatement implements Statement
{
    /**
     * @var Statement[]|null
     * @phpstan-var T
     */
    private $children;
    private $declarations = false;

    /**
     * @param Statement[]|null $children
     * @phpstan-param T $children
     */
    public function __construct(?array $children)
    {
        $this->children = $children;

        if ($this->children === null) {
            return;
        }

        foreach ($children as $child) {
            if ($child instanceof VariableDeclaration || $child instanceof FunctionRule || $child instanceof MixinRule) {
                $this->declarations = true;
                break;
            }

            if ($child instanceof ImportRule) {
                foreach ($child->getImports() as $import) {
                    if ($import instanceof DynamicImport) {
                        $this->declarations = true;
                        break 2;
                    }
                }
            }
        }
    }

    /**
     * @return Statement[]|null
     *
     * @phpstan-return T
     */
    final public function getChildren()
    {
        return $this->children;
    }

    final public function hasDeclarations(): bool
    {
        return $this->declarations;
    }
}
