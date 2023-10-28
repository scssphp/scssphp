<?php

namespace ScssPhp\ScssPhp\Tests\Extend;

use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Ast\Selector\SelectorList;
use ScssPhp\ScssPhp\Extend\ConcreteExtensionStore;
use ScssPhp\ScssPhp\Util\SpanUtil;

class ExtendTest extends TestCase
{
    /**
     * @dataProvider provideExtendedSelectors
     */
    public function testExtendSelector(string $expected, string $selector, string $extendee, string $extender)
    {
        $selectorSelector = SelectorList::parse($selector);
        $sourceSelector = SelectorList::parse($extender);
        $targetsSelector = SelectorList::parse($extendee);

        $extended = ConcreteExtensionStore::extend($selectorSelector, $sourceSelector, $targetsSelector, SpanUtil::bogusSpan());

        $this->assertEquals($expected, (string) $extended);
    }

    /**
     * Provide selector unification tests taken from sass-spec in spec/core_functions/selector/extend
     */
    public static function provideExtendedSelectors(): iterable
    {
        yield 'simple/attribute/equal' => ['[c=d], e', '[c=d]', '[c=d]', 'e'];
        yield 'simple/attribute/unequal/name' => ['[c=d]', '[c=d]', '[e=d]', 'f'];
        yield 'simple/attribute/unequal/value' => ['[c=d]', '[c=d]', '[c=e]', 'f'];
        yield 'simple/attribute/unequal/operator' => ['[c=d]', '[c=d]', '[c^=d]', 'f'];
        yield 'simple/class/equal' => ['.c, e', '.c', '.c', 'e'];
        yield 'simple/class/unequal' => ['.c', '.c', '.d', 'e'];
        yield 'simple/id/equal' => ['#c, e', '#c', '#c', 'e'];
        yield 'simple/id/unequal' => ['#c', '#c', '#d', 'e'];
        yield 'simple/placeholder/equal' => ['%c, e', '%c', '%c', 'e'];
        yield 'simple/placeholder/unequal' => ['%c', '%c', '%d', 'e'];
        yield 'simple/type/equal' => ['c, e', 'c', 'c', 'e'];
        yield 'simple/type/unequal' => ['c', 'c', 'd', 'e'];
        yield 'simple/type/and_universal' => ['c', 'c', '*', 'e'];
        yield 'simple/type/namespace/explicit/and_explicit/equal' => ['c|d, e', 'c|d', 'c|d', 'e'];
        yield 'simple/type/namespace/explicit/and_explicit/unequal' => ['c|d', 'c|d', 'e|d', 'e'];
        yield 'simple/type/namespace/explicit/and_implicit' => ['c|d', 'c|d', 'd', 'e'];
        yield 'simple/type/namespace/explicit/and_empty' => ['c|d', 'c|d', '|d', 'e'];
        yield 'simple/type/namespace/explicit/and_universal' => ['c|d', 'c|d', '*|d', 'e'];
        yield 'simple/type/namespace/empty/and_explicit' => ['|c', '|c', 'd|c', 'e'];
        yield 'simple/type/namespace/empty/and_implicit' => ['|c', '|c', 'c', 'e'];
        yield 'simple/type/namespace/empty/and_empty' => ['|c, e', '|c', '|c', 'e'];
        yield 'simple/type/namespace/empty/and_universal' => ['|c', '|c', '*|c', 'e'];
        yield 'simple/type/namespace/universal/and_explicit' => ['*|c', '*|c', 'd|c', 'd'];
        yield 'simple/type/namespace/universal/and_implicit' => ['*|c', '*|c', 'c', 'd'];
        yield 'simple/type/namespace/universal/and_empty' => ['*|c', '*|c', '|c', 'd'];
        yield 'simple/type/namespace/universal/and_universal' => ['*|c, d', '*|c', '*|c', 'd'];
        yield 'simple/pseudo/arg/class/equal' => [':c(@#$), e', ':c(@#$)', ':c(@#$)', 'e'];
        yield 'simple/pseudo/arg/class/unequal/name' => [':c(@#$)', ':c(@#$)', ':d(@#$)', 'e'];
        yield 'simple/pseudo/arg/class/unequal/argument' => [':c(@#$)', ':c(@#$)', ':c(*&^)', 'e'];
        yield 'simple/pseudo/arg/class/unequal/has_argument' => [':c(@#$)', ':c(@#$)', ':c', 'e'];
        yield 'simple/pseudo/arg/element/equal' => ['::c(@#$), e', '::c(@#$)', '::c(@#$)', 'e'];
        yield 'simple/pseudo/arg/element/unequal/name' => ['::c(@#$)', '::c(@#$)', '::d(@#$)', 'e'];
        yield 'simple/pseudo/arg/element/unequal/argument' => ['::c(@#$)', '::c(@#$)', '::c(*&^)', 'e'];
        yield 'simple/pseudo/arg/element/unequal/has_argument' => ['::c(@#$)', '::c(@#$)', '::c', 'e'];
        yield 'simple/pseudo/no_arg/class/equal' => [':c, e', ':c', ':c', 'e'];
        yield 'simple/pseudo/no_arg/class/unequal' => [':c', ':c', ':d', 'e'];
        yield 'simple/pseudo/no_arg/class/and_element' => [':c', ':c', '::c', 'e'];
        yield 'simple/pseudo/no_arg/element/equal' => ['::c, e', '::c', '::c', 'e'];
        yield 'simple/pseudo/no_arg/element/unequal' => ['::c', '::c', '::d', 'e'];
        yield 'simple/pseudo/no_arg/element/and_class' => ['::c', '::c', ':c', 'e'];
        yield 'simple/pseudo/selector/idempotent/not/simple' => [':not(.c):not(.d)', ':not(.c)', '.c', '.d'];
        yield 'simple/pseudo/selector/idempotent/not/list' => [':not(.c):not(.d):not(.e)', ':not(.c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/idempotent/not/complex' => [':not(.c .d):not(.c .e .f):not(.e .c .f)', ':not(.c .d)', '.d', '.e .f'];
        yield 'simple/pseudo/selector/idempotent/not/component' => [':not(.c.d):not(.d.e)', ':not(.c.d)', '.c', '.e'];
        yield 'simple/pseudo/selector/idempotent/not/list_in_not' => [':not(.c, .e, .d)', ':not(.c, .d)', '.c', '.e'];
        yield 'simple/pseudo/selector/idempotent/not/is/list' => [':not(.c):not(.d):not(.e)', ':not(.c)', '.c', ':is(.d, .e)'];
        yield 'simple/pseudo/selector/idempotent/not/is/list_of_complex' => [':not(.c):not(.d .e):not(.f .g)', ':not(.c)', '.c', ':is(.d .e, .f .g)'];
        yield 'simple/pseudo/selector/idempotent/not/is/in_compound' => [':not(.c):not(.d:is(.e, .f))', ':not(.c)', '.c', '.d:is(.e, .f)'];
        yield 'simple/pseudo/selector/idempotent/not/matches/list' => [':not(.c):not(.d):not(.e)', ':not(.c)', '.c', ':matches(.d, .e)'];
        yield 'simple/pseudo/selector/idempotent/not/matches/list_of_complex' => [':not(.c):not(.d .e):not(.f .g)', ':not(.c)', '.c', ':matches(.d .e, .f .g)'];
        yield 'simple/pseudo/selector/idempotent/not/matches/in_compound' => [':not(.c):not(.d:matches(.e, .f))', ':not(.c)', '.c', '.d:matches(.e, .f)'];
        yield 'simple/pseudo/selector/idempotent/not/where/list' => [':not(.c):not(.d):not(.e)', ':not(.c)', '.c', ':where(.d, .e)'];
        yield 'simple/pseudo/selector/idempotent/not/where/list_of_complex' => [':not(.c):not(.d .e):not(.f .g)', ':not(.c)', '.c', ':where(.d .e, .f .g)'];
        yield 'simple/pseudo/selector/idempotent/not/where/in_compound' => [':not(.c):not(.d:where(.e, .f))', ':not(.c)', '.c', '.d:where(.e, .f)'];
        yield 'simple/pseudo/selector/idempotent/not/not_in_extender' => [':not(.c)', ':not(.c)', '.c', ':not(.d)'];
        yield 'simple/pseudo/selector/idempotent/is/simple' => [':is(.c, .d)', ':is(.c)', '.c', '.d'];
        yield 'simple/pseudo/selector/idempotent/is/list' => [':is(.c, .d, .e)', ':is(.c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/idempotent/is/is_in_extender' => [':is(.c, .d, .e)', ':is(.c)', '.c', ':is(.d, .e)'];
        yield 'simple/pseudo/selector/idempotent/where/simple' => [':where(.c, .d)', ':where(.c)', '.c', '.d'];
        yield 'simple/pseudo/selector/idempotent/where/list' => [':where(.c, .d, .e)', ':where(.c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/idempotent/where/is_in_extender' => [':where(.c, .d, .e)', ':where(.c)', '.c', ':where(.d, .e)'];
        yield 'simple/pseudo/selector/idempotent/matches/simple' => [':matches(.c, .d)', ':matches(.c)', '.c', '.d'];
        yield 'simple/pseudo/selector/idempotent/matches/list' => [':matches(.c, .d, .e)', ':matches(.c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/idempotent/matches/matches_in_extender' => [':matches(.c, .d, .e)', ':matches(.c)', '.c', ':matches(.d, .e)'];
        yield 'simple/pseudo/selector/idempotent/any/simple' => [':any(.c, .d)', ':any(.c)', '.c', '.d'];
        yield 'simple/pseudo/selector/idempotent/any/list' => [':any(.c, .d, .e)', ':any(.c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/idempotent/any/any_in_extender' => [':any(.c, .d, .e)', ':any(.c)', '.c', ':any(.d, .e)'];
        yield 'simple/pseudo/selector/idempotent/current/simple' => [':current(.c, .d)', ':current(.c)', '.c', '.d'];
        yield 'simple/pseudo/selector/idempotent/current/list' => [':current(.c, .d, .e)', ':current(.c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/idempotent/current/current_in_extender' => [':current(.c, .d, .e)', ':current(.c)', '.c', ':current(.d, .e)'];
        yield 'simple/pseudo/selector/idempotent/nth_child/simple' => [':nth-child(2n+1 of .c, .d)', ':nth-child(2n + 1 of .c)', '.c', '.d'];
        yield 'simple/pseudo/selector/idempotent/nth_child/list' => [':nth-child(2n+1 of .c, .d, .e)', ':nth-child(2n + 1 of .c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/idempotent/nth_child/same_arg_in_extender' => [':nth-child(2n+1 of .c, .d, .e)', ':nth-child(2n + 1 of .c)', '.c', ':nth-child(2n + 1 of .d, .e)'];
        yield 'simple/pseudo/selector/idempotent/nth_child/different_arg_in_extender' => [':nth-child(2n+1 of .c)', ':nth-child(2n + 1 of .c)', '.c', ':nth-child(2n + 2 of .d, .e)'];
        yield 'simple/pseudo/selector/idempotent/nth_last_child/simple' => [':nth-last-child(2n+1 of .c, .d)', ':nth-last-child(2n + 1 of .c)', '.c', '.d'];
        yield 'simple/pseudo/selector/idempotent/nth_last_child/list' => [':nth-last-child(2n+1 of .c, .d, .e)', ':nth-last-child(2n + 1 of .c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/idempotent/nth_last_child/same_arg_in_extender' => [':nth-last-child(2n+1 of .c, .d, .e)', ':nth-last-child(2n + 1 of .c)', '.c', ':nth-last-child(2n + 1 of .d, .e)'];
        yield 'simple/pseudo/selector/idempotent/nth_last_child/different_arg_in_extender' => [':nth-last-child(2n+1 of .c)', ':nth-last-child(2n + 1 of .c)', '.c', ':nth-last-child(2n + 2 of .d, .e)'];
        yield 'simple/pseudo/selector/idempotent/prefixed/simple' => [':-ms-matches(.c, .d)', ':-ms-matches(.c)', '.c', '.d'];
        yield 'simple/pseudo/selector/idempotent/prefixed/list' => [':-ms-matches(.c, .d, .e)', ':-ms-matches(.c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/idempotent/prefixed/same_prefix_in_extender' => [':-ms-matches(.c, .d, .e)', ':-ms-matches(.c)', '.c', ':-ms-matches(.d, .e)'];
        yield 'simple/pseudo/selector/idempotent/prefixed/different_prefix_in_extender' => [':-ms-matches(.c)', ':-ms-matches(.c)', '.c', ':-moz-matches(.d, .e)'];
        yield 'simple/pseudo/selector/match/unprefixed/is/class/equal' => [':is(c d.e, f g), h', ':is(c d.e, f g)', ':is(c d.e, f g)', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/is/class/unequal/name' => [':is(c d.e, f g)', ':is(c d.e, f g)', ':-pfx-is(c d.e, f g)', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/is/class/unequal/argument' => [':is(c d.e, f g)', ':is(c d.e, f g)', ':is(d, g)', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/is/class/unequal/has_argument' => [':is(c d.e, f g)', ':is(c d.e, f g)', ':is', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/where/class/equal' => [':where(c d.e, f g), h', ':where(c d.e, f g)', ':where(c d.e, f g)', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/where/class/unequal/name' => [':where(c d.e, f g)', ':where(c d.e, f g)', ':-pfx-where(c d.e, f g)', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/where/class/unequal/argument' => [':where(c d.e, f g)', ':where(c d.e, f g)', ':where(d, g)', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/where/class/unequal/has_argument' => [':where(c d.e, f g)', ':where(c d.e, f g)', ':where', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/matches/class/equal' => [':matches(c d.e, f g), h', ':matches(c d.e, f g)', ':matches(c d.e, f g)', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/matches/class/unequal/name' => [':matches(c d.e, f g)', ':matches(c d.e, f g)', ':-pfx-matches(c d.e, f g)', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/matches/class/unequal/argument' => [':matches(c d.e, f g)', ':matches(c d.e, f g)', ':matches(d, g)', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/matches/class/unequal/has_argument' => [':matches(c d.e, f g)', ':matches(c d.e, f g)', ':matches', 'h'];
        yield 'simple/pseudo/selector/match/unprefixed/element/equal' => ['::slotted(c.d, e.f), g', '::slotted(c.d, e.f)', '::slotted(c.d, e.f)', 'g'];
        yield 'simple/pseudo/selector/match/unprefixed/element/unequal/name' => ['::slotted(c.d, e.f)', '::slotted(c.d, e.f)', '::-pfx-slotted(c.d, e.f)', 'g'];
        yield 'simple/pseudo/selector/match/unprefixed/element/unequal/argument' => ['::slotted(c.d, e.f)', '::slotted(c.d, e.f)', '::slotted(d, g)', 'g'];
        yield 'simple/pseudo/selector/match/unprefixed/element/unequal/has_argument' => ['::slotted(c.d, e.f)', '::slotted(c.d, e.f)', '::slotted', 'g'];
        yield 'simple/pseudo/selector/match/prefixed/equal' => [':nth-child(2n+1 of c d.e, f g), h', ':nth-child(2n + 1 of c d.e, f g)', ':nth-child(2n + 1 of c d.e, f g)', 'h'];
        yield 'simple/pseudo/selector/match/prefixed/unequal/name' => [':nth-child(2n+1 of c d.e, f g)', ':nth-child(2n + 1 of c d.e, f g)', ':nth-last-child(2n + 1 of c d.e, f g)', 'h'];
        yield 'simple/pseudo/selector/match/prefixed/unequal/argument' => [':nth-child(2n+1 of c d.e, f g)', ':nth-child(2n + 1 of c d.e, f g)', ':nth-child(2n + 1 of d, g)', 'h'];
        yield 'simple/pseudo/selector/match/prefixed/unequal/prefix' => [':nth-child(2n+1 of c d.e, f g)', ':nth-child(2n + 1 of c d.e, f g)', ':nth-child(2n of c d.e, f g)', 'h'];
        yield 'simple/pseudo/selector/match/prefixed/unequal/has_argument' => [':nth-child(2n+1 of c d.e, f g)', ':nth-child(2n + 1 of c d.e, f g)', ':nth-child', 'h'];
        yield 'simple/pseudo/selector/non_idempotent/has/simple' => [':has(.c, .d)', ':has(.c)', '.c', '.d'];
        yield 'simple/pseudo/selector/non_idempotent/has/list' => [':has(.c, .d, .e)', ':has(.c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/non_idempotent/has/has_in_extender' => [':has(.c, :has(.d))', ':has(.c)', '.c', ':has(.d)'];
        yield 'simple/pseudo/selector/non_idempotent/host/simple' => [':host(.c, .d)', ':host(.c)', '.c', '.d'];
        yield 'simple/pseudo/selector/non_idempotent/host/list' => [':host(.c, .d, .e)', ':host(.c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/non_idempotent/host/host_in_extender' => [':host(.c, :host(.d))', ':host(.c)', '.c', ':host(.d)'];
        yield 'simple/pseudo/selector/non_idempotent/host_context/simple' => [':host-context(.c, .d)', ':host-context(.c)', '.c', '.d'];
        yield 'simple/pseudo/selector/non_idempotent/host_context/list' => [':host-context(.c, .d, .e)', ':host-context(.c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/non_idempotent/host_context/host_context_in_extender' => [':host-context(.c, :host-context(.d))', ':host-context(.c)', '.c', ':host-context(.d)'];
        yield 'simple/pseudo/selector/non_idempotent/slotted/simple' => ['::slotted(.c, .d)', '::slotted(.c)', '.c', '.d'];
        yield 'simple/pseudo/selector/non_idempotent/slotted/list' => ['::slotted(.c, .d, .e)', '::slotted(.c)', '.c', '.d, .e'];
        yield 'simple/pseudo/selector/non_idempotent/slotted/slotted_in_extender' => ['::slotted(.c, ::slotted(.d))', '::slotted(.c)', '.c', '::slotted(.d)'];
        yield 'complex/combinator_only/selector' => ['+', '+', '.c', '.d'];
        yield 'complex/combinator_only/extender' => ['.c, >', '.c', '.c', '>'];
        yield 'complex/with_unification/parent/without_grandparent/simple' => ['.c.x .d, .x.e .d', '.c.x .d', '.c', '.e'];
        yield 'complex/with_unification/parent/without_grandparent/complex' => ['.c.x .d, .e .x.f .d', '.c.x .d', '.c', '.e .f'];
        yield 'complex/with_unification/parent/without_grandparent/list' => ['.c.x .d, .x.e .d, .x.f .d', '.c.x .d', '.c', '.e, .f'];
        yield 'complex/with_unification/parent/with_grandparent/simple' => ['.c .d.x .e, .c .x.f .e', '.c .d.x .e', '.d', '.f'];
        yield 'complex/with_unification/parent/with_grandparent/complex' => ['.c .d.x .e, .c .f .x.g .e, .f .c .x.g .e', '.c .d.x .e', '.d', '.f .g'];
        yield 'complex/with_unification/parent/with_grandparent/list' => ['.c .d.x .e, .c .x.f .e, .c .x.g .e', '.c .d.x .e', '.d', '.f, .g'];
        yield 'complex/with_unification/leading_combinator/selector' => ['> .c.x, > .x.d', '> .c.x', '.c', '.d'];
        yield 'complex/with_unification/leading_combinator/extender' => ['.c.x, + .x.d', '.c.x', '.c', '+ .d'];
        yield 'complex/with_unification/leading_combinator/both' => ['~ .c.x', '~ .c.x', '.c', '+ .d'];
        yield 'complex/with_unification/trailing_combinator/selector' => ['.c.x +, .x.d +', '.c.x +', '.c', '.d'];
        yield 'complex/with_unification/trailing_combinator/extender/child' => ['.c.x .d, .x.e > .d', '.c.x .d', '.c', '.e >'];
        yield 'complex/with_unification/trailing_combinator/extender/sibling' => ['.c.x .d, .x.e ~ .d', '.c.x .d', '.c', '.e ~'];
        yield 'complex/with_unification/trailing_combinator/extender/next_sibling' => ['.c.x .d, .x.e + .d', '.c.x .d', '.c', '.e +'];
        yield 'complex/with_unification/trailing_combinator/both' => ['.c.x ~', '.c.x ~', '.c', '.d +'];
        yield 'complex/with_unification/multiple_combinators/middle/selector' => ['.c.x ~ ~ .d', '.c.x ~ ~ .d', '.c', '.e'];
        yield 'complex/with_unification/multiple_combinators/middle/extender' => ['.c.x', '.c.x', '.c', '.d ~ + .e'];
        yield 'complex/with_unification/multiple_combinators/leading/selector' => ['> + .c.x', '> + .c.x', '.c', '.d'];
        yield 'complex/with_unification/multiple_combinators/leading/extender' => ['.c.x', '.c.x', '.c', '+ ~ .d'];
        yield 'complex/with_unification/multiple_combinators/trailing/selector' => ['.c.x > ~', '.c.x > ~', '.c', '.d'];
        yield 'complex/with_unification/multiple_combinators/trailing/extender' => ['.c.x', '.c.x', '.c', '.d + +'];
        yield 'list/one_matches' => ['.c, .e', '.c', '.c, .d', '.e'];
        yield 'list/all_match' => ['.c.d, .e', '.c.d', '.c, .d', '.e'];
        yield 'list/different_matches' => ['.c.d, .g, .c .e, .g .e, .d .f, .g .f', '.c.d, .c .e, .d .f', '.c, .d', '.g'];
        yield 'no_op/missing' => ['c', 'c', 'd', 'e'];
        yield 'no_op/conflict/element/alone' => ['c.d', 'c.d', '.d', 'e'];
        yield 'no_op/conflict/element/with_class' => ['c.d', 'c.d', '.d', 'e.f'];
        yield 'no_op/conflict/id' => ['#c.d', '#c.d', '.d', '#e'];
        yield 'no_op/conflict/pseudo_element/unknown' => ['::c.d', '::c.d', '.d', '::e'];
        yield 'no_op/conflict/pseudo_element/class_syntax' => [':before.c', ':before.c', '.c', ':after'];
        yield 'no_op/conflict/universal/namespace_and_namespace' => ['c|*.d', 'c|*.d', '.d', 'e|*'];
        yield 'no_op/conflict/universal/namespace_and_empty' => ['c|*.d', 'c|*.d', '.d', '|*'];
        yield 'no_op/conflict/universal/empty_and_namespace' => ['|*.c', '|*.c', '.c', 'd|*'];
        yield 'no_op/conflict/universal/namespace_and_default' => ['c|*.d', 'c|*.d', '.d', '*'];
        yield 'no_op/conflict/universal/default_and_namespace' => ['*.c', '*.c', '.c', 'd|*'];
        yield 'no_op/conflict/universal/empty_and_default' => ['|*.c', '|*.c', '.c', '*'];
        yield 'no_op/conflict/universal/default_and_empty' => ['*.c', '*.c', '.c', '|*'];
        yield 'no_op/conflict/parent' => ['c > .d', 'c > .d', '.d', 'e > .f'];
        yield 'no_op/conflict/next_sibling' => ['c + .d', 'c + .d', '.d', 'e + .f'];
        yield 'no_op/unification/identical_to_extendee' => ['c.d', 'c.d', '.d', '.d'];
        yield 'no_op/unification/identical_to_selector' => ['c.d', 'c.d', '.d', 'c.d'];
        yield 'no_op/unification/additional/simple' => ['c', 'c', 'c', 'c.d'];
        yield 'no_op/unification/additional/ancestor' => ['c', 'c', 'c', 'd c'];
        yield 'no_op/unification/additional/parent' => ['c', 'c', 'c', 'd > c'];
        yield 'no_op/unification/additional/sibling' => ['c', 'c', 'c', 'd ~ c'];
        yield 'no_op/unification/additional/next_sibling' => ['c', 'c', 'c', 'd + c'];
        yield 'no_op/unification/subselector_of_target/is' => ['.c:is(d)', '.c:is(d)', ':is(d)', 'd.e'];
        yield 'no_op/unification/subselector_of_target/where' => ['.c:where(d)', '.c:where(d)', ':where(d)', 'd.e'];
        yield 'no_op/unification/subselector_of_target/matches' => ['.c:matches(d)', '.c:matches(d)', ':matches(d)', 'd.e'];
        yield 'no_op/unification/specificity_modification/where' => [':where(.x, .x .y)', ':where(.x)', '.x', '.x .y'];
    }
}
