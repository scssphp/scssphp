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
 * Selector comparison tests, taken from sass-spec
 * TODO Remove those tests once the compiler uses this implementation of selectors, as sass-spec covers that logic
 */
class SuperselectorTest extends TestCase
{
    /**
     * @dataProvider provideSuperselectorTests
     */
    public function testIsSuperselector(bool $expected, string $super, string $sub)
    {
        $firstSelector = SelectorList::parse($super);
        $secondSelector = SelectorList::parse($sub);

        $this->assertSame($expected, $firstSelector->isSuperselector($secondSelector));
    }

    /**
     * Provide selector unification tests taken from sass-spec in spec/core_functions/selector/is_superselector/
     */
    public static function provideSuperselectorTests(): iterable
    {
        // simple/pseudo/selector_arg/any
        yield [true, ':any(c d, e f, g h)', 'c d.i, e j f'];
        yield [false, ':any(c d.i, e j f)', 'c d, e f, g h'];
        yield [true, ':-pfx-any(c d, e f, g h)', 'c d.i, e j f'];
        yield [false, ':-pfx-any(c d.i, e j f)', 'c d, e f, g h'];
        // simple/pseudo/selector_arg/current
        yield [false, ':current(c d, e f, g h)', ':current(c d.i, e j f)'];
        yield [false, ':current(c d.i, e j f)', ':current(c d, e f, g h)'];
        yield [true, ':current(c d, e f)', ':current(c d, e f)'];
        yield [false, ':current(c d, e f)', 'c d, e f'];
        yield [false, ':-pfx-current(c d, e f, g h)', ':-pfx-current(c d.i, e j f)'];
        yield [false, ':-pfx-current(c d.i, e j f)', ':-pfx-current(c d, e f, g h)'];
        yield [true, ':-pfx-current(c d, e f)', ':-pfx-current(c d, e f)'];
        // simple/pseudo/selector_arg/has
        yield [true, ':has(c d, e f, g h)', ':has(c d.i, e j f)'];
        yield [false, ':has(c d.i, e j f)', ':has(c d, e f, g h)'];
        yield [false, ':has(c d, e f, g h)', 'c d.i, e j f'];
        yield [true, ':-pfx-has(c d, e f, g h)', ':-pfx-has(c d.i, e j f)'];
        yield [false, ':-pfx-has(c d.i, e j f)', ':-pfx-has(c d, e f, g h)'];
        // simple/pseudo/selector_arg/host
        yield [true, ':host(c d, e f, g h)', ':host(c d.i, e j f)'];
        yield [false, ':host(c d.i, e j f)', ':host(c d, e f, g h)'];
        yield [false, ':host(c d, e f, g h)', 'c d, e f, g h'];
        yield [true, ':-pfx-host(c d, e f, g h)', ':-pfx-host(c d.i, e j f)'];
        yield [false, ':-pfx-host(c d.i, e j f)', ':-pfx-host(c d, e f, g h)'];
        // simple/pseudo/selector_arg/host-context
        yield [true, ':host-context(c d, e f, g h)', ':host-context(c d.i, e j f)'];
        yield [false, ':host-context(c d.i, e j f)', ':host-context(c d, e f, g h)'];
        yield [false, ':host-context(c d, e f, g h)', 'c d, e f, g h'];
        yield [true, ':-pfx-host-context(c d, e f, g h)', ':-pfx-host-context(c d.i, e j f)'];
        yield [false, ':-pfx-host-context(c d.i, e j f)', ':-pfx-host-context(c d, e f, g h)'];
        // simple/pseudo/selector_arg/is
        yield [true, ':is(c)', 'c'];
        yield [false, ':is(c)', 'd'];
        yield [true, ':is(c.e)', 'c.d.e'];
        yield [false, ':is(c.d.e)', 'c e'];
        yield [true, ':is(c e)', 'c d e'];
        yield [false, ':is(c d e)', 'c e'];
        yield [true, ':is(c d, e f, g h)', 'c d, e f'];
        yield [false, ':is(c d, e f)', 'c d, e f, g h'];
        yield [true, ':is(c d, e f, g h)', ':is(c d.i, e j f)'];
        yield [false, ':is(c d.i, e j f)', ':is(c d, e f, g h)'];
        yield [true, ':-pfx-is(c d, e f, g h)', 'c d.i, e j f'];
        yield [false, ':pfx-is(c d.i, e j f)', 'c d, e f, g h'];
        yield [false, ':is(c, d)', ':any(c, d)'];
        yield [false, ':is(c, d)', ':-pfx-is(c, d)'];
        // simple/pseudo/selector_arg/matches
        yield [true, ':matches(c)', 'c'];
        yield [false, ':matches(c)', 'd'];
        yield [true, ':matches(c.e)', 'c.d.e'];
        yield [false, ':matches(c.d.e)', 'c e'];
        yield [true, ':matches(c e)', 'c d e'];
        yield [false, ':matches(c d e)', 'c e'];
        yield [true, ':matches(c d, e f, g h)', 'c d, e f'];
        yield [false, ':matches(c d, e f)', 'c d, e f, g h'];
        yield [true, ':matches(c d, e f, g h)', ':matches(c d.i, e j f)'];
        yield [false, ':matches(c d.i, e j f)', ':matches(c d, e f, g h)'];
        yield [true, ':-pfx-matches(c d, e f, g h)', 'c d.i, e j f'];
        yield [false, ':pfx-matches(c d.i, e j f)', 'c d, e f, g h'];
        yield [false, ':matches(c, d)', ':any(c, d)'];
        yield [false, ':matches(c, d)', ':-pfx-matches(c, d)'];
        // simple/pseudo/selector_arg/not
        yield [false, ':not(c d, e f, g h)', ':not(c d.i, e j f)'];
        yield [true, ':not(c d.i, e j f)', ':not(c d, e f, g h)'];
        yield [false, ':not(c d, e f, g h)', 'c d, e f, g h'];
        yield [true, ':not(c.d)', 'e'];
        yield [true, ':not(#c.d)', '#e'];
        yield [false, ':not(c d):not(e f):not(g h)', ':not(c d.i, e j f)'];
        yield [true, ':not(c d.i):not(e j f)', ':not(c d, e f, g h)'];
        yield [false, ':not(c d, e f, g h)', ':not(c d.i):not(e j f)'];
        yield [true, ':not(c d.i, e j f)', ':not(c d):not(e f):not(g h)'];
        yield [false, ':-pfx-not(c d, e f, g h)', ':-pfx-not(c d.i, e j f)'];
        yield [true, ':-pfx-not(c d.i, e j f)', ':-pfx-not(c d, e f, g h)'];
        // simple/pseudo/selector_arg/nth_child
        yield [true, ':nth-child(n+1 of c d, e f, g h)', ':nth-child(n+1 of c d.i, e j f)'];
        yield [false, ':nth-child(n+1 of c d.i, e j f)', ':nth-child(n+1 of c d, e f, g h)'];
        yield [false, ':nth-child(n+1 of c)', ':nth-child(n+2 of c)'];
        yield [true, 'c', ':nth-child(n+1 of c)'];
        yield [false, ':nth-child(n+1 of c d, e f, g h)', 'c d, e f, g h'];
        yield [true, ':-pfx-nth-child(n+1 of c d, e f, g h)', ':-pfx-nth-child(n+1 of c d.i, e j f)'];
        yield [false, ':-pfx-nth-child(n+1 of c d.i, e j f)', ':-pfx-nth-child(n+1 of c d, e f, g h)'];
        // simple/pseudo/selector_arg/nth_last_child
        yield [true, ':nth-last-child(n+1 of c d, e f, g h)', ':nth-last-child(n+1 of c d.i, e j f)'];
        yield [false, ':nth-last-child(n+1 of c d.i, e j f)', ':nth-last-child(n+1 of c d, e f, g h)'];
        yield [false, ':nth-last-child(n+1 of c)', ':nth-last-child(n+2 of c)'];
        yield [true, 'c', ':nth-last-child(n+1 of c)'];
        yield [false, ':nth-last-child(n+1 of c d, e f, g h)', 'c d, e f, g h'];
        yield [true, ':-pfx-nth-last-child(n+1 of c d, e f, g h)', ':-pfx-nth-last-child(n+1 of c d.i, e j f)'];
        yield [false, ':-pfx-nth-last-child(n+1 of c d.i, e j f)', ':-pfx-nth-last-child(n+1 of c d, e f, g h)'];
        // simple/pseudo/selector_arg/slotted
        yield [true, '::slotted(c d, e f, g h)', '::slotted(c d.i, e j f)'];
        yield [false, '::slotted(c d.i, e j f)', '::slotted(c d, e f, g h)'];
        yield [false, '::slotted(c d, e f, g h)', 'c d, e f, g h'];
        yield [true, '::-pfx-slotted(c d, e f, g h)', '::-pfx-slotted(c d.i, e j f)'];
        yield [false, '::-pfx-slotted(c d.i, e j f)', '::-pfx-slotted(c d, e f, g h)'];
        // simple/pseudo/arg
        yield [true, ':c(@#$)', ':c(@#$)'];
        yield [false, ':c(@#$)', ':d(@#$)'];
        yield [false, ':c(@#$)', ':c(*&^)'];
        yield [false, ':c(@#$)', ':c'];
        yield [true, '::c(@#$)', '::c(@#$)'];
        yield [false, '::c(@#$)', ':d(@#$)'];
        yield [false, '::c(@#$)', '::c(*&^)'];
        yield [false, '::c(@#$)', '::c'];
        // simple/pseudo/no_arg
        yield [true, ':c', ':c'];
        yield [false, ':c', ':d'];
        yield [false, ':c', '::c'];
        yield [true, '::c', '::c'];
        yield [false, '::c', '::d'];
        yield [false, '::c', ':c'];
        // simple/attribute
        yield [true, '[c=d]', '[c=d]'];
        yield [false, '[c=d]', '[e=d]'];
        yield [false, '[c=d]', '[c=e]'];
        yield [false, '[c=d]', '[c^=d]'];
        // simple/class
        yield [true, '.c', '.c'];
        yield [false, '.c', '.d'];
        // simple/id
        yield [true, '#c', '#c'];
        yield [false, '#c', '#d'];
        // simple/placeholder
        yield [true, '%c', '%c'];
        yield [false, '%c', '%d'];
        // simple/type
        yield [true, 'c', 'c'];
        yield [false, 'c', 'd'];
        yield [false, 'c', '*'];
        yield [true, 'c|d', 'c|d'];
        yield [false, 'c|d', 'e|d'];
        yield [false, 'c|d', 'd'];
        yield [false, 'c|d', '|d'];
        yield [false, 'c|d', '*|d'];
        yield [false, '|c', 'd|c'];
        yield [false, '|c', 'c'];
        yield [true, '|c', '|c'];
        yield [false, '|c', '*|c'];
        yield [true, '*|c', 'd|c'];
        yield [true, '*|c', 'c'];
        yield [true, '*|c', '|c'];
        yield [true, '*|c', '*|c'];
        // simple/universal
        yield [true, '*', '*'];
        yield [true, '*', 'c'];
        yield [true, '*', '.c'];
        yield [true, 'c|*', 'c|d'];
        yield [false, 'c|*', 'e|d'];
        yield [false, 'c|*', 'd'];
        yield [false, 'c|*', '|d'];
        yield [true, 'c|*', 'c|*'];
        yield [false, 'c|*', 'd|*'];
        yield [false, 'c|*', '*'];
        yield [false, 'c|*', '|*'];
        yield [false, 'c|*', '*|*'];
        yield [false, 'c|*', '.d'];
        yield [false, '|*', 'c|d'];
        yield [false, '|*', 'd'];
        yield [true, '|*', '|d'];
        yield [false, '|*', 'c|*'];
        yield [false, '|*', '*'];
        yield [true, '|*', '|*'];
        yield [false, '|*', '*|*'];
        yield [false, '|*', '.d'];
        yield [true, '*|*', 'c|d'];
        yield [true, '*|*', 'd'];
        yield [true, '*|*', '|d'];
        yield [true, '*|*', 'c|*'];
        yield [true, '*|*', '*'];
        yield [true, '*|*', '|*'];
        yield [true, '*|*', '*|*'];
        yield [true, '*|*', '.d'];
        // complex/descendant
        yield [true, 'c', 'd c'];
        yield [false, 'c d', 'd'];
        yield [true, 'c d', 'c d'];
        yield [true, 'c d', 'c.e d.f'];
        yield [false, 'c.e d.f', 'c d'];
        yield [true, 'c', 'd e c'];
        yield [true, 'd c', 'd e c'];
        yield [true, 'e c', 'd e c'];
        yield [false, 'f c', 'd e c'];
        yield [true, 'd c', 'd > c'];
        yield [false, 'd > c', 'd c'];
        yield [true, 'd c', 'd > e > c'];
        yield [true, 'e c', 'd > e > c'];
        yield [false, 'f c', 'd > e > c'];
        yield [true, 'a b c', 'a x b c'];
        yield [true, 'a b c', 'a x > b c'];
        yield [true, 'a b c', 'a x ~ b c'];
        yield [true, 'a b c', 'a x + b c'];
        // complex/sibling
        yield [true, 'c', 'd ~ c'];
        yield [false, 'c ~ d', 'd'];
        yield [true, 'c ~ d', 'c ~ d'];
        yield [true, 'c ~ d', 'c.e ~ d.f'];
        yield [false, 'c.e ~ d.f', 'c ~ d'];
        yield [true, 'c', 'd ~ e ~ c'];
        yield [true, 'd ~ c', 'd ~ e ~ c'];
        yield [true, 'e ~ c', 'd ~ e ~ c'];
        yield [false, 'f ~ c', 'd ~ e ~ c'];
        yield [true, 'd ~ c', 'd + c'];
        yield [false, 'd + c', 'd ~ c'];
        yield [true, 'd ~ c', 'd + e + c'];
        yield [true, 'e ~ c', 'd + e + c'];
        yield [false, 'f ~ c', 'd + e + c'];
        yield [false, 'a ~ b ~ c', 'a ~ x b ~ c'];
        yield [false, 'a ~ b ~ c', 'a ~ x > b ~ c'];
        yield [true, 'a ~ b ~ c', 'a ~ x ~ b ~ c'];
        yield [true, 'a ~ b ~ c', 'a ~ x + b ~ c'];
        // complex/adjacent_sibling
        yield [true, 'c', 'd + c'];
        yield [false, 'c + d', 'd'];
        yield [true, 'c + d', 'c + d'];
        yield [true, 'c + d', 'c.e + d.f'];
        yield [false, 'c.e + d.f', 'c + d'];
        yield [true, 'c', 'd + e + c'];
        yield [false, 'd + c', 'd + e + c'];
        yield [true, 'e + c', 'd + e + c'];
        yield [false, 'f + c', 'd + e + c'];
        yield [false, 'a + b + c', 'a + x b + c'];
        yield [false, 'a + b + c', 'a + x > b + c'];
        yield [false, 'a + b + c', 'a + x ~ b + c'];
        yield [false, 'a + b + c', 'a + x + b + c'];
        // complex/bogus
        yield [false, '> c', 'c'];
        yield [false, 'c', 'd + ~ c'];
        // complex/child
        yield [true, 'c', 'd > c'];
        yield [false, 'c > d', 'd'];
        yield [true, 'c > d', 'c > d'];
        yield [true, 'c > d', 'c.e > d.f'];
        yield [false, 'c.e > d.f', 'c > d'];
        yield [true, 'c', 'd > e > c'];
        yield [false, 'd > c', 'd > e > c'];
        yield [true, 'e > c', 'd > e > c'];
        yield [false, 'f > c', 'd > e > c'];
        yield [false, 'a > b > c', 'a > x b > c'];
        yield [false, 'a > b > c', 'a > x > b > c'];
        yield [false, 'a > b > c', 'a > x ~ b > c'];
        yield [false, 'a > b > c', 'a > x + b > c'];
        // compound
        yield [true, 'c', 'c.d'];
        yield [true, 'c.e', 'c:d.e'];
        yield [false, 'c.d', 'c'];
        yield [true, '::d', 'c::d'];
        yield [false, 'c', 'c::d'];
        yield [false, 'c::d', 'c'];
        yield [true, '.c::d', '.c.e::d'];
        yield [true, '::d:c', '::d:c:e'];
        yield [false, '.c.e::d', '.c::d'];
        yield [false, '::d:c:e', '::d:c'];
        yield [true, '::d:e', '::d:e'];
        yield [false, ':e::d', '::d:e'];
        yield [false, 'c', 'c:before'];
        yield [false, 'c', 'c:after'];
        yield [false, 'c', 'c:first-line'];
        yield [false, 'c', 'c:first-letter'];
        // list
        yield [false, 'c', 'c, d'];
        yield [true, 'c, d', 'c'];
        yield [true, 'c, d', 'c, d'];
        yield [true, 'c, d', 'c.e, d.f'];
        yield [false, 'c.e, d.f', 'c, d'];
        yield [true, '.c', 'd.c, e.c'];
        yield [true, 'c, d, e', 'd'];
        yield [true, 'c, d, e', 'e, c'];
        yield [true, 'c, d, e', 'd, c, e'];
        yield [false, 'c, d, e', 'c, f'];
    }
}
