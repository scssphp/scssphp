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

namespace ScssPhp\ScssPhp\Tests\Value\SassColor;

use ScssPhp\ScssPhp\Tests\Value\ValueTestCase;
use ScssPhp\ScssPhp\Value\SassColor;

/**
 * @testdox new SassColor.hwb()
 */
class HwbTest extends ValueTestCase
{
    /**
     * @var SassColor
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = SassColor::hwb(120, 42, 42);
    }

    public function testHasRgbChannels()
    {
        $this->assertSame(0x6B, $this->value->getRed());
        $this->assertSame(0x94, $this->value->getGreen());
        $this->assertSame(0x6B, $this->value->getBlue());
    }

    public function testHasHslChannels()
    {
        $this->assertEquals(120, $this->value->getHue());
        $this->assertEquals(16.078431372549026, $this->value->getSaturation());
        $this->assertEquals(50, $this->value->getLightness());
    }

    public function testHasHwbChannels()
    {
        $this->assertEquals(120, $this->value->getHue());
        $this->assertEquals(41.96078431372549, $this->value->getWhiteness());
        $this->assertEquals(41.96078431372548, $this->value->getBlackness());
    }

    public function testHasAnAlphaChannel()
    {
        $this->assertEquals(1, $this->value->getAlpha());
    }

    public function testEqualsTheSameColor()
    {
        $this->assertSassEquals($this->value, SassColor::rgb(0x6B, 0x94, 0x6B));;
        $this->assertSassEquals($this->value, SassColor::hsl(120, 16, 50));;
        $this->assertSassEquals($this->value, SassColor::hwb(120, 42, 42));;
    }

    public function testAllowsValidValues()
    {
        $this->assertSassEquals(SassColor::hwb(0, 0, 0, 0), self::parseValue('rgba(255, 0, 0, 0)'));
        $this->assertSassEquals(SassColor::hwb(4320, 100, 100, 1), self::parseValue('grey'));
    }

    /**
     * @dataProvider provideInvalidPercentageValues
     */
    public function testHwbConstructorDisallowsInvalidValuesForWhiteness($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        SassColor::hwb(0, $invalidValue, 0, 0);
    }

    /**
     * @dataProvider provideInvalidPercentageValues
     */
    public function testHwbConstructorDisallowsInvalidValuesForBlackness($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        SassColor::hwb(0, 0, $invalidValue, 0);
    }

    /**
     * @dataProvider provideInvalidAlphaValues
     */
    public function testHwbConstructorDisallowsInvalidValuesForAlpha($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        SassColor::hwb(0, 0, 0, $invalidValue);
    }

    public static function provideInvalidPercentageValues(): iterable
    {
        yield [-0.1];
        yield [100.1];
    }

    public static function provideInvalidAlphaValues(): iterable
    {
        yield [-0.1];
        yield [1.1];
    }
}
