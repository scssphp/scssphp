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
 * @testdox A single-element list
 */
class SingleElementTest extends ValueTestCase
{
    /**
     * @var Value
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('[1]');
    }

    public function testHasAnUndecidedSeparator()
    {
        $this->assertEquals(ListSeparator::UNDECIDED, $this->value->getSeparator());
    }

    public function testReturnsItsContentsAsAList()
    {
        $this->assertEquals([SassNumber::create(1)], $this->value->asList());
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
