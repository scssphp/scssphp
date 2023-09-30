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
 * @testdox An HSL color
 */
class HslTest extends ValueTestCase
{
    /**
     * @var SassColor
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('hsl(120, 42%, 42%)');
    }

    public function testHasRgbChannels()
    {
        $this->assertSame(0x3E, $this->value->getRed());
        $this->assertSame(0x98, $this->value->getGreen());
        $this->assertSame(0x3E, $this->value->getBlue());
    }

    public function testHasHslChannels()
    {
        $this->assertEquals(120, $this->value->getHue());
        $this->assertEquals(42, $this->value->getSaturation());
        $this->assertEquals(42, $this->value->getLightness());
    }

    public function testHasHwbChannels()
    {
        $this->assertEquals(120, $this->value->getHue());
        $this->assertEquals(24.313725490196077, $this->value->getWhiteness());
        $this->assertEquals(40.3921568627451, $this->value->getBlackness());
    }

    public function testHasAnAlphaChannel()
    {
        $this->assertEquals(1, $this->value->getAlpha());
    }

    public function testEqualsTheSameColor()
    {
        $rgbValue = SassColor::rgb(0x3E, 0x98, 0x3E);
        $hslValue = SassColor::hsl(120, 42, 42);
        $hwbValue = SassColor::hwb(120, 24.313725490196077, 40.3921568627451);

        $this->assertSassEquals($this->value, $rgbValue);
        $this->assertSassEquals($this->value, $hslValue);
        ;
        $this->assertSassEquals($this->value, $hwbValue);
        ;
    }
}
