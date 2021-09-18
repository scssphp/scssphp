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
use ScssPhp\ScssPhp\Value\SassBoolean;
use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Value\Value;

class SassBooleanTest extends TestCase
{
    public function testTrueTruthy()
    {
        $this->assertTrue(SassBoolean::create(true)->isTruthy());
    }

    public function testFalseIsFalsy()
    {
        $this->assertFalse(SassBoolean::create(false)->isTruthy());
    }

    public static function provideValues(): iterable
    {
        yield [SassBoolean::create(true)];
        yield [SassBoolean::create(false)];
    }

    /**
     * @dataProvider provideValues
     */
    public function testIsABoolean(Value $value)
    {
        $this->assertSame($value, $value->assertBoolean());
    }

    /**
     * @dataProvider provideValues
     */
    public function testIsNotAColor(Value $value)
    {
        $this->expectException(SassScriptException::class);

        $value->assertColor();
    }

    /**
     * @dataProvider provideValues
     */
    public function testIsNotAFunction(Value $value)
    {
        $this->expectException(SassScriptException::class);

        $value->assertFunction();
    }

    /**
     * @dataProvider provideValues
     */
    public function testIsNotAMap(Value $value)
    {
        $this->assertNull($value->tryMap());

        $this->expectException(SassScriptException::class);

        $value->assertMap();
    }

    /**
     * @dataProvider provideValues
     */
    public function testIsNotANumber(Value $value)
    {
        $this->expectException(SassScriptException::class);

        $value->assertNumber();
    }

    /**
     * @dataProvider provideValues
     */
    public function testIsNotAString(Value $value)
    {
        $this->expectException(SassScriptException::class);

        $value->assertString();
    }
}
