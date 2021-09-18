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

use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Tests\Value\ValueTestCase;
use ScssPhp\ScssPhp\Value\ListSeparator;
use ScssPhp\ScssPhp\Value\SassList;
use ScssPhp\ScssPhp\Value\SassMap;
use ScssPhp\ScssPhp\Value\SassNumber;

/**
 * @testdox An empty map
 */
class EmptyTest extends ValueTestCase
{
    /**
     * @var SassMap
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('map-remove((a: b), a)');
    }

    public function testHasAnUndecidedSeparator()
    {
        $this->assertEquals(ListSeparator::UNDECIDED, $this->value->getSeparator());
    }

    public function testReturnsItsContentAsAMap()
    {
        $this->assertEmpty($this->value->getContents());
    }

    public function testReturnsItsContentAsAList()
    {
        $this->assertEmpty($this->value->asList());
    }

    public function testEqualsAnEmptyList()
    {
        $this->assertSassEquals($this->value, SassList::createEmpty());
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
