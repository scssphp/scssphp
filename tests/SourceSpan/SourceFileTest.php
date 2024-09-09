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
use ScssPhp\ScssPhp\SourceSpan\SourceFile;
use PHPUnit\Framework\TestCase;

class SourceFileTest extends TestCase
{
    private SourceFile $file;

    protected function setUp(): void
    {
        $this->file = SourceFile::fromString(<<<'TXT'
foo bar baz
whiz bang boom
zip zap zop
TXT
        );
    }

    public function testErrorsForSpanEndMustComeAfterStart(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->file->span(10, 5);
    }

    public function testErrorsForSpanStartMayNotBeNegative(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->span(-1, 5);
    }

    public function testErrorsForSpanEndMayNotBeOutsideTheFile(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->span(10, 100);
    }

    public function testErrorsForLocationOffsetMayNotBeNegative(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->location(-1);
    }

    public function testErrorsForLocationOffsetMayNotBeOutsideTheFile(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->location(100);
    }

    public function testErrorsForGetLineOffsetMayNotBeNegative(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->getLine(-1);
    }

    public function testErrorsForGetLineOffsetMayNotBeOutsideTheFile(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->getLine(100);
    }

    public function testErrorsForGetColumnOffsetMayNotBeNegative(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->getColumn(-1);
    }

    public function testErrorsForGetColumnOffsetMayNotBeOutsideTheFile(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->getColumn(100);
    }

    public function testErrorsForGetOffsetLineMayNotBeNegative(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->getOffset(-1);
    }

    public function testErrorsForGetOffsetColumnMayNotBeNegative(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->getOffset(1, -1);
    }

    public function testErrorsForGetOffsetLineMayNotBeOutsideTheFile(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->getOffset(100);
    }

    public function testErrorsForGetOffsetColumnMayNotBeOutsideTheFile(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->getOffset(2, 100);
    }

    public function testErrorsForGetOffsetColumnMayNotBeOutsideTheLine(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->getOffset(1, 20);
    }

    public function testErrorsForGetTextEndMustComeAfterStart(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->file->getText(10, 5);
    }

    public function testErrorsForGetTextStartMayNotBeNegative(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->getText(-1, 5);
    }

    public function testErrorsForGetTextEndMayNotBeOutsideTheFile(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->file->getText(10, 100);
    }

    public function testErrorsForSpanUnionSourceUrlsMustMatch(): void
    {
        $other = SourceFile::fromString(
            <<<'TXT'
foo bar baz
whiz bang boom
zip zap zop
TXT,
            Uri::new('bar.dart')
        )->span(10, 11);

        $span = $this->file->span(9, 10);

        $this->expectException(\InvalidArgumentException::class);
        $span->union($other);
    }

    public function testErrorsForSpanUnionSpansMayNotBeDisjoint(): void
    {
        $span = $this->file->span(9, 10);
        $other = $this->file->span(11, 12);

        $this->expectException(\InvalidArgumentException::class);
        $span->union($other);
    }

    public function testErrorsForSpanExpandSourceUrlsMustMatch(): void
    {
        $other = SourceFile::fromString(
            <<<'TXT'
foo bar baz
whiz bang boom
zip zap zop
TXT,
            Uri::new('bar.dart')
        )->span(10, 11);

        $span = $this->file->span(9, 10);

        $this->expectException(\InvalidArgumentException::class);
        $span->expand($other);
    }

    public function testFieldsWorkCorrectly(): void
    {
        self::assertEquals(3, $this->file->getLines());
        self::assertEquals(38, $this->file->getLength());
    }

    public function testConstructorHandlesCrlfCorrectly(): void
    {
        self::assertEquals(1, SourceFile::fromString("foo\r\nbar")->getLine(6));
    }

    public function testConstructorHandlesALoneCrCorrectly(): void
    {
        self::assertEquals(1, SourceFile::fromString("foo\rbar")->getLine(5));
    }

    public function testSpanReturnsASpanBetweenTheGivenOffsets(): void
    {
        $span = $this->file->span(5, 10);

        self::assertEquals($this->file->location(5), $span->getStart());
        self::assertEquals($this->file->location(10), $span->getEnd());
    }

    public function testSpanEndDefaultsToTheEndOfTheFile(): void
    {
        $span = $this->file->span(5);

        self::assertEquals($this->file->location(5), $span->getStart());
        self::assertEquals($this->file->location($this->file->getLength()), $span->getEnd());
    }

    public function testGetLineWorksForAMiddleCharacterOfTheLine(): void
    {
        self::assertEquals(1, $this->file->getLine(15));
    }

    public function testGetLineWorksForTheFirstCharacterOfTheLine(): void
    {
        self::assertEquals(1, $this->file->getLine(12));
    }

    public function testGetLineWorksForANewlineCharacter(): void
    {
        self::assertEquals(0, $this->file->getLine(11));
    }

    public function testGetLineWorksForTheLastOffset(): void
    {
        self::assertEquals(2, $this->file->getLine($this->file->getLength()));
    }

    public function testGetOffsetWorksForAMiddleCharacterOfTheLine(): void
    {
        self::assertEquals(15, $this->file->getOffset(1, 3));
    }

    public function testGetOffsetWorksForTheFirstCharacterOfTheLine(): void
    {
        self::assertEquals(12, $this->file->getOffset(1));
    }

    public function testGetOffsetWorksForANewlineCharacter(): void
    {
        self::assertEquals(11, $this->file->getOffset(0, 11));
    }

    public function testGetOffsetWorksForTheLastOffset(): void
    {
        self::assertEquals($this->file->getLength(), $this->file->getOffset(2, 11));
    }

    public function testGetTextReturnsASubstringOfTheSource(): void
    {
        self::assertEquals("baz\nwhi", $this->file->getText(8, 15));
    }

    public function testGetTextEndDefaultsToTheEndOfTheFile(): void
    {
        self::assertEquals("g boom\nzip zap zop", $this->file->getText(20));
    }
}
