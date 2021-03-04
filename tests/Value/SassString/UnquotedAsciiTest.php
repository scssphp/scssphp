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
 * @testdox An unquoted ASCII string
 */
class UnquotedAsciiTest extends ValueTestCase
{
    /**
     * @var SassString
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('foobar');
    }

    public function testHasTheCorrectText()
    {
        $this->assertEquals('foobar', $this->value->getText());
    }

    public function testHasNoQuotes()
    {
        $this->assertFalse($this->value->hasQuotes());
    }

    public function testEqualsTheSameString()
    {
        $this->assertSassEquals($this->value, new SassString('foobar', false));
        $this->assertSassEquals($this->value, new SassString('foobar', true));
    }

    public function testIsAString()
    {
        $this->assertSame($this->value, $this->value->assertString());
    }

    public function testIsNotABoolean()
    {
        $this->expectException(SassScriptException::class);

        $this->value->assertBoolean();
    }

    public function testIsNotAColor()
    {
        $this->expectException(SassScriptException::class);

        $this->value->assertColor();
    }

    public function testIsNotAFunction()
    {
        $this->expectException(SassScriptException::class);

        $this->value->assertFunction();
    }

    public function testIsNotAMap()
    {
        $this->assertNull($this->value->tryMap());

        $this->expectException(SassScriptException::class);

        $this->value->assertMap();
    }

    public function testIsNotANumber()
    {
        $this->expectException(SassScriptException::class);

        $this->value->assertNumber();
    }

    public function testSassLengthReturnsTheLength()
    {
        $this->assertEquals(6, $this->value->getSassLength());
    }

    /**
     * @testdox sassIndexToStringIndex() converts a positive index to a PHP index
     */
    public function testSassIndexToStringIndexConvertsAPositiveIndexToAPHPIndex()
    {
        $this->assertEquals(0, $this->value->sassIndexToStringIndex(SassNumber::create(1)));
        $this->assertEquals(1, $this->value->sassIndexToStringIndex(SassNumber::create(2)));
        $this->assertEquals(2, $this->value->sassIndexToStringIndex(SassNumber::create(3)));
        $this->assertEquals(3, $this->value->sassIndexToStringIndex(SassNumber::create(4)));
        $this->assertEquals(4, $this->value->sassIndexToStringIndex(SassNumber::create(5)));
        $this->assertEquals(5, $this->value->sassIndexToStringIndex(SassNumber::create(6)));
    }

    /**
     * @testdox sassIndexToStringIndex() converts a negative index to a PHP index
     */
    public function testSassIndexToStringIndexConvertsANegativeIndexToAPHPIndex()
    {
        $this->assertEquals(5, $this->value->sassIndexToStringIndex(SassNumber::create(-1)));
        $this->assertEquals(4, $this->value->sassIndexToStringIndex(SassNumber::create(-2)));
        $this->assertEquals(3, $this->value->sassIndexToStringIndex(SassNumber::create(-3)));
        $this->assertEquals(2, $this->value->sassIndexToStringIndex(SassNumber::create(-4)));
        $this->assertEquals(1, $this->value->sassIndexToStringIndex(SassNumber::create(-5)));
        $this->assertEquals(0, $this->value->sassIndexToStringIndex(SassNumber::create(-6)));
    }

    /**
     * @testdox sassIndexToStringIndex() rejects a non-number
     */
    public function testSassIndexToStringIndexRejectsANonNumber()
    {
        $this->expectException(SassScriptException::class);
        $this->value->sassIndexToStringIndex(new SassString('foo'));
    }

    /**
     * @testdox sassIndexToStringIndex() rejects a non-integer
     */
    public function testSassIndexToStringIndexRejectsANonInteger()
    {
        $this->expectException(SassScriptException::class);
        $this->value->sassIndexToStringIndex(SassNumber::create(1.1));
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
        $this->assertEquals(5, $this->value->sassIndexToCodePointIndex(SassNumber::create(6)));
    }

    /**
     * @testdox sassIndexToCodePointIndex() converts a negative index to a PHP index
     */
    public function testSassIndexToCodePointIndexConvertsANegativeIndexToAPHPIndex()
    {
        $this->assertEquals(5, $this->value->sassIndexToCodePointIndex(SassNumber::create(-1)));
        $this->assertEquals(4, $this->value->sassIndexToCodePointIndex(SassNumber::create(-2)));
        $this->assertEquals(3, $this->value->sassIndexToCodePointIndex(SassNumber::create(-3)));
        $this->assertEquals(2, $this->value->sassIndexToCodePointIndex(SassNumber::create(-4)));
        $this->assertEquals(1, $this->value->sassIndexToCodePointIndex(SassNumber::create(-5)));
        $this->assertEquals(0, $this->value->sassIndexToCodePointIndex(SassNumber::create(-6)));
    }

    /**
     * @testdox sassIndexToCodePointIndex() rejects a non-number
     */
    public function testSassIndexToCodePointIndexRejectsANonNumber()
    {
        $this->expectException(SassScriptException::class);
        $this->value->sassIndexToCodePointIndex(new SassString('foo'));
    }

    /**
     * @testdox sassIndexToCodePointIndex() rejects a non-integer
     */
    public function testSassIndexToCodePointIndexRejectsANonInteger()
    {
        $this->expectException(SassScriptException::class);
        $this->value->sassIndexToCodePointIndex(SassNumber::create(1.1));
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
        yield [7];
        yield [-7];
    }
}
