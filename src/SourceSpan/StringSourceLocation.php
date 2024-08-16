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
final class StringSourceLocation implements SourceLocation
{
    private readonly int $offset;

    private readonly ?UriInterface $sourceUrl;

    public function __construct(int $offset, ?UriInterface $sourceUrl = null)
    {
        $this->offset = $offset;
        $this->sourceUrl = $sourceUrl;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLine(): int
    {
        return 0;
    }

    public function getColumn(): int
    {
        return $this->offset;
    }

    public function getSourceUrl(): ?UriInterface
    {
        return $this->sourceUrl;
    }
}
