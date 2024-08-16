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

namespace ScssPhp\ScssPhp\SourceSpan;

use League\Uri\Contracts\UriInterface;

/**
 * @internal
 */
interface SourceLocation
{
    public function getOffset(): int;

    /**
     * The 0-based line of that location
     */
    public function getLine(): int;

    /**
     * The 0-based column of that location
     */
    public function getColumn(): int;

    public function getSourceUrl(): ?UriInterface;
}
