<?php

namespace ScssPhp\ScssPhp\Tests\SourceSpan;

use League\Uri\Uri;
use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\SourceSpan\SimpleSourceLocation;
use ScssPhp\ScssPhp\SourceSpan\SimpleSourceSpan;
use ScssPhp\ScssPhp\SourceSpan\SourceFile;

class ConcreteFileSpanTest extends TestCase
{
    private SourceFile $file;

    protected function setUp(): void
    {
        $this->file = SourceFile::fromString(
            <<<'TXT'
foo bar baz
whiz bang boom
zip zap zop
TXT,
            Uri::new('foo.dart')
        );
    }

    public function testTextReturnsASubstringOfTheSource(): void
    {
        self::assertEquals("baz\nwhi", $this->file->span(8, 15)->getText());
    }

    public function testTextIncludesTheLastCharWhenEndIsDefaultedToEndOfFile(): void
    {
        self::assertEquals('p zap zop', $this->file->span(29)->getText());
    }

    public function testContextContainsTheSpansText(): void
    {
        $span = $this->file->span(8, 15);

        self::assertStringContainsString($span->getText(), $span->getContext());
        self::assertEquals("foo bar baz\nwhiz bang boom\n", $span->getContext());
    }

    public function testContextContainsThePreviousLineForAPointSpanAtTheEndOfALine(): void
    {
        $span = $this->file->span(25, 25);

        self::assertEquals("whiz bang boom\n", $span->getContext());
    }

    public function testContextContainsTheNextLineForAPointSpanAtTheBeginningOfALine(): void
    {
        $span = $this->file->span(12, 12);

        self::assertEquals("whiz bang boom\n", $span->getContext());
    }

    public function testContextContainsTheLastLineForAPointSpanAtTheEndOfTheFileWithoutANewLine(): void
    {
        $span = $this->file->span($this->file->getLength(), $this->file->getLength());

        self::assertEquals('zip zap zop', $span->getContext());
    }

    public function testContextContainsAnEmptyLineForAPointSpanAtTheEndOfTheFileWithANewLine(): void
    {
        $file = SourceFile::fromString(<<<'TXT'
foo bar baz
whiz bang boom
zip zap zop

TXT
        );
        $span = $file->span($file->getLength(), $file->getLength());

        self::assertEquals('', $span->getContext());
    }

    public function testUnionWorksWithAPrecedingAdjacentSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(0, 5);

        $result = $span->union($other);
        self::assertEquals($other->getStart(), $result->getStart());
        self::assertEquals($span->getEnd(), $result->getEnd());
        self::assertEquals("foo bar baz\n", $result->getText());
    }

    public function testUnionWorksWithAPrecedingOverlappingSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(0, 8);

        $result = $span->union($other);
        self::assertEquals($other->getStart(), $result->getStart());
        self::assertEquals($span->getEnd(), $result->getEnd());
        self::assertEquals("foo bar baz\n", $result->getText());
    }

    public function testUnionWorksWithAFollowingAdjacentSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(12, 16);

        $result = $span->union($other);
        self::assertEquals($span->getStart(), $result->getStart());
        self::assertEquals($other->getEnd(), $result->getEnd());
        self::assertEquals("ar baz\nwhiz", $result->getText());
    }

    public function testUnionWorksWithAFollowingOverlappingSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(9, 16);

        $result = $span->union($other);
        self::assertEquals($span->getStart(), $result->getStart());
        self::assertEquals($other->getEnd(), $result->getEnd());
        self::assertEquals("ar baz\nwhiz", $result->getText());
    }

    public function testUnionWorksWithAnInternalOverlappingSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(7, 10);

        self::assertEquals($span, $span->union($other));
    }

    public function testUnionWorksWithAnExternalOverlappingSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(0, 16);

        self::assertEquals($other, $span->union($other));
    }

    public function testUnionReturnsAFileSpanForAFileSpanInput(): void
    {
        $span = $this->file->span(5, 12);

        self::assertInstanceOf(FileSpan::class, $span->union($this->file->span(0, 5)));
    }

    public function testUnionReturnsABaseSourceSpanForASourceSpanInput(): void
    {
        $span = $this->file->span(5, 12);

        $other = new SimpleSourceSpan(
            new SimpleSourceLocation(0, $span->getSourceUrl()),
            new SimpleSourceLocation(5, $span->getSourceUrl()),
            'hey, '
        );

        $result = $span->union($other);

        self::assertNotInstanceOf(FileSpan::class, $result);
        self::assertEquals($other->getStart(), $result->getStart());
        self::assertEquals($span->getEnd(), $result->getEnd());
        self::assertEquals("hey, ar baz\n", $result->getText());
    }

    public function testExpandWorksWithAPrecedingNonAdjacentSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(0, 3);
        $result = $span->expand($other);

        self::assertEquals($other->getStart(), $result->getStart());
        self::assertEquals($span->getEnd(), $result->getEnd());
        self::assertEquals("foo bar baz\n", $result->getText());
    }

    public function testExpandWorksWithAPrecedingOverlappingSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(0, 8);
        $result = $span->expand($other);

        self::assertEquals($other->getStart(), $result->getStart());
        self::assertEquals($span->getEnd(), $result->getEnd());
        self::assertEquals("foo bar baz\n", $result->getText());
    }

    public function testExpandWorksWithAFollowingNonAdjacentSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(14, 16);
        $result = $span->expand($other);

        self::assertEquals($span->getStart(), $result->getStart());
        self::assertEquals($other->getEnd(), $result->getEnd());
        self::assertEquals("ar baz\nwhiz", $result->getText());
    }

    public function testExpandWorksWithAFollowingOverlappingSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(9, 16);
        $result = $span->expand($other);

        self::assertEquals($span->getStart(), $result->getStart());
        self::assertEquals($other->getEnd(), $result->getEnd());
        self::assertEquals("ar baz\nwhiz", $result->getText());
    }

    public function testExpandWorksWithAnInternalOverlappingSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(7, 10);

        self::assertEquals($span, $span->expand($other));
    }

    public function testExpandWorksWithAnExternalOverlappingSpan(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(0, 16);

        self::assertEquals($other, $span->expand($other));
    }

    public function testSubspanStartMustBeGreaterThanZero(): void
    {
        $span = $this->file->span(5, 11);

        $this->expectException(\OutOfRangeException::class);
        $span->subspan(-1);
    }

    public function testSubspanStartMustBeLessThanOrEqualToLength(): void
    {
        $span = $this->file->span(5, 11);

        $this->expectException(\OutOfRangeException::class);
        $span->subspan($span->getLength() + 1);
    }

    public function testSubspanEndMustBeGreaterThanStarth(): void
    {
        $span = $this->file->span(5, 11);

        $this->expectException(\OutOfRangeException::class);
        $span->subspan(2, 1);
    }

    public function testSubspanEndMustBeLessThanOrEqualToLength(): void
    {
        $span = $this->file->span(5, 11);

        $this->expectException(\OutOfRangeException::class);
        $span->subspan(0, $span->getLength() + 1);
    }

    public function testSubspanPreservesTheSourceUrl(): void
    {
        $span = $this->file->span(5, 11);
        $result = $span->subspan(1, 2);

        self::assertEquals($span->getSourceUrl(), $result->getStart()->getSourceUrl());
        self::assertEquals($span->getSourceUrl(), $result->getEnd()->getSourceUrl());
    }

    public function testSubspanReturnsTheOriginalSpanWithAnImplicitEnd(): void
    {
        $span = $this->file->span(5, 11);

        self::assertSame($span, $span->subspan(0));
    }

    public function testSubspanReturnsTheOriginalSpanWithAnExplicitEnd(): void
    {
        $span = $this->file->span(5, 11);

        self::assertSame($span, $span->subspan(0, $span->getLength()));
    }

    public function testSubspanWithinASingleLineReturnsAStrictSubstringOfTheOriginalSpan(): void
    {
        $span = $this->file->span(5, 11);

        $result = $span->subspan(1, 5);

        self::assertEquals('r ba', $result->getText());
        self::assertEquals(6, $result->getStart()->getOffset());
        self::assertEquals(0, $result->getStart()->getLine());
        self::assertEquals(6, $result->getStart()->getColumn());
        self::assertEquals(10, $result->getEnd()->getOffset());
        self::assertEquals(0, $result->getEnd()->getLine());
        self::assertEquals(10, $result->getEnd()->getColumn());
    }

    public function testSubspanWithinASingleLineAnImplicitEndGoesToTheEndOfTheOriginalSpan(): void
    {
        $span = $this->file->span(5, 11);

        $result = $span->subspan(1);

        self::assertEquals('r baz', $result->getText());
        self::assertEquals(6, $result->getStart()->getOffset());
        self::assertEquals(0, $result->getStart()->getLine());
        self::assertEquals(6, $result->getStart()->getColumn());
        self::assertEquals(11, $result->getEnd()->getOffset());
        self::assertEquals(0, $result->getEnd()->getLine());
        self::assertEquals(11, $result->getEnd()->getColumn());
    }

    public function testSubspanWithinASingleLineCanReturnAnEmptySpan(): void
    {
        $span = $this->file->span(5, 11);

        $result = $span->subspan(3, 3);

        self::assertEmpty($result->getText());
        self::assertEquals(8, $result->getStart()->getOffset());
        self::assertEquals(0, $result->getStart()->getLine());
        self::assertEquals(8, $result->getStart()->getColumn());
        self::assertEquals($result->getStart(), $result->getEnd());
    }

    public function testSubspanAcrossMultipleLinesWithStartAndEndInTheMiddleOfALine(): void
    {
        $span = $this->file->span(22, 30);

        $result = $span->subspan(3, 6);

        self::assertEquals("m\nz", $result->getText());
        self::assertEquals(25, $result->getStart()->getOffset());
        self::assertEquals(1, $result->getStart()->getLine());
        self::assertEquals(13, $result->getStart()->getColumn());
        self::assertEquals(28, $result->getEnd()->getOffset());
        self::assertEquals(2, $result->getEnd()->getLine());
        self::assertEquals(1, $result->getEnd()->getColumn());
    }

    public function testSubspanAcrossMultipleLinesWithStartAtTheEndOfALine(): void
    {
        $span = $this->file->span(22, 30);

        $result = $span->subspan(4, 6);

        self::assertEquals("\nz", $result->getText());
        self::assertEquals(26, $result->getStart()->getOffset());
        self::assertEquals(1, $result->getStart()->getLine());
        self::assertEquals(14, $result->getStart()->getColumn());
    }

    public function testSubspanAcrossMultipleLinesWithStartAtTheBeginningOfALine(): void
    {
        $span = $this->file->span(22, 30);

        $result = $span->subspan(5, 6);

        self::assertEquals('z', $result->getText());
        self::assertEquals(27, $result->getStart()->getOffset());
        self::assertEquals(2, $result->getStart()->getLine());
        self::assertEquals(0, $result->getStart()->getColumn());
    }

    public function testSubspanAcrossMultipleLinesWithEndAtTheEndOfALine(): void
    {
        $span = $this->file->span(22, 30);

        $result = $span->subspan(3, 4);

        self::assertEquals('m', $result->getText());
        self::assertEquals(26, $result->getEnd()->getOffset());
        self::assertEquals(1, $result->getEnd()->getLine());
        self::assertEquals(14, $result->getEnd()->getColumn());
    }

    public function testSubspanAcrossMultipleLinesWithEndAtTheBeginningOfALine(): void
    {
        $span = $this->file->span(22, 30);

        $result = $span->subspan(3, 5);

        self::assertEquals("m\n", $result->getText());
        self::assertEquals(27, $result->getEnd()->getOffset());
        self::assertEquals(2, $result->getEnd()->getLine());
        self::assertEquals(0, $result->getEnd()->getColumn());
    }

    public function testCompareToSortsByStartLocationFirst(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(6, 14);

        self::assertLessThan(0, $span->compareTo($other));
        self::assertGreaterThan(0, $other->compareTo($span));
    }

    public function testCompareToSortsByLengthSecond(): void
    {
        $span = $this->file->span(5, 12);
        $other = $this->file->span(5, 14);

        self::assertLessThan(0, $span->compareTo($other));
        self::assertGreaterThan(0, $other->compareTo($span));
    }

    public function testCompareToConsidersEqualSpansEqual(): void
    {
        $span = $this->file->span(5, 12);
        self::assertEquals(0, $span->compareTo($span));
    }

    public function testCompareToSupportsOtherSpans(): void
    {
        $span = $this->file->span(5, 12);
        $other = new SimpleSourceSpan(new SimpleSourceLocation(6, $this->file->getSourceUrl()), new SimpleSourceLocation(14, $this->file->getSourceUrl()), 'oo bar b');

        self::assertLessThan(0, $span->compareTo($other));
        self::assertGreaterThan(0, $other->compareTo($span));
    }
}
