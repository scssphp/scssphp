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
 * A mixin for easily implementing {@see SourceLocation}.
 *
 * @internal
 */
abstract class SourceLocationMixin implements SourceLocation
{
    public function distance(SourceLocation $other): int
    {
        if (!Util::isSameUrl($this->getSourceUrl(), $other->getSourceUrl())) {
            throw new \InvalidArgumentException("Source URLs \"{$this->getSourceUrl()}\" and \"{$other->getSourceUrl()}\" don't match.");
        }

        return abs($this->getOffset() - $other->getOffset());
    }

    public function pointSpan(): SourceSpan
    {
        return new SimpleSourceSpan($this, $this, '');
    }

    public function compareTo(SourceLocation $other): int
    {
        if (!Util::isSameUrl($this->getSourceUrl(), $other->getSourceUrl())) {
            throw new \InvalidArgumentException("Source URLs \"{$this->getSourceUrl()}\" and \"{$other->getSourceUrl()}\" don't match.");
        }

        return $this->getOffset() - $other->getOffset();
    }
}
