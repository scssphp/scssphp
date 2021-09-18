<?php

/**
 * SCSSPHP
 *
 * @copyright 2018-2020 Anthon Pang
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Tests\Value\SassString;

use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Tests\Value\ValueTestCase;
use ScssPhp\ScssPhp\Value\SassNumber;
use ScssPhp\ScssPhp\Value\SassString;

/**
 * @testdox An unquoted unicode string
 */
class UnquotedUnicodeTest extends ValueTestCase
{
    /**
     * @var SassString
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('a👭b👬c');
    }

    public function testSassLengthReturnsTheLength()
    {
        $this->assertEquals(5, $this->value->getSassLength());
    }

    /**
     * @testdox sassIndexToStringIndex() converts a positive index to a PHP index
     */
    public function testSassIndexToStringIndexConvertsAPositiveIndexToAPHPIndex()
    {
        $this->assertEquals(0, $this->value->sassIndexToStringIndex(SassNumber::create(1)));
        $this->assertEquals(1, $this->value->sassIndexToStringIndex(SassNumber::create(2)));
        $this->assertEquals(5, $this->value->sassIndexToStringIndex(SassNumber::create(3)));
        $this->assertEquals(6, $this->value->sassIndexToStringIndex(SassNumber::create(4)));
        $this->assertEquals(10, $this->value->sassIndexToStringIndex(SassNumber::create(5)));
    }

    /**
     * @testdox sassIndexToStringIndex() converts a negative index to a PHP index
     */
    public function testSassIndexToStringIndexConvertsANegativeIndexToAPHPIndex()
    {
        $this->assertEquals(10, $this->value->sassIndexToStringIndex(SassNumber::create(-1)));
        $this->assertEquals(6, $this->value->sassIndexToStringIndex(SassNumber::create(-2)));
        $this->assertEquals(5, $this->value->sassIndexToStringIndex(SassNumber::create(-3)));
        $this->assertEquals(1, $this->value->sassIndexToStringIndex(SassNumber::create(-4)));
        $this->assertEquals(0, $this->value->sassIndexToStringIndex(SassNumber::create(-5)));
    }

    /**
     * @testdox sassIndexToStringIndex() rejects invalid indices
     * @dataProvider provideInvalidIndices
     */
    public function testSassIndexToStringIndexRejectsInvalidIndices($index)
    {
        $this->expectException(SassScriptException::class);
        $this->value->sassIndexToStringIndex(SassNumber::create($index));
    }

    /**
     * @testdox sassIndexToCodePointIndex() converts a positive index to a PHP index
     */
    public function testSassIndexToCodePointIndexConvertsAPositiveIndexToAPHPIndex()
    {
        $this->assertEquals(0, $this->value->sassIndexToCodePointIndex(SassNumber::create(1)));
        $this->assertEquals(1, $this->value->sassIndexToCodePointIndex(SassNumber::create(2)));
        $this->assertEquals(2, $this->value->sassIndexToCodePointIndex(SassNumber::create(3)));
        $this->assertEquals(3, $this->value->sassIndexToCodePointIndex(SassNumber::create(4)));
        $this->assertEquals(4, $this->value->sassIndexToCodePointIndex(SassNumber::create(5)));
    }

    /**
     * @testdox sassIndexToCodePointIndex() converts a negative index to a PHP index
     */
    public function testSassIndexToCodePointIndexConvertsANegativeIndexToAPHPIndex()
    {
        $this->assertEquals(4, $this->value->sassIndexToCodePointIndex(SassNumber::create(-1)));
        $this->assertEquals(3, $this->value->sassIndexToCodePointIndex(SassNumber::create(-2)));
        $this->assertEquals(2, $this->value->sassIndexToCodePointIndex(SassNumber::create(-3)));
        $this->assertEquals(1, $this->value->sassIndexToCodePointIndex(SassNumber::create(-4)));
        $this->assertEquals(0, $this->value->sassIndexToCodePointIndex(SassNumber::create(-5)));
    }

    /**
     * @testdox sassIndexToCodePointIndex() rejects invalid indices
     * @dataProvider provideInvalidIndices
     */
    public function testSassIndexToCodePointIndexRejectsInvalidIndices($index)
    {
        $this->expectException(SassScriptException::class);
        $this->value->sassIndexToCodePointIndex(SassNumber::create($index));
    }

    public static function provideInvalidIndices(): iterable
    {
        yield [0];
        yield [6];
        yield [-6];
    }
}
