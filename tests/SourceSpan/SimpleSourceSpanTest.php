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

namespace ScssPhp\ScssPhp\Tests\SourceSpan;

use League\Uri\Uri;
use ScssPhp\ScssPhp\SourceSpan\SimpleSourceLocation;
use ScssPhp\ScssPhp\SourceSpan\SimpleSourceSpan;
use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\SourceSpan\SourceSpan;

class SimpleSourceSpanTest extends TestCase
{
    private SourceSpan $span;

    protected function setUp(): void
    {
        $url = Uri::new('foo.dart');

        $this->span = new SimpleSourceSpan(new SimpleSourceLocation(5, $url), new SimpleSourceLocation(12, $url), 'foo bar');
    }

    public function testForConstructorSourceUrlsMustMatch(): void
    {
        $start = new SimpleSourceLocation(0, Uri::new('foo.dart'));
        $end = new SimpleSourceLocation(1, Uri::new('bar.dart'));

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpan($start, $end, '_');
    }

    public function testForConstructorEndMustComeAfterStart(): void
    {
        $start = new SimpleSourceLocation(1);
        $end = new SimpleSourceLocation(0);

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpan($start, $end, '_');
    }

    public function testForConstructorTextMustBeTheRightLength(): void
    {
        $start = new SimpleSourceLocation(0);
        $end = new SimpleSourceLocation(1);

        $this->expectException(\InvalidArgumentException::class);
        new SimpleSourceSpan($start, $end, 'abc');
    }

    public function testForUnionSourceUrlsMustMatch(): void
    {
        $url = Uri::new('bar.dart');

        $other = new SimpleSourceSpan(new SimpleSourceLocation(12, $url), new SimpleSourceLocation(13, $url), '_');

        $this->expectException(\InvalidArgumentException::class);
        $this->span->union($other);
    }

    public function testForUnionSpansMayNotBeDisjoint(): void
    {
        $other = new SimpleSourceSpan(new SimpleSourceLocation(13, $this->span->getSourceUrl()), new SimpleSourceLocation(14, $this->span->getSourceUrl()), '_');

        $this->expectException(\InvalidArgumentException::class);
        $this->span->union($other);
    }

    public function testForCompareToSourceUrlsMustMatch(): void
    {
        $url = Uri::new('bar.dart');

        $other = new SimpleSourceSpan(new SimpleSourceLocation(12, $url), new SimpleSourceLocation(13, $url), '_');

        $this->expectException(\InvalidArgumentException::class);
        $this->span->compareTo($other);
    }

    public function testFieldsWorkCorrectly(): void
    {
        self::assertEquals(7, $this->span->getLength());
    }

    public function testUnionWorksWithAPrecedingAdjacentSpan(): void
    {
        $other = new SimpleSourceSpan(
            new SimpleSourceLocation(0, $this->span->getSourceUrl()),
            new SimpleSourceLocation(5, $this->span->getSourceUrl()),
            'hey, '
        );

        $result = $this->span->union($other);
        self::assertEquals($other->getStart(), $result->getStart());
        self::assertEquals($this->span->getEnd(), $result->getEnd());
        self::assertEquals('hey, foo bar', $result->getText());
    }

    public function testUnionWorksWithAPrecedingOverlappingSpan(): void
    {
        $other = new SimpleSourceSpan(
            new SimpleSourceLocation(0, $this->span->getSourceUrl()),
            new SimpleSourceLocation(8, $this->span->getSourceUrl()),
            'hey, foo'
        );

        $result = $this->span->union($other);
        self::assertEquals($other->getStart(), $result->getStart());
        self::assertEquals($this->span->getEnd(), $result->getEnd());
        self::assertEquals('hey, foo bar', $result->getText());
    }

    public function testUnionWorksWithAFollowingAdjacentSpan(): void
    {
        $other = new SimpleSourceSpan(
            new SimpleSourceLocation(12, $this->span->getSourceUrl()),
            new SimpleSourceLocation(16, $this->span->getSourceUrl()),
            ' baz'
        );

        $result = $this->span->union($other);
        self::assertEquals($this->span->getStart(), $result->getStart());
        self::assertEquals($other->getEnd(), $result->getEnd());
        self::assertEquals('foo bar baz', $result->getText());
    }

    public function testUnionWorksWithAFollowingOverlappingSpan(): void
    {
        $other = new SimpleSourceSpan(
            new SimpleSourceLocation(9, $this->span->getSourceUrl()),
            new SimpleSourceLocation(16, $this->span->getSourceUrl()),
            'bar baz'
        );

        $result = $this->span->union($other);
        self::assertEquals($this->span->getStart(), $result->getStart());
        self::assertEquals($other->getEnd(), $result->getEnd());
        self::assertEquals('foo bar baz', $result->getText());
    }

    public function testUnionWorksWithAnInternalOverlappingSpan(): void
    {
        $other = new SimpleSourceSpan(
            new SimpleSourceLocation(7, $this->span->getSourceUrl()),
            new SimpleSourceLocation(10, $this->span->getSourceUrl()),
            'o b'
        );

        self::assertEquals($this->span, $this->span->union($other));
    }

    public function testUnionWorksWithAnExternalOverlappingSpan(): void
    {
        $other = new SimpleSourceSpan(
            new SimpleSourceLocation(0, $this->span->getSourceUrl()),
            new SimpleSourceLocation(16, $this->span->getSourceUrl()),
            'hey, foo bar baz'
        );

        self::assertEquals($other, $this->span->union($other));
    }

    public function testSubspanStartMustBeGreaterThanZero(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->span->subspan(-1);
    }

    public function testSubspanStartMustBeLessThanOrEqualToLength(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->span->subspan($this->span->getLength() + 1);
    }

    public function testSubspanEndMustBeGreaterThanStarth(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->span->subspan(2, 1);
    }

    public function testSubspanEndMustBeLessThanOrEqualToLength(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->span->subspan(0, $this->span->getLength() + 1);
    }

    public function testSubspanPreservesTheSourceUrl(): void
    {
        $result = $this->span->subspan(1, 2);

        self::assertEquals($this->span->getSourceUrl(), $result->getStart()->getSourceUrl());
        self::assertEquals($this->span->getSourceUrl(), $result->getEnd()->getSourceUrl());
    }

    public function testSubspanReturnsTheOriginalSpanWithAnImplicitEnd(): void
    {
        self::assertSame($this->span, $this->span->subspan(0));
    }

    public function testSubspanReturnsTheOriginalSpanWithAnExplicitEnd(): void
    {
        self::assertSame($this->span, $this->span->subspan(0, $this->span->getLength()));
    }

    public function testSubspanWithinASingleLineReturnsAStrictSubstringOfTheOriginalSpan(): void
    {
        $result = $this->span->subspan(1, 5);

        self::assertEquals('oo b', $result->getText());
        self::assertEquals(6, $result->getStart()->getOffset());
        self::assertEquals(0, $result->getStart()->getLine());
        self::assertEquals(6, $result->getStart()->getColumn());
        self::assertEquals(10, $result->getEnd()->getOffset());
        self::assertEquals(0, $result->getEnd()->getLine());
        self::assertEquals(10, $result->getEnd()->getColumn());
    }

    public function testSubspanWithinASingleLineAnImplicitEndGoesToTheEndOfTheOriginalSpan(): void
    {
        $result = $this->span->subspan(1);

        self::assertEquals('oo bar', $result->getText());
        self::assertEquals(6, $result->getStart()->getOffset());
        self::assertEquals(0, $result->getStart()->getLine());
        self::assertEquals(6, $result->getStart()->getColumn());
        self::assertEquals(12, $result->getEnd()->getOffset());
        self::assertEquals(0, $result->getEnd()->getLine());
        self::assertEquals(12, $result->getEnd()->getColumn());
    }

    public function testSubspanWithinASingleLineCanReturnAnEmptySpan(): void
    {
        $result = $this->span->subspan(3, 3);

        self::assertEmpty($result->getText());
        self::assertEquals(8, $result->getStart()->getOffset());
        self::assertEquals(0, $result->getStart()->getLine());
        self::assertEquals(8, $result->getStart()->getColumn());
        self::assertEquals($result->getStart(), $result->getEnd());
    }

    public function testSubspanAcrossMultipleLinesWithStartAndEndInTheMiddleOfALine(): void
    {
        $span = new SimpleSourceSpan(
            new SimpleSourceLocation(5, line: 2, column: 0),
            new SimpleSourceLocation(16, line: 4, column: 3),
            "foo\nbar\nbaz"
        );

        $result = $span->subspan(2, 5);

        self::assertEquals("o\nb", $result->getText());
        self::assertEquals(7, $result->getStart()->getOffset());
        self::assertEquals(2, $result->getStart()->getLine());
        self::assertEquals(2, $result->getStart()->getColumn());
        self::assertEquals(10, $result->getEnd()->getOffset());
        self::assertEquals(3, $result->getEnd()->getLine());
        self::assertEquals(1, $result->getEnd()->getColumn());
    }

    public function testSubspanAcrossMultipleLinesWithStartAtTheEndOfALine(): void
    {
        $span = new SimpleSourceSpan(
            new SimpleSourceLocation(5, line: 2, column: 0),
            new SimpleSourceLocation(16, line: 4, column: 3),
            "foo\nbar\nbaz"
        );

        $result = $span->subspan(3, 5);

        self::assertEquals("\nb", $result->getText());
        self::assertEquals(8, $result->getStart()->getOffset());
        self::assertEquals(2, $result->getStart()->getLine());
        self::assertEquals(3, $result->getStart()->getColumn());
    }

    public function testSubspanAcrossMultipleLinesWithStartAtTheBeginningOfALine(): void
    {
        $span = new SimpleSourceSpan(
            new SimpleSourceLocation(5, line: 2, column: 0),
            new SimpleSourceLocation(16, line: 4, column: 3),
            "foo\nbar\nbaz"
        );

        $result = $span->subspan(4, 5);

        self::assertEquals('b', $result->getText());
        self::assertEquals(9, $result->getStart()->getOffset());
        self::assertEquals(3, $result->getStart()->getLine());
        self::assertEquals(0, $result->getStart()->getColumn());
    }

    public function testSubspanAcrossMultipleLinesWithEndAtTheEndOfALine(): void
    {
        $span = new SimpleSourceSpan(
            new SimpleSourceLocation(5, line: 2, column: 0),
            new SimpleSourceLocation(16, line: 4, column: 3),
            "foo\nbar\nbaz"
        );

        $result = $span->subspan(2, 3);

        self::assertEquals('o', $result->getText());
        self::assertEquals(8, $result->getEnd()->getOffset());
        self::assertEquals(2, $result->getEnd()->getLine());
        self::assertEquals(3, $result->getEnd()->getColumn());
    }

    public function testSubspanAcrossMultipleLinesWithEndAtTheBeginningOfALine(): void
    {
        $span = new SimpleSourceSpan(
            new SimpleSourceLocation(5, line: 2, column: 0),
            new SimpleSourceLocation(16, line: 4, column: 3),
            "foo\nbar\nbaz"
        );

        $result = $span->subspan(2, 4);

        self::assertEquals("o\n", $result->getText());
        self::assertEquals(9, $result->getEnd()->getOffset());
        self::assertEquals(3, $result->getEnd()->getLine());
        self::assertEquals(0, $result->getEnd()->getColumn());
    }

    public function testMessagePrintsTheTextBeingDisplayed(): void
    {
        self::markTestIncomplete('Highlighting is not implemented yet.');

        self::assertEquals(
            <<<'TXT'
line 1, column 6 of foo.dart: oh no
  ,
1 | foo bar
  | ^^^^^^^
  '
TXT,
            $this->span->message('oh no')
        );
    }

    public function testMessageGracefullyHandlesAMissingSourceUrl(): void
    {
        $span = new SimpleSourceSpan(new SimpleSourceLocation(5), new SimpleSourceLocation(12), 'foo bar');
        self::markTestIncomplete('Highlighting is not implemented yet.');

        self::assertEquals(
            <<<'TXT'
line 1, column 6: oh no
  ,
1 | foo bar
  | ^^^^^^^
  '
TXT,
            $span->message('oh no')
        );
    }

    public function testMessageGracefullyHandlesEmptyText(): void
    {
        $span = new SimpleSourceSpan(new SimpleSourceLocation(5), new SimpleSourceLocation(5), '');

        self::assertEquals('line 1, column 6: oh no', $span->message('oh no'));
    }

    public function testCompareToSortsByStartLocationFirst(): void
    {
        $other = new SimpleSourceSpan(new SimpleSourceLocation(6, $this->span->getSourceUrl()), new SimpleSourceLocation(14, $this->span->getSourceUrl()), 'oo bar b');

        self::assertLessThan(0, $this->span->compareTo($other));
        self::assertGreaterThan(0, $other->compareTo($this->span));
    }

    public function testCompareToSortsByLengthSecond(): void
    {
        $other = new SimpleSourceSpan(new SimpleSourceLocation(5, $this->span->getSourceUrl()), new SimpleSourceLocation(14, $this->span->getSourceUrl()), 'foo bar b');

        self::assertLessThan(0, $this->span->compareTo($other));
        self::assertGreaterThan(0, $other->compareTo($this->span));
    }

    public function testCompareToConsidersEqualSpansEqual(): void
    {
        self::assertEquals(0, $this->span->compareTo($this->span));
    }
}
