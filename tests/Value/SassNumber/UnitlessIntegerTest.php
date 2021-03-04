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
 * @testdox A unitless integer
 */
class UnitlessIntegerTest extends ValueTestCase
{
    /**
     * @var SassNumber
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('123');
    }

    public function testHasTheCorrectValue()
    {
        $this->assertEquals(123, $this->value->getValue());
        $this->assertIsInt($this->value->getValue());
    }

    public function testHasNoUnits()
    {
        $this->assertEmpty($this->value->getNumeratorUnits());
        $this->assertEmpty($this->value->getDenominatorUnits());
        $this->assertFalse($this->value->hasUnits());
        $this->assertFalse($this->value->hasUnit('px'));
        $this->value->assertNoUnits(); // should not throw
    }

    public function testHasNoUnitsAssertion()
    {
        $this->expectException(SassScriptException::class);
        $this->value->assertUnit('px');
    }

    public function testIsAnInt()
    {
        $this->assertTrue($this->value->isInt());
        $this->assertEquals(123, $this->value->asInt());
        $this->assertEquals(123, $this->value->assertInt());
    }

    public function testCanBeCoercedToUnitless()
    {
        $this->assertSassEquals($this->value->coerce([], []), SassNumber::withUnits(123));
    }

    public function testCanBeCoercedToAnyUnits()
    {
        $this->assertSassEquals($this->value->coerce(['abc'], ['def']), SassNumber::withUnits(123, ['abc'], ['def']));
    }

    public function testCanBeConvertedToUnitless()
    {
        $this->assertSassEquals($this->value->convertToMatch(SassNumber::create(456)), SassNumber::withUnits(123));
    }

    public function testCantBeConvertedToAUnit()
    {
        $this->expectException(SassScriptException::class);
        $this->value->convertToMatch(SassNumber::create(456, 'px'));
    }

    public function testCanCoerceItsValueToUnitless()
    {
        $this->assertEquals(123, $this->value->coerceValue([], []));
    }

    public function testCanCoerceItsValueToAnyUnits()
    {
        $this->assertEquals(123, $this->value->coerceValue(['abc'], ['def']));
    }

    public function testCanConvertItsValueToUnitless()
    {
        $this->assertEquals(123, $this->value->convertValueToMatch(SassNumber::create(456)));
    }

    public function testCantConvertItsValueToAnyUnit()
    {
        $this->expectException(SassScriptException::class);
        $this->value->convertValueToMatch(SassNumber::create(456, 'px'));
    }

    public function testIsCompatibleWithAnyUnit()
    {
        $this->assertTrue($this->value->compatibleWithUnit('px'));
    }

    public function testValueInRangeReturnsItsValueWithinAGivenRange()
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

    public function testEqualsTheSameNumber()
    {
        $this->assertSassEquals($this->value, SassNumber::create(123));
    }

    public function testEqualsTheSameNumberWithinPrecisionTolerance()
    {
        $this->assertSassEquals($this->value, SassNumber::create(123 + pow(10, -SassNumber::PRECISION - 2)));
        $this->assertSassEquals($this->value, SassNumber::create(123 - pow(10, -SassNumber::PRECISION - 2)));
    }

    public function testDoesntEqualADifferentNumber()
    {
        $this->assertNotSassEquals($this->value, SassNumber::create(124));
        $this->assertNotSassEquals($this->value, SassNumber::create(122));
        $this->assertNotSassEquals($this->value, SassNumber::create(123 + pow(10, -SassNumber::PRECISION - 1)));
        $this->assertNotSassEquals($this->value, SassNumber::create(123 - pow(10, -SassNumber::PRECISION - 1)));
    }

    public function testDoesntEqualANumberWithUnit()
    {
        $this->assertNotSassEquals($this->value, SassNumber::create(123, 'px'));
    }

    public function testIsANumber()
    {
        $this->assertSame($this->value, $this->value->assertNumber());
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

    public function testIsNotAString()
    {
        $this->expectException(SassScriptException::class);

        $this->value->assertString();
    }
}
