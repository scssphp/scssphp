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
use ScssPhp\ScssPhp\Value\SassMap;
use ScssPhp\ScssPhp\Value\SassNumber;
use ScssPhp\ScssPhp\Value\Value;

/**
 * @testdox An empty list
 */
class EmptyListTest extends ValueTestCase
{
    /**
     * @var Value
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('()');
    }

    public function testHasAnUndecidedSeparator()
    {
        $this->assertEquals(ListSeparator::UNDECIDED, $this->value->getSeparator());
    }

    public function testReturnsItsContentsAsAList()
    {
        $this->assertEmpty($this->value->asList());
    }

    public function testEqualsAnEmptyMap()
    {
        $this->assertSassEquals($this->value, SassMap::createEmpty());
    }

    public function testCountsAsAnEmptyMap()
    {
        $this->assertEmpty($this->value->assertMap()->getContents());
        $this->assertNotNull($this->value->tryMap());
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
        yield [1];
        yield [-1];
    }
}
