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

namespace ScssPhp\ScssPhp\Tests\Extend;

use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Ast\Selector\SelectorList;

/**
 * Selector nesting tests, taken from sass-spec
 * TODO Remove those tests once the compiler uses this implementation of selectors, as sass-spec covers that logic
 */
class NestingTest extends TestCase
{
    /**
     * @dataProvider provideNestingTests
     */
    public function testNest(string $expected, string $parent, string $child)
    {
        $parentSelector = SelectorList::parse($parent);
        $childSelector = SelectorList::parse($child);

        $this->assertSame($expected, (string) $childSelector->resolveParentSelectors($parentSelector));
    }

    /**
     * Provide selector nesting tests taken from sass-spec in spec/core_functions/selector/nest/
     */
    public static function provideNestingTests(): iterable
    {
        yield ['c d', 'c', 'd'];
        // combinators
        yield ['> c d', '> c', 'd'];
        yield ['c + d', 'c', '+ d'];
        yield ['c ~ d', 'c ~', 'd'];
        yield ['c d >', 'c', 'd >'];
        yield ['c > > d e', 'c > > d', 'e'];
        yield ['~ ~ c d', '~ ~ c', 'd'];
        yield ['c + > d', 'c', '+ > d'];
        yield ['c > ~ d', 'c > ~', 'd'];
        yield ['c d + >', 'c', 'd + >'];
        yield ['~ c', '~', 'c'];
        yield ['c +', 'c', '+'];
        // parent
        yield ['c', 'c', '&'];
        yield ['c.d', 'c', '&.d'];
        yield ['cd', 'c', '&d'];
        yield ['d c.e', 'c', 'd &.e'];
        yield ['e c d.f', 'c d', 'e &.f'];
        yield [':is(c)', 'c', ':is(&)'];
        yield [':where(c)', 'c', ':where(&)'];
        yield [':matches(c)', 'c', ':matches(&)'];
        yield [':is(c d)', 'c d', ':is(&)'];
        yield [':where(c d)', 'c d', ':where(&)'];
        yield [':matches(c d)', 'c d', ':matches(&)'];
        yield ['c.d c.e', 'c', '&.d &.e'];
        yield ['c.d, c e', 'c', '&.d, e'];
        // list
        yield ['c e, d e', 'c, d', 'e'];
        yield ['c d, c e', 'c', 'd, e'];
        yield ['c, d', 'c, d', '&'];
        yield ['c.e, d.e', 'c, d', '&.e'];
        yield ['ce, de', 'c, d', '&e'];
        yield ['e c.f, e d.f', 'c, d', 'e &.f'];
        yield [':is(c, d)', 'c, d', ':is(&)'];
        yield [':where(c, d)', 'c, d', ':where(&)'];
        yield [':matches(c, d)', 'c, d', ':matches(&)'];
        yield ['c.e c.f, c.e d.f, d.e c.f, d.e d.f', 'c, d', '&.e &.f'];
        yield ['c.e, c f, d.e, d f', 'c, d', '&.e, f'];
    }
}
