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
final class SimpleSourceLocation extends SourceLocationMixin
{
    private readonly int $offset;
    private readonly int $line;
    private readonly int $column;

    private readonly ?UriInterface $sourceUrl;

    /**
     * Creates a new location indicating $offset within $sourceUrl.
     *
     * $line and $column default to assuming the source is a single line. This
     * means that $line defaults to 0 and $column defaults to $offset.
     */
    public function __construct(int $offset, ?UriInterface $sourceUrl = null, ?int $line = null, ?int $column = null)
    {
        $this->offset = $offset;
        $this->line = $line ?? 0;
        $this->column = $column ?? $offset;
        $this->sourceUrl = $sourceUrl;

        if ($offset < 0) {
            throw new \OutOfRangeException('Offset may not be negative.');
        }

        if ($line !== null && $line < 0) {
            throw new \OutOfRangeException('Line may not be negative.');
        }

        if ($column !== null && $column < 0) {
            throw new \OutOfRangeException('Column may not be negative.');
        }
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getColumn(): int
    {
        return $this->column;
    }

    public function getSourceUrl(): ?UriInterface
    {
        return $this->sourceUrl;
    }
}
