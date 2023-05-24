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

class SassColorTest extends ValueTestCase
{
    public function testAnRgbaColorHasAnAlphaChannel()
    {
        /** @var SassColor $color */
        $color = self::parseValue('rgba(10, 20, 30, 0.7)');
        $this->assertEqualsWithDelta(0.7, $color->getAlpha(), 1e-11);
    }

    /**
     * @dataProvider provideInvalidRgbValues
     */
    public function testRgbConstructorDisallowsInvalidValuesForRed($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        SassColor::rgb($invalidValue, 0, 0, 0);
    }

    /**
     * @dataProvider provideInvalidRgbValues
     */
    public function testRgbConstructorDisallowsInvalidValuesForGreen($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        SassColor::rgb(0, $invalidValue, 0, 0);
    }

    /**
     * @dataProvider provideInvalidRgbValues
     */
    public function testRgbConstructorDisallowsInvalidValuesForBlue($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        SassColor::rgb(0, 0, $invalidValue, 0);
    }

    /**
     * @dataProvider provideInvalidAlphaValues
     */
    public function testRgbConstructorDisallowsInvalidValuesForAlpha($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        SassColor::rgb(0, 0, 0, $invalidValue);
    }

    /**
     * @dataProvider provideInvalidPercentageValues
     */
    public function testHslConstructorDisallowsInvalidValuesForSaturation($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        SassColor::hsl(0, $invalidValue, 0, 0);
    }

    /**
     * @dataProvider provideInvalidPercentageValues
     */
    public function testHslConstructorDisallowsInvalidValuesForLightness($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        SassColor::hsl(0, 0, $invalidValue, 0);
    }

    /**
     * @dataProvider provideInvalidAlphaValues
     */
    public function testHslConstructorDisallowsInvalidValuesForAlpha($invalidValue)
    {
        $this->expectException(\OutOfRangeException::class);
        SassColor::hsl(0, 0, 0, $invalidValue);
    }

    public static function provideInvalidRgbValues(): iterable
    {
        yield [-1];
        yield [256];
    }

    public static function provideInvalidAlphaValues(): iterable
    {
        yield [-0.1];
        yield [1.1];
    }

    public static function provideInvalidPercentageValues(): iterable
    {
        yield [-0.1];
        yield [100.1];
    }
}
