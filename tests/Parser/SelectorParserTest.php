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

namespace ScssPhp\ScssPhp\Tests\Parser;

use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Ast\Selector\CompoundSelector;
use ScssPhp\ScssPhp\Ast\Selector\SelectorList;
use ScssPhp\ScssPhp\Ast\Selector\SimpleSelector;

class SelectorParserTest extends TestCase
{
    /**
     * @dataProvider provideSimpleSelectors
     */
    public function testSimpleSelector(string $selector)
    {
        $this->assertInstanceOf(SimpleSelector::class, SimpleSelector::parse($selector));
    }

    /**
     * @dataProvider provideSimpleSelectors
     * @dataProvider provideCompoundSelectors
     */
    public function testCompoundSelector(string $selector)
    {
        $this->assertInstanceOf(CompoundSelector::class, CompoundSelector::parse($selector));
    }

    /**
     * @dataProvider provideSimpleSelectors
     * @dataProvider provideCompoundSelectors
     * @dataProvider provideSelectorLists
     */
    public function testSelectorList(string $selector)
    {
        $this->assertInstanceOf(SelectorList::class, SelectorList::parse($selector));
    }

    public static function provideSimpleSelectors(): iterable
    {
        yield ['b'];
        yield ['a|b'];
        yield ['|b'];
        yield ['*|b'];
        yield ['*'];
        yield ['a|*'];
        yield ['|*'];
        yield ['*|*'];
        yield ['.foo'];
        yield ['#bar'];
        yield ['[a]'];
        yield ['[*|a]'];
        yield ['[c|a]'];
        yield ['[a=b]'];
        yield ['[a=\'b\']'];
        yield ['[a="b"]'];
        yield ['[a|=b]'];
        yield ['[a~=b]'];
        yield ['[a*=b]'];
        yield ['[a^=b]'];
        yield ['[a$=b]'];
        yield ['%a'];
        yield ['&'];
        yield ['&a'];
        yield ['&2'];
        yield [':before'];
        yield ['::before'];
        yield ['::slotted(.foo bar)'];
        yield [':not(.foo bar)'];
        yield [':where(.foo bar)'];
        yield [':nth-child(n)'];
        yield [':nth-child(2n)'];
        yield [':nth-child(2n+5)'];
        yield [':nth-child(-2n+5)'];
        yield [':nth-child(odd)'];
        yield [':nth-child(even)'];
        yield [':nth-child(2n of .foo, bar)'];
        yield [':last-child'];
    }

    public static function provideCompoundSelectors(): iterable
    {
        yield ['a.b'];
        yield ['a#b'];
        yield ['a#b::before:hover'];
    }

    public static function provideSelectorLists(): iterable
    {
        yield ['a b'];
        yield ['a > .b'];
        yield ['a ~ .b'];
        yield ['a + .b'];
        yield ['a, .b'];
        yield ["a,\n.b"];
        yield ['a,'];
        yield ['a, , .b'];
    }
}
