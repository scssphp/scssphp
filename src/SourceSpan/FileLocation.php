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
final class FileLocation extends SourceLocationMixin
{
    private readonly SourceFile $file;

    private readonly int $offset;

    public function __construct(SourceFile $file, int $offset)
    {
        $this->file = $file;
        $this->offset = $offset;
    }

    public function getFile(): SourceFile
    {
        return $this->file;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLine(): int
    {
        return $this->file->getLine($this->offset);
    }

    public function getColumn(): int
    {
        return $this->file->getColumn($this->offset);
    }

    public function getSourceUrl(): ?UriInterface
    {
        return $this->file->getSourceUrl();
    }

    public function pointSpan(): FileSpan
    {
        return new ConcreteFileSpan($this->file, $this->offset, $this->offset);
    }
}
