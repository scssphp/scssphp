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

use ScssPhp\ScssPhp\SourceSpan\FileSpan;

/**
 * A wrapper for {@see FileSpan} that allows an expensive creation process to be
 * deferred until the span is actually needed.
 *
 * @internal
 */
class LazyFileSpan implements FileSpan
{
    /**
     * @var \Closure(): FileSpan
     * @readonly
     */
    private readonly \Closure $builder;

    /**
     * @var FileSpan|null
     */
    private ?FileSpan $span = null;

    /**
     * @param \Closure(): FileSpan $builder
     */
    public function __construct(\Closure $builder)
    {
        $this->builder = $builder;
    }

    public function getSpan(): FileSpan
    {
        if ($this->span === null) {
            $this->span = ($this->builder)();
        }

        return $this->span;
    }

    public function getFile(): SourceFile
    {
        return $this->getSpan()->getFile();
    }

    public function getSourceUrl(): ?string
    {
        return $this->getSpan()->getSourceUrl();
    }

    public function getLength(): int
    {
        return $this->getSpan()->getLength();
    }

    public function getStart(): SourceLocation
    {
        return $this->getSpan()->getStart();
    }

    public function getEnd(): SourceLocation
    {
        return $this->getSpan()->getEnd();
    }

    public function getText(): string
    {
        return $this->getSpan()->getText();
    }

    public function expand(FileSpan $other): FileSpan
    {
        return $this->getSpan()->expand($other);
    }

    public function message(string $message): string
    {
        return $this->getSpan()->message($message);
    }

    public function subspan(int $start, ?int $end = null): FileSpan
    {
        return $this->getSpan()->subspan($start, $end);
    }
}
