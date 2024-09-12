<?php

namespace ScssPhp\ScssPhp\Tests\SourceSpan;

use League\Uri\Uri;
use ScssPhp\ScssPhp\SourceSpan\SimpleSourceLocation;
use ScssPhp\ScssPhp\SourceSpan\SimpleSourceSpanWithContext;
use PHPUnit\Framework\TestCase;

class SimpleSourceSpanWithContextTest extends TestCase
{
    public function testForConstructorSourceUrlsMustMatch(): void
    {
        $start = new SimpleSourceLocation(0, Uri::new('foo.dart'));
        $end = new SimpleSourceLocation(1, Uri::new('bar.dart'));

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpanWithContext($start, $end, '_', '_');
    }

    public function testForConstructorEndMustComeAfterStart(): void
    {
        $start = new SimpleSourceLocation(1);
        $end = new SimpleSourceLocation(0);

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpanWithContext($start, $end, '_', '_');
    }

    public function testForConstructorTextMustBeTheRightLength(): void
    {
        $start = new SimpleSourceLocation(0);
        $end = new SimpleSourceLocation(1);

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpanWithContext($start, $end, 'abc', 'abc');
    }

    public function testForConstructorContextMustContainText(): void
    {
        $start = new SimpleSourceLocation(2);
        $end = new SimpleSourceLocation(5);

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpanWithContext($start, $end, 'abc', '--axc--');
    }

    public function testForConstructorTextStartsAtStartColumnInContext(): void
    {
        $start = new SimpleSourceLocation(2);
        $end = new SimpleSourceLocation(5);

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpanWithContext($start, $end, 'abc', '-abc--');
    }

    public function testForConstructorTextStartsAtStartColumnInMultilineContext(): void
    {
        $start = new SimpleSourceLocation(4, line: 55, column: 3);
        $end = new SimpleSourceLocation(7, line: 55, column: 6);

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpanWithContext($start, $end, 'abc', "\n--abc--");
    }

    public function testForConstructorTextStartsAtStartColumnInMultilineContext2(): void
    {
        $start = new SimpleSourceLocation(4, line: 55, column: 3);
        $end = new SimpleSourceLocation(7, line: 55, column: 6);

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpanWithContext($start, $end, 'abc', "\n----abc--");
    }

    public function testForConstructorTextStartsAtStartColumnInMultilineContext3(): void
    {
        $start = new SimpleSourceLocation(4, line: 55, column: 3);
        $end = new SimpleSourceLocation(7, line: 55, column: 6);

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpanWithContext($start, $end, 'abc', "\n\n--abc--");
    }

    public function testForConstructorTextStartsAtStartColumnInMultilineContext4(): void
    {
        $start = new SimpleSourceLocation(4, line: 55, column: 3);
        $end = new SimpleSourceLocation(7, line: 55, column: 6);

        new SimpleSourceSpanWithContext($start, $end, 'abc', "\n---abc--");
        new SimpleSourceSpanWithContext($start, $end, 'abc', "\n\n---abc--");
        $this->expectNotToPerformAssertions(); // We just want to expect that we get no exception
    }

    public function testForConstructorTextCanOccurMultipleTimesInContext(): void
    {
        $start1 = new SimpleSourceLocation(4, line: 55, column: 2);
        $end1 = new SimpleSourceLocation(7, line: 55, column: 5);
        $start2 = new SimpleSourceLocation(4, line: 55, column: 8);
        $end2 = new SimpleSourceLocation(7, line: 55, column: 11);

        new SimpleSourceSpanWithContext($start1, $end1, 'abc', "--abc---abc--\n");
        new SimpleSourceSpanWithContext($start1, $end1, 'abc', "--abc--abc--\n");
        new SimpleSourceSpanWithContext($start2, $end2, 'abc', "--abc---abc--\n");
        new SimpleSourceSpanWithContext($start2, $end2, 'abc', "---abc--abc--\n");
        $this->expectNotToPerformAssertions(); // We just want to expect that we get no exception
    }

    public function testForConstructorTextCanOccurMultipleTimesInContext2(): void
    {
        $start1 = new SimpleSourceLocation(4, line: 55, column: 2);
        $end1 = new SimpleSourceLocation(7, line: 55, column: 5);

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpanWithContext($start1, $end1, 'abc', "---abc--abc--\n");
    }

    public function testForConstructorTextCanOccurMultipleTimesInContext3(): void
    {
        $start2 = new SimpleSourceLocation(4, line: 55, column: 8);
        $end2 = new SimpleSourceLocation(7, line: 55, column: 11);

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpanWithContext($start2, $end2, 'abc', "--abc--abc--\n");
    }

    public function testSubspanReturnsTheOriginalSpanWithAnImplicitEnd(): void
    {
        $start = new SimpleSourceLocation(2);
        $end = new SimpleSourceLocation(5);
        $span = new SimpleSourceSpanWithContext($start, $end, 'abc', '--abc--');

        self::assertSame($span, $span->subspan(0));
    }

    public function testSubspanReturnsTheOriginalSpanWithAnExplicitEnd(): void
    {
        $start = new SimpleSourceLocation(2);
        $end = new SimpleSourceLocation(5);
        $span = new SimpleSourceSpanWithContext($start, $end, 'abc', '--abc--');

        self::assertSame($span, $span->subspan(0, $span->getLength()));
    }

    public function testSubspanPreservesTheContext(): void
    {
        $start = new SimpleSourceLocation(2);
        $end = new SimpleSourceLocation(5);
        $span = new SimpleSourceSpanWithContext($start, $end, 'abc', '--abc--');

        self::assertEquals('--abc--', $span->subspan(1, 2)->getContext());
    }
}
