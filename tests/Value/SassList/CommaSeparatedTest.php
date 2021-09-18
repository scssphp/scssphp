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

namespace ScssPhp\ScssPhp\Tests\Value\SassList;

use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Tests\Value\ValueTestCase;
use ScssPhp\ScssPhp\Value\ListSeparator;
use ScssPhp\ScssPhp\Value\SassList;
use ScssPhp\ScssPhp\Value\SassNumber;
use ScssPhp\ScssPhp\Value\SassString;
use ScssPhp\ScssPhp\Value\Value;

/**
 * @testdox A comma-separated list
 */
class CommaSeparatedTest extends ValueTestCase
{
    /**
     * @var Value
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('a, b, c');
    }

    public function testIsCommaSeparated()
    {
        $this->assertEquals(ListSeparator::COMMA, $this->value->getSeparator());
    }

    public function testHasNoBrackets()
    {
        $this->assertFalse($this->value->hasBrackets());
    }

    public function testReturnsItsContentsAsAList()
    {
        $this->assertEquals([
            new SassString('a', false),
            new SassString('b', false),
            new SassString('c', false),
        ], $this->value->asList());
    }

    public function testEqualsTheSameList()
    {
        $this->assertSassEquals($this->value, new SassList([
            new SassString('a', false),
            new SassString('b', false),
            new SassString('c', false),
        ], ListSeparator::COMMA));
    }

    public function testDoesntEqualAValueWithDifferentMetadata()
    {
        $this->assertNotSassEquals($this->value, new SassList([
            new SassString('a', false),
            new SassString('b', false),
            new SassString('c', false),
        ], ListSeparator::SPACE));

        $this->assertNotSassEquals($this->value, new SassList([
            new SassString('a', false),
            new SassString('b', false),
            new SassString('c', false),
        ], ListSeparator::COMMA, true));
    }

    /**
     * @testdox sassIndexToListIndex() converts a positive index to a PHP index
     */
    public function testSassIndexToListIndexConvertsAPositiveIndexToAPHPIndex()
    {
        $this->assertEquals(0, $this->value->sassIndexToListIndex(SassNumber::create(1)));
        $this->assertEquals(1, $this->value->sassIndexToListIndex(SassNumber::create(2)));
        $this->assertEquals(2, $this->value->sassIndexToListIndex(SassNumber::create(3)));
    }

    /**
     * @testdox sassIndexToListIndex() converts a negative index to a PHP index
     */
    public function testSassIndexToListIndexConvertsANegativeIndexToAPHPIndex()
    {
        $this->assertEquals(2, $this->value->sassIndexToListIndex(SassNumber::create(-1)));
        $this->assertEquals(1, $this->value->sassIndexToListIndex(SassNumber::create(-2)));
        $this->assertEquals(0, $this->value->sassIndexToListIndex(SassNumber::create(-3)));
    }

    /**
     * @testdox sassIndexToListIndex() rejects a non-number
     */
    public function testSassIndexToListIndexRejectsANonNumber()
    {
        $this->expectException(SassScriptException::class);
        $this->value->sassIndexToListIndex(new SassString('foo'));
    }

    /**
     * @testdox sassIndexToListIndex() rejects a non-integer
     */
    public function testSassIndexToListIndexRejectsANonInteger()
    {
        $this->expectException(SassScriptException::class);
        $this->value->sassIndexToListIndex(SassNumber::create(1.1));
    }

    /**
     * @testdox sassIndexToListIndex() rejects invalid indices
     * @dataProvider provideInvalidIndices
     */
    public function testSassIndexToListIndexRejectsInvalidIndices($index)
    {
        $this->expectException(SassScriptException::class);
        $this->value->sassIndexToListIndex(SassNumber::create($index));
    }

    public static function provideInvalidIndices(): iterable
    {
        yield [0];
        yield [4];
        yield [-4];
    }

    public function testDoesntEqualAValueWithDifferentContents()
    {
        $this->assertNotSassEquals($this->value, new SassList([
            new SassString('a', false),
            new SassString('x', false),
            new SassString('c', false),
        ], ListSeparator::COMMA));
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

    public function testIsNotAString()
    {
        $this->expectException(SassScriptException::class);

        $this->value->assertString();
    }
}
