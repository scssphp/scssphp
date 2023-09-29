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

use ScssPhp\ScssPhp\Util\ErrorUtil;
use ScssPhp\ScssPhp\Util\Path;

/**
 * @internal
 */
final class ConcreteFileSpan implements FileSpan
{
    private readonly SourceFile $file;

    private readonly int $start;

    private readonly int $end;

    /**
     * @param int $start The offset of the beginning of the span.
     * @param int $end   The offset of the end of the span.
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

    public function getSourceUrl(): ?string
    {
        return $this->file->getSourceUrl();
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
        if ($this->file->getSourceUrl() !== $other->getFile()->getSourceUrl()) {
            throw new \InvalidArgumentException('Source map URLs don\'t match.');
        }

        $start = min($this->start, $other->getStart()->getOffset());
        $end = max($this->end, $other->getEnd()->getOffset());

        return new ConcreteFileSpan($this->file, $start, $end);
    }

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

    public function subspan(int $start, ?int $end = null): FileSpan
    {
        ErrorUtil::checkValidRange($start, $end, $this->getLength());

        if ($start === 0 && ($end === null || $end === $this->getLength())) {
            return $this;
        }

        return $this->file->span($this->start + $start, $end === null ? $this->end : $this->start + $end);
    }
}
