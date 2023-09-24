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
 * Selector unification tests, taken from sass-spec
 * TODO Remove those tests once the compiler uses this implementation of selectors, as sass-spec covers that logic
 */
class UnificationTest extends TestCase
{
    /**
     * @dataProvider provideUnificationTests
     */
    public function testUnify($expected, $first, $second)
    {
        $firstSelector = SelectorList::parse($first);
        $secondSelector = SelectorList::parse($second);
        $unified = $firstSelector->unify($secondSelector);

        if ($expected === null) {
            $this->assertNull($unified);
        } else {
            $this->assertInstanceOf(SelectorList::class, $unified);
            $this->assertEquals($expected, (string) $unified);
        }
    }

    /**
     * Provide selector unification tests taken from sass-spec in spec/core_functions/selector/unify/
     */
    public static function provideUnificationTests(): iterable
    {
        // complex/combinators
        yield ['.e .c > .d.f', '.c > .d', '.e .f'];
        yield ['.c > .s1.s2', '.c > .s1', '.c .s2'];
        yield ['.c.s1-1 > .s1-2.s2', '.c.s1-1 > .s1-2', '.c .s2'];
        yield ['.c.s2-1 .c.s1-1 > .s1-2.s2-2', '.c.s1-1 > .s1-2', '.c.s2-1 .s2-2'];
        yield ['.e.c > .d.f', '.c > .d', '.e > .f'];
        yield ['.c.s1-1 > .s1-2.s2', '.c.s1-1 > .s1-2', '.c > .s2'];
        yield ['.c.s2-1.s1-1 > .s1-2.s2-2', '.c.s1-1 > .s1-2', '.c.s2-1 > .s2-2'];
        yield [null, '#s1-1 > .s1-2', '#s2-1 > .s2-2'];
        yield ['.c > .c ~ .s1.s2', '.c > .s1', '.c ~ .s2'];
        yield ['.c > .c + .s1.s2', '.c > .s1', '.c + .s2'];
        yield ['.c .c ~ .s1.s2', '.c ~ .s1', '.c .s2'];
        yield ['.c > .c ~ .s1.s2', '.c ~ .s1', '.c > .s2'];
        yield ['.c ~ .e ~ .d.f, .e ~ .c ~ .d.f, .e.c ~ .d.f', '.c ~ .d', '.e ~ .f'];
        yield ['.c ~ .s1.s2', '.c ~ .s1', '.c ~ .s2'];
        yield ['.c.s1-1 ~ .s1-2.s2', '.c.s1-1 ~ .s1-2', '.c ~ .s2'];
        yield ['.c.s1-1 ~ .c.s2-1 ~ .s1-2.s2-2, .c.s2-1 ~ .c.s1-1 ~ .s1-2.s2-2, .c.s2-1.s1-1 ~ .s1-2.s2-2', '.c.s1-1 ~ .s1-2', '.c.s2-1 ~ .s2-2'];
        yield ['#s1-1 ~ #s2-1 ~ .s1-2.s2-2, #s2-1 ~ #s1-1 ~ .s1-2.s2-2', '#s1-1 ~ .s1-2', '#s2-1 ~ .s2-2'];
        yield ['.c ~ .e + .d.f, .e.c + .d.f', '.c ~ .d', '.e + .f'];
        yield ['.c + .s1.s2', '.c ~ .s1', '.c + .s2'];
        yield ['.c.s1-1 ~ .c + .s1-2.s2, .c.s1-1 + .s1-2.s2', '.c.s1-1 ~ .s1-2', '.c + .s2'];
        yield ['.c.s1-1 ~ .c.s2-1 + .s1-2.s2-2, .c.s2-1.s1-1 + .s1-2.s2-2', '.c.s1-1 ~ .s1-2', '.c.s2-1 + .s2-2'];
        yield ['#s1-1 ~ #s2-1 + .s1-2.s2-2', '#s1-1 ~ .s1-2', '#s2-1 + .s2-2'];
        yield ['.c .c + .s1.s2', '.c + .s1', '.c .s2'];
        yield ['.c > .c + .s1.s2', '.c + .s1', '.c > .s2'];
        yield ['.e ~ .c + .d.f, .c.e + .d.f', '.c + .d', '.e ~ .f'];
        yield ['.c + .s1.s2', '.c + .s1', '.c ~ .s2'];
        yield ['.c.s1-1 + .s1-2.s2', '.c.s1-1 + .s1-2', '.c ~ .s2'];
        yield ['.c.s2-1 ~ .c.s1-1 + .s1-2.s2-2, .c.s1-1.s2-1 + .s1-2.s2-2', '.c.s1-1 + .s1-2', '.c.s2-1 ~ .s2-2'];
        yield ['#s2-1 ~ #s1-1 + .s1-2.s2-2', '#s1-1 + .s1-2', '#s2-1 ~ .s2-2'];
        yield ['.e.c + .d.f', '.c + .d', '.e + .f'];
        yield ['.c.s1-1 + .s1-2.s2', '.c.s1-1 + .s1-2', '.c + .s2'];
        yield ['.c.s2-1.s1-1 + .s1-2.s2-2', '.c.s1-1 + .s1-2', '.c.s2-1 + .s2-2'];
        yield [null, '#s1-1 + .s1-2', '#s2-1 + .s2-2'];
        yield ['> .c.d', '> .c', '.d'];
        yield ['~ .c.d', '.c', '~ .d'];
        yield ['+ .c.d', '+ .c', '+ .d'];
        yield [null, '+ ~ > .c', '> + ~ > > .d'];
        yield [null, '+ ~ > .c', '+ > ~ ~ > .d'];
        yield [null, '+ ~ > .c', '+ > ~ ~ .d'];
        yield ['.f .c > .g ~ .d + .e.h, .f .c > .d.g + .e.h', '.c > .d + .e', '.f .g ~ .h'];
        yield [null, '.c + ~ > .d', '.e + ~ > .f'];
        yield [null, '.c + ~ > .d', '.e > + ~ > > .f'];
        yield [null, '.c + ~ > .d', '.e + > ~ ~ > .f'];
        yield [null, '.c + ~ > .d', '.e + > ~ ~ .f'];
        // complex/distinct
        yield ['.c .e .d.f, .e .c .d.f', '.c .d', '.e .f'];
        yield ['.c .d .f .g .e.h, .f .g .c .d .e.h', '.c .d .e', '.f .g .h'];
        // complex/identical
        yield ['.c .s1.s2', '.c .s1', '.c .s2'];
        yield ['.c .s1-1 .s2-1 .s1-2.s2-2, .c .s2-1 .s1-1 .s1-2.s2-2', '.c .s1-1 .s1-2', '.c .s2-1 .s2-2'];
        yield ['.s1-1 .s2-1 .d .s1-2.s2-2, .s2-1 .s1-1 .d .s1-2.s2-2', '.s1-1 .d .s1-2', '.s2-1 .d .s2-2'];
        // complex/lcs
        yield ['.e .c .d .e .s1.s2', '.c .d .e .s1', '.e .c .d .s2'];
        yield ['.f .g .c .d .e .f .g .s1.s2', '.c .d .e .f .g .s1', '.f .g .c .d .e .s2'];
        yield ['.s1-1 .s2-1 .c .d .s1-2 .s2-2 .e .s1-3.s2-3, .s2-1 .s1-1 .c .d .s1-2 .s2-2 .e .s1-3.s2-3, .s1-1 .s2-1 .c .d .s2-2 .s1-2 .e .s1-3.s2-3, .s2-1 .s1-1 .c .d .s2-2 .s1-2 .e .s1-3.s2-3', '.s1-1 .c .d .s1-2 .e .s1-3', '.s2-1 .c .d .s2-2 .e .s2-3'];
        yield ['.s1-1 .c .s2-1 .d .s1-2 .e .s2-2 .s1-3.s2-3', '.s1-1 .c .d .s1-2 .e .s1-3', '.c .s2-1 .d .e .s2-2 .s2-3'];
        // complex/overlap
        yield ['.c.s1-1 .c.s2-1 .s1-2.s2-2, .c.s2-1 .c.s1-1 .s1-2.s2-2', '.c.s1-1 .s1-2', '.c.s2-1 .s2-2'];
        yield ['#s1-1.c #s2-1.c .s1-2.s2-2, #s2-1.c #s1-1.c .s1-2.s2-2', '#s1-1.c .s1-2', '#s2-1.c .s2-2'];
        yield ['#c.s2-1.s1-1 .s1-2.s2-2', '#c.s1-1 .s1-2', '#c.s2-1 .s2-2'];
        yield ['::s1-1.c ::s2-1.c .s1-2.s2-2, ::s2-1.c ::s1-1.c .s1-2.s2-2', '::s1-1.c .s1-2', '::s2-1.c .s2-2'];
        yield ['.s2-1.s1-1::c .s1-2.s2-2', '.s1-1::c .s1-2', '.s2-1::c .s2-2'];
        // complex/root
        yield [':root .d .c.e', ':root .c', '.d .e'];
        yield [':root .c .d.e', '.c .d', ':root .e'];
        yield [null, 'c:root .d', 'e:root .f'];
        yield ['c:root .d.e', 'c:root .d', ':root .e'];
        yield ['.e.c:root .d.f', '.c:root .d', '.e:root .f'];
        // complex/superselector
        yield ['.c.s1-1 .s1-2.s2', '.c.s1-1 .s1-2', '.c .s2'];
        yield ['.c.s1-1 .s1-2 .s2-1 .s1-3.s2-2, .c.s1-1 .s2-1 .s1-2 .s1-3.s2-2', '.c.s1-1 .s1-2 .s1-3', '.c .s2-1 .s2-2'];
        yield ['.s1-1 .s2-1 .c.s1-2 .s1-3.s2-2, .s2-1 .s1-1 .c.s1-2 .s1-3.s2-2', '.s1-1 .c.s1-2 .s1-3', '.s2-1 .c .s2-2'];
        // simple/attribute
        yield ['[c]', '[c]', '[c]'];
        yield ['[c][d]', '[c]', '[d]'];
        // simple/class
        yield ['.c', '.c', '.c'];
        yield ['.c.d', '.c', '.d'];
        // simple/different_types
        yield ['c#d', 'c', '#d'];
        // simple/id
        yield ['#c', '#c', '#c'];
        yield [null, '#c', '#d'];
        // simple/placeholder
        yield ['%c', '%c', '%c'];
        yield ['%c%d', '%c', '%d'];
        // simple/pseudo
        yield [':c', ':c', ':c'];
        yield [':c:d', ':c', ':d'];
        yield ['::c', '::c', '::c'];
        yield [null, '::c', '::d'];
        yield [':before', ':before', '::before'];
        yield [':after', ':after', '::after'];
        yield [':first-line', ':first-line', '::first-line'];
        yield [':first-letter', ':first-letter', '::first-letter'];
        yield [':c(@#$)', ':c(@#$)', ':c(@#$)'];
        yield [':c(@#$):c(*&^)', ':c(@#$)', ':c(*&^)'];
        yield ['::c(@#$)', '::c(@#$)', '::c(@#$)'];
        yield [null, '::c(@#$)', '::c(*&^)'];
        yield [':is(.c)', ':is(.c)', ':is(.c)'];
        yield [':is(.c):is(.d)', ':is(.c)', ':is(.d)'];
        yield [':matches(.c)', ':matches(.c)', ':matches(.c)'];
        yield [':matches(.c):matches(.d)', ':matches(.c)', ':matches(.d)'];
        yield [':host', ':host', ':host'];
        yield [':host:host(.c)', ':host', ':host(.c)'];
        yield [':host:host-context(.c)', ':host', ':host-context(.c)'];
        yield [':host-context(.c):host', ':host-context(.c)', ':host'];
        yield [':is(.c):host', ':host', ':is(.c)'];
        yield [':is(.c):host', ':is(.c)', ':host'];
        yield [null, ':host', ':hover'];
        yield [null, ':hover', ':host'];
        yield [null, ':host', '.c'];
        yield [null, '.c', ':host'];
        yield [null, ':host', '*'];
        yield [null, '*', ':host'];
        yield [':is(.c):host:is(.d)', ':host', ':is(.c):is(.d)'];
        yield [':is(.c):is(.d):host', ':is(.c):is(.d)', ':host'];
        yield [null, ':host', '.c:is(.d)'];
        yield [null, '.c:is(.d)', ':host'];
        yield [null, ':host', ':host.c'];
        yield [null, ':host.c', ':host'];
        yield [':is(.d):host(.c)', ':host(.c)', ':is(.d)'];
        yield [':is(.c):host(.d)', ':is(.c)', ':host(.d)'];
        yield [null, ':host(.c)', '.d'];
        yield [null, '.c', ':host(.d)'];
        yield [':is(.d):host-context(.c)', ':host-context(.c)', ':is(.d)'];
        yield [':is(.c):host-context(.d)', ':is(.c)', ':host-context(.d)'];
        yield [null, ':host-context(.c)', '.d'];
        yield [null, '.c', ':host-context(.d)'];
        // simple/type
        yield [null, 'c', 'd|c'];
        yield [null, 'c', '|c'];
        yield ['c', 'c', 'c'];
        yield [null, 'c', 'd'];
        yield ['c', 'c', '*|c'];
        yield [null, 'c', '*|d'];
        yield ['c|d', 'c|d', 'c|d'];
        yield [null, 'c|d', 'e|d'];
        yield [null, 'c|d', 'c|e'];
        yield [null, 'c|d', '|d'];
        yield [null, 'c|d', 'd'];
        yield ['c|d', 'c|d', '*|d'];
        yield [null, 'c|d', '*|e'];
        yield [null, '|c', 'e|c'];
        yield ['|c', '|c', '|c'];
        yield [null, '|c', '|d'];
        yield [null, '|c', 'c'];
        yield ['|c', '|c', '*|c'];
        yield [null, '|c', '*|d'];
        yield ['d|c', '*|c', 'd|c'];
        yield [null, '*|c', 'd|e'];
        yield ['|c', '*|c', '|c'];
        yield [null, '*|c', '|d'];
        yield ['c', '*|c', 'c'];
        yield [null, '*|c', 'd'];
        yield ['*|c', '*|c', '*|c'];
        yield [null, '*|c', '*|d'];
        yield [null, 'c', 'e|*'];
        yield [null, 'c', '|*'];
        yield ['c', 'c', '*'];
        yield ['c', 'c', '*|*'];
        yield ['c|d', 'c|d', 'c|*'];
        yield [null, 'c|d', 'e|*'];
        yield [null, 'c|d', '|*'];
        yield [null, 'c|d', '*'];
        yield ['c|d', 'c|d', '*|*'];
        yield [null, '|c', 'e|*'];
        yield ['|c', '|c', '|*'];
        yield [null, '|c', '*'];
        yield ['|c', '|c', '*|*'];
        yield ['d|c', '*|c', 'd|*'];
        yield ['|c', '*|c', '|*'];
        yield ['c', '*|c', '*'];
        yield ['*|c', '*|c', '*|*'];
        // simple/universal
        yield [null, '*', 'c|d'];
        yield [null, '*', '|c'];
        yield ['c', '*', 'c'];
        yield ['c', '*', '*|c'];
        yield ['c|d', 'c|*', 'c|d'];
        yield [null, 'c|*', 'd|e'];
        yield [null, 'c|*', '|d'];
        yield [null, 'c|*', 'd'];
        yield ['c|d', 'c|*', '*|d'];
        yield [null, '|*', 'c|d'];
        yield ['|c', '|*', '|c'];
        yield [null, '|*', 'c'];
        yield ['|c', '|*', '*|c'];
        yield ['c|d', '*|*', 'c|d'];
        yield ['|c', '*|*', '|c'];
        yield ['c', '*|*', 'c'];
        yield ['*|c', '*|*', '*|c'];
        yield [null, '*', 'e|*'];
        yield [null, '*', '|*'];
        yield ['*', '*', '*'];
        yield ['*', '*', '*|*'];
        yield ['c|*', 'c|*', 'c|*'];
        yield [null, 'c|*', '|*'];
        yield [null, 'c|*', '*'];
        yield ['c|*', 'c|*', '*|*'];
        yield [null, '|*', 'e|*'];
        yield ['|*', '|*', '|*'];
        yield [null, '|*', '*'];
        yield ['|*', '|*', '*|*'];
        yield ['c|*', '*|*', 'c|*'];
        yield ['|*', '*|*', '|*'];
        yield ['*', '*|*', '*'];
        yield ['*|*', '*|*', '*|*'];
        // chooses_superselector
        yield ['d c.e', 'c', 'd c.e'];
        yield ['d c.e', 'd c.e', 'c'];
        yield ['c.e d.f', 'c d', 'c.e .f'];
        yield ['c.e d.f', 'c.e .f', 'c d'];
        // compound
        yield ['.c.d.e.f', '.c.d', '.e.f'];
        yield ['.c.d.e', '.c.d', '.d.e'];
        yield ['.c.d', '.c.d', '.c.d'];
        yield ['d.c', '.c', 'd'];
        yield ['.d::c', '::c', '.d'];
        yield ['.d:c', ':c', '.d'];
        yield [':d::c', '::c', ':d'];
        yield [':c::d', ':c', '::d'];
    }
}
