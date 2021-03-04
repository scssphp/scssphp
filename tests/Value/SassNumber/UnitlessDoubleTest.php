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

namespace ScssPhp\ScssPhp\Tests\Value\SassNumber;

use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Tests\Value\ValueTestCase;
use ScssPhp\ScssPhp\Value\SassNumber;

/**
 * @testdox A unitless double
 */
class UnitlessDoubleTest extends ValueTestCase
{
    /**
     * @var SassNumber
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('123.456');
    }

    public function testHasTheCorrectValue()
    {
        $this->assertEquals(123.456, $this->value->getValue());
    }

    public function testIsNotAnInt()
    {
        $this->assertFalse($this->value->isInt());
        $this->assertNull($this->value->asInt());

        $this->expectException(SassScriptException::class);
        $this->value->assertInt();
    }
}
