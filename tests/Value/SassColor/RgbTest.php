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

use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Tests\Value\ValueTestCase;
use ScssPhp\ScssPhp\Value\SassColor;

/**
 * @testdox An RGB Color
 */
class RgbTest extends ValueTestCase
{
    /**
     * @var SassColor
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('#123456');
    }

    public function testHasRgbChannels()
    {
        $this->assertSame(0x12, $this->value->getRed());
        $this->assertSame(0x34, $this->value->getGreen());
        $this->assertSame(0x56, $this->value->getBlue());
    }

    public function testHasHslChannels()
    {
        $this->assertEquals(210, $this->value->getHue());
        $this->assertEquals(65.3846153846154, $this->value->getSaturation());
        $this->assertEquals(20.392156862745097, $this->value->getLightness());
    }

    public function testHasHwbChannels()
    {
        $this->assertEquals(210, $this->value->getHue());
        $this->assertEquals(7.0588235294117645, $this->value->getWhiteness());
        $this->assertEquals(66.27450980392157, $this->value->getBlackness());
    }

    public function testHasAnAlphaChannel()
    {
        $this->assertEquals(1, $this->value->getAlpha());
    }

    public function testEqualsTheSameValue()
    {
        $rgbValue = SassColor::rgb(0x12, 0x34, 0x56);
        $hslValue = SassColor::hsl(210, 65.3846153846154, 20.392156862745097);

        $this->assertSassEquals($this->value, $rgbValue);
        $this->assertSassEquals($this->value, $hslValue);
    }

    public function testChangeRgbChangesRGBValues()
    {
        $this->assertSassEquals($this->value->changeRgb(0xAA), SassColor::rgb(0xAA, 0x34, 0x56));
        $this->assertSassEquals($this->value->changeRgb(null, 0xAA), SassColor::rgb(0x12, 0xAA, 0x56));
        $this->assertSassEquals($this->value->changeRgb(null, null, 0xAA), SassColor::rgb(0x12, 0x34, 0xAA));
        $this->assertSassEquals($this->value->changeRgb(null, null, null, 0.5), SassColor::rgb(0x12, 0x34, 0x56, 0.5));
        $this->assertSassEquals($this->value->changeRgb(0xAA, 0xAA, 0xAA, 0.5), SassColor::rgb(0xAA, 0xAA, 0xAA, 0.5));
    }

    public function testChangeRgbAllowsValidValues()
    {
        $this->assertEquals(0, $this->value->changeRgb(0)->getRed());
        $this->assertEquals(0xFF, $this->value->changeRgb(0xFF)->getRed());
        $this->assertEquals(0, $this->value->changeRgb(null, 0)->getGreen());
        $this->assertEquals(0xFF, $this->value->changeRgb(null, 0xFF)->getGreen());
        $this->assertEquals(0, $this->value->changeRgb(null, null, 0)->getBlue());
        $this->assertEquals(0xFF, $this->value->changeRgb(null, null, 0xFF)->getBlue());
        $this->assertEquals(0, $this->value->changeRgb(null, null, null, 0)->getAlpha());
        $this->assertEquals(1, $this->value->changeRgb(null, null, null, 1)->getAlpha());
    }

    /**
     * @dataProvider provideInvalidRgbValues
     */
    public function testChangeRgbDisallowsInvalidValuesForRed($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        $this->value->changeRgb($invalidValue);
    }

    /**
     * @dataProvider provideInvalidRgbValues
     */
    public function testChangeRgbDisallowsInvalidValuesForGreen($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        $this->value->changeRgb(null, $invalidValue);
    }

    /**
     * @dataProvider provideInvalidRgbValues
     */
    public function testChangeRgbDisallowsInvalidValuesForBlue($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        $this->value->changeRgb(null, null, $invalidValue);
    }

    public static function provideInvalidRgbValues(): iterable
    {
        yield [-1];
        yield [256];
    }

    /**
     * @dataProvider provideInvalidAlphaValues
     */
    public function testChangeRgbDisallowsInvalidValuesForAlpha($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        $this->value->changeRgb(null, null, null, $invalidValue);
    }

    public static function provideInvalidAlphaValues(): iterable
    {
        yield [-0.1];
        yield [1.1];
    }

    public function testChangeHslChangesHSLValues()
    {
        $this->assertSassEquals($this->value->changeHsl(120), SassColor::hsl(120, 65.3846153846154, 20.392156862745097));
        $this->assertSassEquals($this->value->changeHsl(null, 42), SassColor::hsl(210, 42, 20.392156862745097));
        $this->assertSassEquals($this->value->changeHsl(null, null, 42), SassColor::hsl(210, 65.3846153846154, 42));
        $this->assertSassEquals($this->value->changeHsl(null, null, null, 0.5), SassColor::hsl(210, 65.3846153846154, 20.392156862745097, 0.5));
        $this->assertSassEquals($this->value->changeHsl(120, 42, 42, 0.5), SassColor::hsl(120, 42, 42, 0.5));
    }

    public function testChangeHslAllowsValidValues()
    {
        $this->assertEquals(0, $this->value->changeHsl(null, 0)->getSaturation());
        $this->assertEquals(100, $this->value->changeHsl(null, 100)->getSaturation());
        $this->assertEquals(0, $this->value->changeHsl(null, null, 0)->getLightness());
        $this->assertEquals(100, $this->value->changeHsl(null, null, 100)->getLightness());
        $this->assertEquals(0, $this->value->changeHsl(null, null, null, 0)->getAlpha());
        $this->assertEquals(1, $this->value->changeHsl(null, null, null, 1)->getAlpha());
    }

    /**
     * @dataProvider provideInvalidPercentageValues
     */
    public function testChangeHslDisallowsInvalidValuesForSaturation($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        $this->value->changeHsl(null, $invalidValue);
    }

    /**
     * @dataProvider provideInvalidPercentageValues
     */
    public function testChangeHslDisallowsInvalidValuesForLightness($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        $this->value->changeHsl(null, null, $invalidValue);
    }

    public static function provideInvalidPercentageValues(): iterable
    {
        yield [-0.1];
        yield [100.1];
    }

    /**
     * @dataProvider provideInvalidAlphaValues
     */
    public function testChangeHslDisallowsInvalidValuesForAlpha($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        $this->value->changeHsl(null, null, null, $invalidValue);
    }

    public function testChangeHwbChangesHWBValues()
    {
        $this->assertSassEquals($this->value->changeHwb(120), SassColor::hwb(120, 7.0588235294117645, 66.27450980392157));
        $this->assertSassEquals($this->value->changeHwb(null, 20), SassColor::hwb(210, 20, 66.27450980392157));
        $this->assertSassEquals($this->value->changeHwb(null, null, 42), SassColor::hwb(210, 7.0588235294117645, 42));
        $this->assertSassEquals($this->value->changeHwb(null, null, null, 0.5), SassColor::hwb(210, 7.0588235294117645, 66.27450980392157, 0.5));
        $this->assertSassEquals($this->value->changeHwb(120, 42, 42, 0.5), SassColor::hwb(120, 42, 42, 0.5));
        $this->assertSassEquals($this->value->changeHwb(null, 50), SassColor::hwb(210, 43, 57));
    }

    public function testChangeHwbAllowsValidValues()
    {
        $this->assertEquals(0, $this->value->changeHwb(null, 0)->getWhiteness());
        $this->assertEquals(60.0, $this->value->changeHwb(null, 100)->getWhiteness());
        $this->assertEquals(0, $this->value->changeHwb(null, null, 0)->getBlackness());
        $this->assertEquals(93.33333333333333, $this->value->changeHwb(null, null, 100)->getBlackness());
        $this->assertEquals(0, $this->value->changeHwb(null, null, null, 0)->getAlpha());
        $this->assertEquals(1, $this->value->changeHwb(null, null, null, 1)->getAlpha());
    }

    /**
     * @dataProvider provideInvalidPercentageValues
     */
    public function testChangeHwbDisallowsInvalidValuesForWhiteness($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        $this->value->changeHwb(null, $invalidValue);
    }

    /**
     * @dataProvider provideInvalidPercentageValues
     */
    public function testChangeHwbDisallowsInvalidValuesForBlackness($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        $this->value->changeHwb(null, null, $invalidValue);
    }

    /**
     * @dataProvider provideInvalidAlphaValues
     */
    public function testChangeHwbDisallowsInvalidValuesForAlpha($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        $this->value->changeHwb(null, null, null, $invalidValue);
    }

    public function testChangeAlphaChangesTheAlphaValue()
    {
        $this->assertSassEquals($this->value->changeAlpha(0.5), SassColor::rgb(0x12, 0x34, 0x56, 0.5));
    }

    public function testChangeAlphaAcceptsValidAlpha()
    {
        $this->assertEquals(0, $this->value->changeAlpha(0)->getAlpha());
        $this->assertEquals(1, $this->value->changeAlpha(1)->getAlpha());
    }

    /**
     * @dataProvider provideInvalidAlphaValues
     */
    public function testChangeAlphaRejectsInvalidAlpha($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        $this->value->changeAlpha($invalidValue);
    }

    public function testIsTruthy()
    {
        $this->assertTrue(SassColor::rgb(0x12, 0x34, 0x56)->isTruthy());
    }

    public function testIsNotABoolean()
    {
        $this->expectException(SassScriptException::class);

        $this->value->assertBoolean();
    }

    public function testIsAColor()
    {
        $this->assertSame($this->value, $this->value->assertColor());
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
