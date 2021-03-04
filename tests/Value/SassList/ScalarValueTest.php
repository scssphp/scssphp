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
use ScssPhp\ScssPhp\Value\SassNumber;
use ScssPhp\ScssPhp\Value\Value;

/**
 * @testdox A scalar value
 */
class ScalarValueTest extends ValueTestCase
{
    /**
     * @var Value
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('blue');
    }

    public function testHasAnUndecidedSeparator()
    {
        $this->assertEquals(ListSeparator::UNDECIDED, $this->value->getSeparator());
    }

    public function testHasNoBrackets()
    {
        $this->assertFalse($this->value->hasBrackets());
    }

    public function testReturnsItselfAsAList()
    {
        $list = $this->value->asList();

        $this->assertCount(1, $list);
        $this->assertSame($this->value, $list[0]);
    }

    /**
     * @testdox sassIndexToListIndex() converts a positive index to a PHP index
     */
    public function testSassIndexToListIndexConvertsAPositiveIndexToAPHPIndex()
    {
        $this->assertEquals(0, $this->value->sassIndexToListIndex(SassNumber::create(1)));
    }

    /**
     * @testdox sassIndexToListIndex() converts a negative index to a PHP index
     */
    public function testSassIndexToListIndexConvertsANegativeIndexToAPHPIndex()
    {
        $this->assertEquals(0, $this->value->sassIndexToListIndex(SassNumber::create(-1)));
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
        yield [2];
        yield [-2];
    }
}
