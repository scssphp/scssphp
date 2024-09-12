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
use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\SourceSpan\SimpleSourceLocation;
use ScssPhp\ScssPhp\SourceSpan\SimpleSourceSpan;
use ScssPhp\ScssPhp\SourceSpan\SourceFile;

class MultipleHighlightTest extends TestCase
{
    private SourceFile $file;

    protected function setUp(): void
    {
        $this->file = SourceFile::fromString(
            <<<'TXT'
foo bar baz
whiz bang boom
zip zap zop
fwee fwoo fwip
argle bargle boo
gibble bibble bop

TXT,
            Uri::new('file1.txt')
        );
    }

    public function testHighlightsSpansOnSeparateLines(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 | foo bar baz
  |     === three
2 | whiz bang boom
  |      ^^^^ one
3 | zip zap zop
  |     === two
  '
TXT,
            $this->file->span(17, 21)->highlightMultiple('one', [
                'two' => $this->file->span(31, 34),
                'three' => $this->file->span(4, 7),
            ])
        );
    }

    public function testHighlightsNonContiguousSpans(): void
    {
        self::assertEquals(
            <<<'TXT'
    ,
1   | foo bar baz
    |     === three
2   | whiz bang boom
    |      ^^^^ one
... |
5   | argle bargle boo
    |       ====== two
    '
TXT,
            $this->file->span(17, 21)->highlightMultiple('one', [
                'two' => $this->file->span(60, 66),
                'three' => $this->file->span(4, 7),
            ])
        );
    }

    public function testHighlightsSpansOnTheSameLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | whiz bang boom
  |      ^^^^ one
  | ==== three
  |           ==== two
  '
TXT,
            $this->file->span(17, 21)->highlightMultiple('one', [
                'two' => $this->file->span(22, 26),
                'three' => $this->file->span(12, 16),
            ])
        );
    }

    public function testHighlightsOverlappingSpansOnTheSameLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | whiz bang boom
  |      ^^^^ one
  | ====== three
  |         ====== two
  '
TXT,
            $this->file->span(17, 21)->highlightMultiple('one', [
                'two' => $this->file->span(20, 26),
                'three' => $this->file->span(12, 18),
            ])
        );
    }

    public function testHighlightsMultipleMultilineSpans(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 | / foo bar baz
2 | | whiz bang boom
  | '--- three
3 | / zip zap zop
4 | | fwee fwoo fwip
  | '--- one
5 | / argle bargle boo
6 | | gibble bibble bop
  | '--- two
  '
TXT,
            $this->file->span(27, 54)->highlightMultiple('one', [
                'two' => $this->file->span(54, 89),
                'three' => $this->file->span(0, 27),
            ])
        );
    }

    public function testHighlightsMultipleOverlappingMultilineSpans(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 | /- foo bar baz
2 | |/ whiz bang boom
  | '+--- three
3 |  | zip zap zop
4 |  | fwee fwoo fwip
5 | /+ argle bargle boo
  | |'--- one
6 | |  gibble bibble bop
  | '---- two
  '
TXT,
            $this->file->span(12, 70)->highlightMultiple('one', [
                'two' => $this->file->span(54, 89),
                'three' => $this->file->span(0, 27),
            ])
        );
    }

    public function testHighlightsManyLayersOfOverlaps(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 | /--- foo bar baz
2 | |/-- whiz bang boom
3 | ||/- zip zap zop
4 | |||/ fwee fwoo fwip
  | '+++--- one
5 |  ||| argle bargle boo
6 |  ||| gibble bibble bop
  |  '++------^ two
  |   '+-------------^ three
  |    '--- four
  '
TXT,
            $this->file->span(0, 54)->highlightMultiple('one', [
                'two' => $this->file->span(12, 77),
                'three' => $this->file->span(27, 84),
                'four' => $this->file->span(39, 88),
            ])
        );
    }

    public function testHighlightsAMultilineSpanThatIsASubsetWithNoFirstOrLastLineOverlap(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | /- whiz bang boom
3 | |/ zip zap zop
4 | || fwee fwoo fwip
  | |'--- inner
5 | |  argle bargle boo
  | '---- outer
  '
TXT,
            $this->file->span(27, 53)->highlightMultiple('inner', [
                'outer' => $this->file->span(12, 70),
            ])
        );
    }

    public function testHighlightsAMultilineSpanThatIsASubsetOverlappingTheWholeFirstLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | // whiz bang boom
3 | || zip zap zop
4 | || fwee fwoo fwip
  | |'--- inner
5 | |  argle bargle boo
  | '---- outer
  '
TXT,
            $this->file->span(12, 53)->highlightMultiple('inner', [
                'outer' => $this->file->span(12, 70),
            ])
        );
    }

    public function testHighlightsAMultilineSpanThatIsASubsetOverlappingPartOfFirstLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | /- whiz bang boom
  | |,------^
3 | || zip zap zop
4 | || fwee fwoo fwip
  | |'--- inner
5 | |  argle bargle boo
  | '---- outer
  '
TXT,
            $this->file->span(17, 53)->highlightMultiple('inner', [
                'outer' => $this->file->span(12, 70),
            ])
        );
    }

    public function testHighlightsAMultilineSpanThatIsASubsetOverlappingTheWholeLastLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | /- whiz bang boom
3 | |/ zip zap zop
4 | || fwee fwoo fwip
5 | || argle bargle boo
  | |'--- inner
  | '---- outer
  '
TXT,
            $this->file->span(27, 70)->highlightMultiple('inner', [
                'outer' => $this->file->span(12, 70),
            ])
        );
    }

    public function testHighlightsAMultilineSpanThatIsASubsetOverlappingPartOfTheLastLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | /- whiz bang boom
3 | |/ zip zap zop
4 | || fwee fwoo fwip
5 | || argle bargle boo
  | |'------------^ inner
  | '---- outer
  '
TXT,
            $this->file->span(27, 66)->highlightMultiple('inner', [
                'outer' => $this->file->span(12, 70),
            ])
        );
    }

    public function testHighlightsASingleLineSpanInAMultilineSpanOnTheFirstLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | / whiz bang boom
  | |      ^^^^ inner
3 | | zip zap zop
4 | | fwee fwoo fwip
5 | | argle bargle boo
  | '--- outer
  '
TXT,
            $this->file->span(17, 21)->highlightMultiple('inner', [
                'outer' => $this->file->span(12, 70),
            ])
        );
    }

    public function testHighlightsASingleLineSpanInAMultilineSpanInTheMiddle(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | / whiz bang boom
3 | | zip zap zop
  | |     ^^^ inner
4 | | fwee fwoo fwip
5 | | argle bargle boo
  | '--- outer
  '
TXT,
            $this->file->span(31, 34)->highlightMultiple('inner', [
                'outer' => $this->file->span(12, 70),
            ])
        );
    }

    public function testHighlightsASingleLineSpanInAMultilineSpanOnTheLastLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | / whiz bang boom
3 | | zip zap zop
4 | | fwee fwoo fwip
5 | | argle bargle boo
  | |       ^^^^^^ inner
  | '--- outer
  '
TXT,
            $this->file->span(60, 66)->highlightMultiple('inner', [
                'outer' => $this->file->span(12, 70),
            ])
        );
    }

    public function testWritesHeadersWhenHighlightingMultipleFilesItWritesAllFilesUrls(): void
    {
        $span2 = SourceFile::fromString("quibble bibble boop\n", Uri::new('file2.txt'))->span(8, 14);

        self::assertEquals(
            <<<'TXT'
  ,--> file1.txt
3 | zip zap zop
  |     ^^^ one
  '
  ,--> file2.txt
1 | quibble bibble boop
  |         ====== two
  '
TXT,
            $this->file->span(31, 34)->highlightMultiple('one', [
                'two' => $span2,
            ])
        );
    }

    public function testWritesHeadersWhenHighlightingMultipleFilesItAllowsSecondarySpanToHaveNullUrl(): void
    {
        $span2 = new SimpleSourceSpan(new SimpleSourceLocation(1), new SimpleSourceLocation(4), 'foo');

        self::assertEquals(
            <<<'TXT'
  ,--> file1.txt
3 | zip zap zop
  |     ^^^ one
  '
  ,
1 | foo
  | === two
  '
TXT,
            $this->file->span(31, 34)->highlightMultiple('one', [
                'two' => $span2,
            ])
        );
    }

    public function testWritesHeadersWhenHighlightingMultipleFilesItAllowsPrimarySpanToHaveNullUrl(): void
    {
        $span1 = new SimpleSourceSpan(new SimpleSourceLocation(1), new SimpleSourceLocation(4), 'foo');

        self::assertEquals(
            <<<'TXT'
  ,
1 | foo
  | ^^^ one
  '
  ,--> file1.txt
3 | zip zap zop
  |     === two
  '
TXT,
            $span1->highlightMultiple('one', [
                'two' => $this->file->span(31, 34),
            ])
        );
    }

    public function testHighlightsMultipleNullUrlsAsSeparateFiles(): void
    {
        $span1 = new SimpleSourceSpan(new SimpleSourceLocation(1), new SimpleSourceLocation(4), 'foo');
        $span2 = new SimpleSourceSpan(new SimpleSourceLocation(1), new SimpleSourceLocation(4), 'bar');

        self::assertEquals(
            <<<'TXT'
  ,
1 | foo
  | ^^^ one
  '
  ,
1 | bar
  | === two
  '
TXT,
            $span1->highlightMultiple('one', [
                'two' => $span2,
            ])
        );
    }

    public function testIndentsMultilineLabelForThePrimaryLabel(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | whiz bang boom
  |      ^^^^ line 1
  |           line 2
  |           line 3
  '
TXT,
            $this->file->span(17, 21)->highlightMultiple("line 1\nline 2\nline 3", [])
        );
    }

    public function testIndentsMultilineLabelForASecondaryLabelOnTheSameLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | whiz bang boom
  |      ^^^^ primary
  |           ==== line 1
  |                line 2
  |                line 3
  '
TXT,
            $this->file->span(17, 21)->highlightMultiple('primary', [
                "line 1\nline 2\nline 3" => $this->file->span(22, 26),
            ])
        );
    }

    public function testIndentsMultilineLabelForASecondaryLabelOnADifferentLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | whiz bang boom
  |      ^^^^ primary
3 | zip zap zop
  |     === line 1
  |         line 2
  |         line 3
  '
TXT,
            $this->file->span(17, 21)->highlightMultiple('primary', [
                "line 1\nline 2\nline 3" => $this->file->span(31, 34),
            ])
        );
    }

    public function testIndentsMultilineLabelForAMultilineSpanThatCoversTheWholeLastLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | / whiz bang boom
3 | | zip zap zop
4 | | fwee fwoo fwip
5 | | argle bargle boo
  | '--- line 1
  |      line 2
  |      line 3
  '
TXT,
            $this->file->span(12, 70)->highlightMultiple("line 1\nline 2\nline 3", [])
        );
    }

    public function testIndentsMultilineLabelForAMultilineSpanTheCoversPartOfTheLastLine(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
2 | / whiz bang boom
3 | | zip zap zop
4 | | fwee fwoo fwip
5 | | argle bargle boo
  | '------------^ line 1
  |                line 2
  |                line 3
  '
TXT,
            $this->file->span(12, 66)->highlightMultiple("line 1\nline 2\nline 3", [])
        );
    }

    public function testIndentsMultilineLabelForAMultilineSpanWithAnOverlappingSpan(): void
    {
        self::assertEquals(
            <<<'TXT'
  ,
1 | /- foo bar baz
2 | |/ whiz bang boom
  | '+--- three
3 |  | zip zap zop
4 |  | fwee fwoo fwip
5 | /+ argle bargle boo
  | |'--- line 1
  | |     line 2
  | |     line 3
6 | |  gibble bibble bop
  | '---- two
  '
TXT,
            $this->file->span(12, 70)->highlightMultiple("line 1\nline 2\nline 3", [
                'two' => $this->file->span(54, 89),
                'three' => $this->file->span(0, 27),
            ])
        );
    }
}
