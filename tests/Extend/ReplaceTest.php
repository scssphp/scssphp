<?php

namespace ScssPhp\ScssPhp\Tests\Extend;

use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Ast\Selector\SelectorList;
use ScssPhp\ScssPhp\Extend\ConcreteExtensionStore;
use ScssPhp\ScssPhp\Util\SpanUtil;

class ReplaceTest extends TestCase
{
    /**
     * @dataProvider provideReplacedSelectors
     */
    public function testReplaceSelector(string $expected, string $selector, string $original, string $replacement)
    {
        $selectorSelector = SelectorList::parse($selector);
        $sourceSelector = SelectorList::parse($replacement);
        $targetsSelector = SelectorList::parse($original);

        $replaced = ConcreteExtensionStore::replace($selectorSelector, $sourceSelector, $targetsSelector, SpanUtil::bogusSpan());

        $this->assertEquals($expected, (string) $replaced);
    }

    /**
     * Provide selector unification tests taken from sass-spec in spec/core_functions/selector/replace
     */
    public static function provideReplacedSelectors(): iterable
    {
        yield 'simple' => ['d', 'c', 'c', 'd'];
        yield 'compound' => ['e.d', 'c.d', 'c', 'e'];
        yield 'complex' => ['c e f, e c f', 'c d', 'd', 'e f'];
        yield 'selector_pseudo/is' => [':is(d)', ':is(c)', 'c', 'd'];
        yield 'selector_pseudo/where' => [':where(d)', ':where(c)', 'c', 'd'];
        yield 'selector_pseudo/matches' => [':matches(d)', ':matches(c)', 'c', 'd'];
        yield 'selector_pseudo/not' => [':not(d)', ':not(c)', 'c', 'd'];
        yield 'no_op' => ['c', 'c', 'd', 'e'];
        yield 'partial_no_op' => ['c, e', 'c, d', 'd', 'e'];
        yield 'format/input/multiple_extendees/compound' => ['e', 'c.d', 'c.d', 'e'];
        yield 'format/input/multiple_extendees/list' => ['e', 'c.d', 'c, .d', 'e'];
        yield 'format/input/multiple_extendees/list_of_compound' => ['.g', 'c.d.e.f', 'c.d, .e.f', '.g'];
    }
}
