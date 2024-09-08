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
final class SourceFile
{
    private readonly string $string;

    private readonly ?UriInterface $sourceUrl;

    /**
     * @var list<int>
     */
    private readonly array $lineStarts;

    /**
     * The 0-based last line that was returned by {@see getLine}
     *
     * This optimizes computation for successive accesses to
     * the same line or to the next line.
     * It is stored as 0-based to correspond to the indices
     * in {@see $lineStarts}.
     *
     * @var int|null
     */
    private ?int $cachedLine = null;

    public static function fromString(string $content, ?UriInterface $sourceUrl = null): SourceFile
    {
        return new SourceFile($content, $sourceUrl);
    }

    public function __construct(string $content, ?UriInterface $sourceUrl = null)
    {
        $this->string = $content;
        $this->sourceUrl = $sourceUrl;

        // Extract line starts
        $lineStarts = [0];

        if ($content === '') {
            $this->lineStarts = $lineStarts;
            return;
        }

        $prev = 0;

        while (($pos = strpos($content, "\n", $prev)) !== false) {
            $lineStarts[] = $pos + 1;
            $prev = $pos + 1;
        }

        $lineStarts[] = \strlen($content);

        if (!str_ends_with($content, "\n")) {
            $lineStarts[] = \strlen($content) + 1;
        }

        $this->lineStarts = $lineStarts;
    }

    public function getLength(): int
    {
        return \strlen($this->string);
    }

    /**
     * The number of lines in the file.
     */
    public function getLines(): int
    {
        return \count($this->lineStarts);
    }

    public function span(int $start, ?int $end = null): FileSpan
    {
        if ($end === null) {
            $end = \strlen($this->string);
        }

        return new ConcreteFileSpan($this, $start, $end);
    }

    public function location(int $offset): FileLocation
    {
        if ($offset < 0) {
            throw new \OutOfRangeException("Offset may not be negative, was $offset.");
        }

        if ($offset > \strlen($this->string)) {
            $fileLength = \strlen($this->string);

            throw new \OutOfRangeException("Offset $offset must not be greater than the number of characters in the file, $fileLength.");
        }

        return new FileLocation($this, $offset);
    }

    public function getSourceUrl(): ?UriInterface
    {
        return $this->sourceUrl;
    }

    public function getString(): string
    {
        return $this->string;
    }

    /**
     * The 0-based line corresponding to that offset.
     */
    public function getLine(int $offset): int
    {
        if ($offset < 0) {
            throw new \OutOfRangeException('Position cannot be negative');
        }

        if ($offset > \strlen($this->string)) {
            throw new \OutOfRangeException('Position cannot be greater than the number of characters in the string.');
        }

        if ($this->isNearCacheLine($offset)) {
            assert($this->cachedLine !== null);

            return $this->cachedLine;
        }

        $low = 0;
        $high = \count($this->lineStarts);

        while ($low < $high) {
            $mid = (int) (($high + $low) / 2);

            if ($offset < $this->lineStarts[$mid]) {
                $high = $mid - 1;
                continue;
            }

            if ($offset >= $this->lineStarts[$mid + 1]) {
                $low = $mid + 1;
                continue;
            }

            $this->cachedLine = $mid;

            return $this->cachedLine;
        }

        $this->cachedLine = $low;

        return $this->cachedLine;
    }

    /**
     * Returns `true` if $offset is near {@see $cachedLine}.
     *
     * Checks on {@see $cachedLine} and the next line. If it's on the next line, it
     * updates {@see $cachedLine} to point to that.
     */
    private function isNearCacheLine(int $offset): bool
    {
        if ($this->cachedLine === null) {
            return false;
        }

        if ($offset < $this->lineStarts[$this->cachedLine]) {
            return false;
        }

        if (
            $this->cachedLine >= \count($this->lineStarts) - 1 ||
            $offset < $this->lineStarts[$this->cachedLine + 1]
        ) {
            return true;
        }

        if (
            $this->cachedLine >= \count($this->lineStarts) - 2 ||
            $offset < $this->lineStarts[$this->cachedLine + 2]
        ) {
            ++$this->cachedLine;

            return true;
        }

        return false;
    }

    /**
     * The 0-based column of that offset.
     */
    public function getColumn(int $offset): int
    {
        $line = $this->getLine($offset);

        return $offset - $this->lineStarts[$line];
    }

    /**
     * Gets the offset for a line and column.
     */
    public function getOffset(int $line, int $column = 0): int
    {
        if ($line < 0) {
            throw new \OutOfRangeException('Line may not be negative.');
        }

        if ($line >= \count($this->lineStarts)) {
            throw new \OutOfRangeException('Line must be less than the number of lines in the file.');
        }

        if ($column < 0) {
            throw new \OutOfRangeException('Column may not be negative.');
        }

        $result = $this->lineStarts[$line] + $column;

        if ($result > \strlen($this->string) || ($line + 1 < \count($this->lineStarts) && $result >= $this->lineStarts[$line + 1])) {
            throw new \OutOfRangeException("Line $line doesn't have $column columns.");
        }

        return $result;
    }

    /**
     * Returns the text of the file from $start to $end (exclusive).
     *
     * If $end isn't passed, it defaults to the end of the file.
     */
    public function getText(int $start, ?int $end = null): string
    {
        if ($end !== null) {
            if ($end < $start) {
                throw new \InvalidArgumentException("End $end must come after start $start.");
            }

            if ($end > $this->getLength()) {
                throw new \OutOfRangeException("End $end not be greater than the number of characters in the file, {$this->getLength()}.");
            }
        }

        if ($start < 0) {
            throw new \OutOfRangeException("Start may not be negative, was $start.");
        }

        return Util::substring($this->string, $start, $end);
    }
}
