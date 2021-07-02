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

use ScssPhp\ScssPhp\Util\Path;

/**
 * @internal
 */
final class FileSpan
{
    private $file;
    private $start;
    private $end;

    /**
     * @param SourceFile $file
     * @param int        $start The offset of the beginning of the span.
     * @param int        $end   The offset of the end of the span.
     */
    public function __construct(SourceFile $file, int $start, int $end)
    {
        $this->file = $file;
        $this->start = $start;
        $this->end = $end;
    }

    public function getFile(): SourceFile
    {
        return $this->file;
    }

    public function getLength(): int
    {
        return $this->end - $this->start;
    }

    public function getStart(): SourceLocation
    {
        return new SourceLocation($this->file, $this->start);
    }

    public function getEnd(): SourceLocation
    {
        return new SourceLocation($this->file, $this->end);
    }

    public function getText(): string
    {
        return $this->file->getText($this->start, $this->end);
    }

    public function expand(FileSpan $other): FileSpan
    {
        if ($this->file->getSourceUrl() !== $other->file->getSourceUrl()) {
            throw new \InvalidArgumentException('Source map URLs don\'t match.');
        }

        $start = min($this->start, $other->start);
        $end = max($this->end, $other->end);

        return new FileSpan($this->file, $start, $end);
    }

    /**
     * Formats $message in a human-friendly way associated with this span.
     *
     * @param string $message
     *
     * @return string
     */
    public function message(string $message): string
    {
        $startLine = $this->getStart()->getLine() + 1;
        $startColumn = $this->getStart()->getColumn() + 1;
        $sourceUrl = $this->file->getSourceUrl();

        $buffer = "line $startLine, column $startColumn";

        if ($sourceUrl !== null) {
            $prettyUri = Path::prettyUri($sourceUrl);
            $buffer .= " of $prettyUri";
        }

        $buffer .= ": $message";

        // TODO implement the highlighting of a code snippet

        return $buffer;
    }
}
