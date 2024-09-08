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

use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\SourceSpan\SourceFile;

class HighlighterTest extends TestCase
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

    public function testPointsToSpanInTheSource(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 | foo bar baz
  |     ^^^
  '
TXT,
            $this->file->span(4, 7)->highlight()
        );
    }

    public function testHighlightsAPointSpanInTheMiddleOfALine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 | foo bar baz
  |     ^
  '
TXT,
            $this->file->location(4)->pointSpan()->highlight()
        );
    }

    public function testHighlightsAPointSpanAtTheBeginningOfTheFile(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 | foo bar baz
  | ^
  '
TXT,
            $this->file->location(0)->pointSpan()->highlight()
        );
    }

    public function testHighlightsAPointSpanAtTheBeginningOfALine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | whiz bang boom
  | ^
  '
TXT,
            $this->file->location(12)->pointSpan()->highlight()
        );
    }

    public function testHighlightsAPointSpanAtTheEndOfALine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 | foo bar baz
  |            ^
  '
TXT,
            $this->file->location(11)->pointSpan()->highlight()
        );
    }

    public function testHighlightsAPointSpanAtTheEndOfTheFile(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
3 | zip zap zop
  |            ^
  '
TXT,
            $this->file->location(38)->pointSpan()->highlight()
        );
    }

    public function testHighlightsAPointSpanAtTheEndOfTheFileWithNoTrailingNewline(): void
    {
        $file = SourceFile::fromString('zip zap zop');

        self::assertEquals(
            <<<'TXT'
  ,
1 | zip zap zop
  |           ^
  '
TXT,
            $file->location(10)->pointSpan()->highlight()
        );
    }

    public function testHighlightsAPointSpanAfterTheEndOfTheFileWithNoTrailingNewline(): void
    {
        $file = SourceFile::fromString('zip zap zop');

        self::assertEquals(
            <<<'TXT'
  ,
1 | zip zap zop
  |            ^
  '
TXT,
            $file->location(11)->pointSpan()->highlight()
        );
    }

    public function testHighlightsAPointSpanInAnEmptyFile(): void
    {
        $file = SourceFile::fromString('');

        self::assertEquals(
            "  ,\n" .
            "1 | \n" .
            "  | ^\n" .
            "  '",
            $file->location(0)->pointSpan()->highlight()
        );
    }

    public function testHighlightsAPointSpanOnAnEmptyLine(): void
    {
        $file = SourceFile::fromString("foo\n\nbar");

        self::assertEquals(
            "  ,\n" .
            "2 | \n" .
            "  | ^\n" .
            "  '",
            $file->location(4)->pointSpan()->highlight()
        );
    }

    public function testHighlightsASingleLineFileWithoutANewline(): void
    {
        $file = SourceFile::fromString('foo bar');

        self::assertEquals(
            <<<'TXT'
  ,
1 | foo bar
  | ^^^^^^^
  '
TXT,
            $file->span(0, 7)->highlight()
        );
    }

    public function testHighlightsTextIncludingATrailingNewline(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 | foo bar baz
  |            ^
  '
TXT,
            $this->file->span(11, 12)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsTheMiddleOfTheFirstAndLastLines(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,-----^
2 | | whiz bang boom
3 | | zip zap zop
  | '-------^
  '
TXT,
            $this->file->span(4, 34)->highlight()
        );
    }

    public function testWithAMultilineSpanItWorksWhenItBeginsAtTheEndOfALine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,------------^
2 | | whiz bang boom
3 | | zip zap zop
  | '-------^
  '
TXT,
            $this->file->span(11, 34)->highlight()
        );
    }

    public function testWithAMultilineSpanItWorksWhenItEndsAtTheBeginningOfALine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,-----^
2 | | whiz bang boom
3 | | zip zap zop
  | '-^
  '
TXT,
            $this->file->span(4, 28)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsTheFullFirstLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 | / foo bar baz
2 | | whiz bang boom
3 | | zip zap zop
  | '-------^
  '
TXT,
            $this->file->span(0, 34)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsTheFullFirstLineEvenIfItIsIndented(): void
    {
        $file = SourceFile::fromString(<<<'TXT'
  foo bar baz
  whiz bang boom
  zip zap zop

TXT
        );

        self::assertEquals(
            <<<'TXT'
  ,
1 | /   foo bar baz
2 | |   whiz bang boom
3 | |   zip zap zop
  | '-------^
  '
TXT,
            $file->span(2, 38)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsTheFullFirstLineIfItIsEmpty(): void
    {
        $file = SourceFile::fromString(<<<'TXT'
foo

bar

TXT
        );

        self::assertEquals(
            "  ,\n" .
            "2 | / \n" .
            "3 | \\ bar\n" .
            "  '",
            $file->span(4, 9)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsTheFullLastLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,-----^
2 | \ whiz bang boom
  '
TXT,
            $this->file->span(4, 27)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsTheFullLastLineWithNoTrailingNewline(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,-----^
2 | \ whiz bang boom
  '
TXT,
            $this->file->span(4, 26)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsTheFullLastLineWithATrailingWindowsNewline(): void
    {
        $file = SourceFile::fromString(
            "foo bar baz\r
whiz bang boom\r
zip zap zop\r
"
        );

        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,-----^
2 | \ whiz bang boom
  '
TXT,
            $file->span(4, 29)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsTheFullLastLineAtTheEndOfTheFile(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,-----^
2 | | whiz bang boom
3 | \ zip zap zop
  '
TXT,
            $this->file->span(4, 39)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsTheFullLastLineAtTheEndOfTheFileWithNoTrailingNewline(): void
    {
        $file = SourceFile::fromString(<<<'TXT'
foo bar baz
whiz bang boom
zip zap zop
TXT
        );
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,-----^
2 | | whiz bang boom
3 | \ zip zap zop
  '
TXT,
            $file->span(4, 38)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsTheFullLastLineIfItIsEmpty(): void
    {
        $file = SourceFile::fromString(<<<'TXT'
foo

bar

TXT
        );

        self::assertEquals(
            "  ,\n" .
            "1 | / foo\n" .
            "2 | \\ \n" .
            "  '",
            $file->span(0, 5)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsMultipleEmptyLines(): void
    {
        $file = SourceFile::fromString("foo\n\n\n\nbar");

        self::assertEquals(
            "  ,\n" .
            "2 | / \n" .
            "3 | | \n" .
            "4 | \\ \n" .
            "  '",
            $file->span(4, 7)->highlight()
        );
    }

    public function testWithAMultilineSpanHighlightsTheEndOfALineAndAnEmptyLine(): void
    {
        $file = SourceFile::fromString("foo\n\n");
        self::assertEquals(
            "  ,\n" .
            "1 |   foo\n" .
            "  | ,----^\n" .
            "2 | \\ \n" .
            "  '",
            $file->span(3, 5)->highlight()
        );
    }

    public function testPrintsTabsAsSpacesInASingleLineSpanBeforeTheHighlightedSection(): void
    {
        $file = SourceFile::fromString("foo\tbar baz");
        self::assertEquals(
            <<<'TXT'
  ,
1 | foo    bar baz
  |        ^^^
  '
TXT,
            $file->span(4, 7)->highlight()
        );
    }

    public function testPrintsTabsAsSpacesInASingleLineSpanWithinTheHighlightedSection(): void
    {
        $file = SourceFile::fromString("foo bar\tbaz bang");
        self::assertEquals(
            <<<'TXT'
  ,
1 | foo bar    baz bang
  |     ^^^^^^^^^^
  '
TXT,
            $file->span(4, 11)->highlight()
        );
    }

    public function testPrintsTabsAsSpacesInASingleLineSpanAfterTheHighlightedSection(): void
    {
        $file = SourceFile::fromString("foo bar\tbaz");
        self::assertEquals(
            <<<'TXT'
  ,
1 | foo bar    baz
  |     ^^^
  '
TXT,
            $file->span(4, 7)->highlight()
        );
    }

    public function testPrintsTabsAsSpacesInAMultilineSpanBeforeTheHighlightedSection(): void
    {
        $file = SourceFile::fromString("foo\tbar baz\nwhiz bang boom");
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo    bar baz
  | ,--------^
2 | | whiz bang boom
  | '---------^
  '
TXT,
            $file->span(4, 21)->highlight()
        );
    }

    public function testPrintsTabsAsSpacesInAMultilineSpanWithinTheFirstHighlightedLine(): void
    {
        $file = SourceFile::fromString("foo bar\tbaz\nwhiz bang boom");
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar    baz
  | ,-----^
2 | | whiz bang boom
  | '---------^
  '
TXT,
            $file->span(4, 21)->highlight()
        );
    }

    public function testPrintsTabsAsSpacesInAMultilineSpanAtTheBeginningOfTheFirstHighlightedLine(): void
    {
        $file = SourceFile::fromString("foo bar\tbaz\nwhiz bang boom");
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar    baz
  | ,--------^
2 | | whiz bang boom
  | '---------^
  '
TXT,
            $file->span(7, 21)->highlight()
        );
    }

    public function testPrintsTabsAsSpacesInAMultilineSpanWithinAMiddleHighlightedLine(): void
    {
        $file = SourceFile::fromString("foo bar baz\nwhiz\tbang boom\nzip zap zop");
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,-----^
2 | | whiz    bang boom
3 | | zip zap zop
  | '-------^
  '
TXT,
            $file->span(4, 34)->highlight()
        );
    }

    public function testPrintsTabsAsSpacesInAMultilineSpanWithinTheLastHighlightedLine(): void
    {
        $file = SourceFile::fromString("foo bar baz\nwhiz\tbang boom");
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,-----^
2 | | whiz    bang boom
  | '------------^
  '
TXT,
            $file->span(4, 21)->highlight()
        );
    }

    public function testPrintsTabsAsSpacesInAMultilineSpanAtTheEndOfTheLastHighlightedLine(): void
    {
        $file = SourceFile::fromString("foo bar baz\nwhiz\tbang boom");
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,-----^
2 | | whiz    bang boom
  | '--------^
  '
TXT,
            $file->span(4, 17)->highlight()
        );
    }

    public function testPrintsTabsAsSpacesInAMultilineSpanAfterTheHighlightedSection(): void
    {
        $file = SourceFile::fromString("foo bar baz\nwhiz bang\tboom");
        self::assertEquals(
            <<<'TXT'
  ,
1 |   foo bar baz
  | ,-----^
2 | | whiz bang    boom
  | '---------^
  '
TXT,
            $file->span(4, 21)->highlight()
        );
    }

    public function testLineNumbersHaveAppropriatePaddingWithLineNumber9(): void
    {
        $file = SourceFile::fromString(str_repeat("\n", 8) . "foo bar baz\n");
        self::assertEquals(
            <<<'TXT'
  ,
9 | foo bar baz
  | ^^^
  '
TXT,
            $file->span(8, 11)->highlight()
        );
    }

    public function testLineNumbersHaveAppropriatePaddingWithLineNumber10(): void
    {
        $file = SourceFile::fromString(str_repeat("\n", 9) . "foo bar baz\n");
        self::assertEquals(
            <<<'TXT'
   ,
10 | foo bar baz
   | ^^^
   '
TXT,
            $file->span(9, 12)->highlight()
        );
    }
}
