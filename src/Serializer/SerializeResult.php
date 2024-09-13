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

namespace ScssPhp\ScssPhp\Serializer;

/**
 * The result of converting a CSS AST to CSS text.
 *
 * @internal
 */
final class SerializeResult
{
    public function __construct(
        public readonly string $css,
    ) {
    }
}
