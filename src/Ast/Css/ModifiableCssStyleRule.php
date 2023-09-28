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

namespace ScssPhp\ScssPhp\Ast\Css;

use ScssPhp\ScssPhp\Ast\Selector\SelectorList;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Util\Box;
use ScssPhp\ScssPhp\Util\EquatableUtil;
use ScssPhp\ScssPhp\Visitor\ModifiableCssVisitor;

/**
 * A modifiable version of {@see CssStyleRule} for use in the evaluation step.
 *
 * @internal
 */
final class ModifiableCssStyleRule extends ModifiableCssParentNode implements CssStyleRule
{
    /**
     * A reference to the modifiable selector list provided by the extension
     * store, which may update it over time as new extensions are applied.
     *
     * @var Box<SelectorList>
     */
    private readonly Box $selector;

    private readonly SelectorList $originalSelector;

    private readonly FileSpan $span;

    /**
     * @param Box<SelectorList> $selector
     */
    public function __construct(Box $selector, FileSpan $span, ?SelectorList $originalSelector = null)
    {
        parent::__construct();
        $this->selector = $selector;
        $this->originalSelector = $originalSelector ?? $selector->getValue();
        $this->span = $span;
    }

    public function getSelector(): SelectorList
    {
        return $this->selector->getValue();
    }

    public function getOriginalSelector(): SelectorList
    {
        return $this->originalSelector;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function accept(ModifiableCssVisitor $visitor)
    {
        return $visitor->visitCssStyleRule($this);
    }

    public function equalsIgnoringChildren(ModifiableCssNode $other): bool
    {
        return $other instanceof ModifiableCssStyleRule && EquatableUtil::equals($this->selector, $other->selector);
    }

    public function copyWithoutChildren(): ModifiableCssStyleRule
    {
        return new ModifiableCssStyleRule($this->selector, $this->span, $this->originalSelector);
    }
}
