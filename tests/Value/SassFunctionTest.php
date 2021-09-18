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

namespace ScssPhp\ScssPhp\Tests\Value;

use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Value\SassFunction;

class SassFunctionTest extends ValueTestCase
{
    /**
     * @var SassFunction
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue("get-function('red')");
    }

    public function testEqualsTheSameFunction()
    {
        $this->assertSassEquals($this->value, self::parseValue("get-function('red')"));
    }

    public function testIsTruthy()
    {
        $this->assertTrue($this->value->isTruthy());
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

    public function testIsAFunction()
    {
        $this->assertSame($this->value, $this->value->assertFunction());
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
