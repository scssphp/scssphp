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

namespace ScssPhp\ScssPhp\Tests\Value\SassMap;

use ScssPhp\ScssPhp\Collection\Map;
use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Tests\Value\ValueTestCase;
use ScssPhp\ScssPhp\Value\ListSeparator;
use ScssPhp\ScssPhp\Value\SassList;
use ScssPhp\ScssPhp\Value\SassMap;
use ScssPhp\ScssPhp\Value\SassNumber;
use ScssPhp\ScssPhp\Value\SassString;

class ContentsTest extends ValueTestCase
{
    /**
     * @var SassMap
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('(a: b, c: d)');
    }

    public function testHasACommaSeparator()
    {
        $this->assertEquals(ListSeparator::COMMA, $this->value->getSeparator());
    }

    public function testReturnsItsContentAsAMap()
    {
        $map = new Map();
        $map->put(new SassString('a', false), new SassString('b', false));
        $map->put(new SassString('c', false), new SassString('d', false));

        $this->assertEquals(Map::unmodifiable($map), $this->value->getContents());
    }

    public function testReturnsItsContentAsAList()
    {
        $list = [
            new SassList([new SassString('a', false), new SassString('b', false)], ListSeparator::SPACE),
            new SassList([new SassString('c', false), new SassString('d', false)], ListSeparator::SPACE),
        ];
        $this->assertEquals($list, $this->value->asList());
    }

    /**
     * @testdox sassIndexToListIndex() converts a positive index to a PHP index
     */
    public function testSassIndexToListIndexConvertsAPositiveIndexToAPHPIndex()
    {
        $this->assertEquals(0, $this->value->sassIndexToListIndex(SassNumber::create(1)));
        $this->assertEquals(1, $this->value->sassIndexToListIndex(SassNumber::create(2)));
    }

    /**
     * @testdox sassIndexToListIndex() converts a negative index to a PHP index
     */
    public function testSassIndexToListIndexConvertsANegativeIndexToAPHPIndex()
    {
        $this->assertEquals(1, $this->value->sassIndexToListIndex(SassNumber::create(-1)));
        $this->assertEquals(0, $this->value->sassIndexToListIndex(SassNumber::create(-2)));
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
        yield [3];
        yield [-3];
    }

    public function testEqualsTheSameMap()
    {
        $map = new Map();
        $map->put(new SassString('a', false), new SassString('b', false));
        $map->put(new SassString('c', false), new SassString('d', false));

        $this->assertSassEquals($this->value, SassMap::create($map));
    }

    public function testDoesntEqualTheEquivalentList()
    {
        $list = new SassList([
            new SassList([new SassString('a', false), new SassString('b', false)], ListSeparator::SPACE),
            new SassList([new SassString('c', false), new SassString('d', false)], ListSeparator::SPACE),
        ], ListSeparator::COMMA);

        $this->assertNotSassEquals($this->value, $list);
    }

    public function testDoesntEqualAMapWithADifferentValue()
    {
        $map = new Map();
        $map->put(new SassString('a', false), new SassString('x', false));
        $map->put(new SassString('c', false), new SassString('d', false));

        $this->assertNotSassEquals($this->value, SassMap::create($map));
    }

    public function testDoesntEqualAMapWithADifferentKey()
    {
        $map = new Map();
        $map->put(new SassString('a', false), new SassString('b', false));
        $map->put(new SassString('x', false), new SassString('d', false));

        $this->assertNotSassEquals($this->value, SassMap::create($map));
    }

    public function testDoesntEqualAMapWithAMissingPair()
    {
        $map = new Map();
        $map->put(new SassString('a', false), new SassString('b', false));

        $this->assertNotSassEquals($this->value, SassMap::create($map));
    }

    public function testDoesntEqualAMapWithAnAdditionalPair()
    {
        $map = new Map();
        $map->put(new SassString('a', false), new SassString('b', false));
        $map->put(new SassString('c', false), new SassString('d', false));
        $map->put(new SassString('e', false), new SassString('f', false));

        $this->assertNotSassEquals($this->value, SassMap::create($map));
    }

    public function testIsAMap()
    {
        $this->assertSame($this->value, $this->value->assertMap());
        $this->assertSame($this->value, $this->value->tryMap());
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
}
