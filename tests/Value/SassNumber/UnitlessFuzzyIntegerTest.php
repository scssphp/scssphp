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
 * @testdox A unitless fuzzy integer
 */
class UnitlessFuzzyIntegerTest extends ValueTestCase
{
    /**
     * @var SassNumber
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('123.000000000001');
    }

    public function testHasTheCorrectValue()
    {
        $this->assertEquals(123.000000000001, $this->value->getValue());
    }

    public function testIsAnInt()
    {
        $this->assertTrue($this->value->isInt());
        $this->assertEquals(123, $this->value->asInt());
        $this->assertEquals(123, $this->value->assertInt());
    }

    public function testEqualsTheSameNumber()
    {
        $this->assertSassEquals($this->value, SassNumber::create(123 + pow(10, -SassNumber::PRECISION - 2)));
    }

    public function testEqualsTheSameNumberWithinPrecisionTolerance()
    {
        $this->assertSassEquals($this->value, SassNumber::create(123));
        $this->assertSassEquals($this->value, SassNumber::create(123 - pow(10, -SassNumber::PRECISION - 2)));
    }

    public function testValueInRangeClampsWithinAGivenRange()
    {
        $this->assertEquals(123, $this->value->valueInRange(0, 123));
        $this->assertEquals(123, $this->value->valueInRange(123, 123));
        $this->assertEquals(123, $this->value->valueInRange(123, 1000));
    }

    /**
     * @dataProvider provideRanges
     */
    public function testValueInRangeRejectsAValueOutsideTheRange($min, $max)
    {
        $this->expectException(SassScriptException::class);
        $this->value->valueInRange($min, $max);
    }

    public static function provideRanges(): iterable
    {
        yield [0, 122];
        yield [124, 1000];
    }
}
