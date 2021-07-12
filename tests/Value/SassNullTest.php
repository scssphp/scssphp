<?php

namespace ScssPhp\ScssPhp\Tests\Value;

use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Value\SassNull;
use PHPUnit\Framework\TestCase;

class SassNullTest extends TestCase
{
    public function testIsFalsy()
    {
        $this->assertFalse(SassNull::create()->isTruthy());
    }

    public function testIsNotBoolean()
    {
        $value = SassNull::create();
        $this->expectException(SassScriptException::class);

        $value->assertBoolean();
    }

    public function testIsNotColor()
    {
        $value = SassNull::create();
        $this->expectException(SassScriptException::class);

        $value->assertColor();
    }

    public function testIsNotFunction()
    {
        $value = SassNull::create();
        $this->expectException(SassScriptException::class);

        $value->assertFunction();
    }

    public function testIsNotMap()
    {
        $value = SassNull::create();

        $this->assertNull($value->tryMap());

        $this->expectException(SassScriptException::class);

        $value->assertMap();
    }

    public function testIsNotNumber()
    {
        $value = SassNull::create();
        $this->expectException(SassScriptException::class);

        $value->assertNumber();
    }

    public function testIsNotString()
    {
        $value = SassNull::create();
        $this->expectException(SassScriptException::class);

        $value->assertString();
    }
}
