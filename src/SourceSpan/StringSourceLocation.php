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

/**
 * @internal
 */
final class StringSourceLocation implements SourceLocation
{
    /**
     * @var int
     * @readonly
     */
    private $offset;

    /**
     * @var string|null
     * @readonly
     */
    private $sourceUrl;

    public function __construct(int $offset, ?string $sourceUrl = null)
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

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }
}
