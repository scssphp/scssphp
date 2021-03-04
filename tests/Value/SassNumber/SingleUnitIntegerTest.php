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
 * @testdox An integer with a single numerator unit
 */
class SingleUnitIntegerTest extends ValueTestCase
{
    /**
     * @var SassNumber
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('123px');
    }

    public function testHasThatUnits()
    {
        $this->assertEquals(['px'], $this->value->getNumeratorUnits());
        $this->assertTrue($this->value->hasUnits());
        $this->assertTrue($this->value->hasUnit('px'));
        $this->value->assertUnit('px'); // should not throw
    }

    public function testHasNoUnitsAssertion()
    {
        $this->expectException(SassScriptException::class);
        $this->value->assertNoUnits();
    }

    public function testHasNoOtherUnits()
    {
        $this->assertEmpty($this->value->getDenominatorUnits());
        $this->assertFalse($this->value->hasUnit('in'));

        $this->expectException(SassScriptException::class);
        $this->value->assertUnit('in');
    }

    public function testCanBeCoercedToUnitless()
    {
        $this->assertSassEquals($this->value->coerce([], []), SassNumber::withUnits(123));
    }

    public function testCanBeCoercedToCompatibleUnits()
    {
        $this->assertEquals($this->value->coerce(['px'], []), $this->value);
        $this->assertEquals($this->value->coerce(['in'], []), SassNumber::create(1.28125, 'in'));
    }

    public function testCantBeCoercedToIncompatibleUnits()
    {
        $this->expectException(SassScriptException::class);
        $this->value->coerce(['abc'], []);
    }

    public function testCantBeConvertedToUnitless()
    {
        $this->expectException(SassScriptException::class);
        $this->value->convertToMatch(SassNumber::create(456));
    }

    public function testCanBeConvertedToCompatibleUnits()
    {
        $this->assertEquals($this->value->convertToMatch(SassNumber::create(456, 'px')), $this->value);
        $this->assertEquals($this->value->convertToMatch(SassNumber::create(456, 'in')), SassNumber::create(1.28125, 'in'));
    }

    public function testCantBeConvertedToIncompatibleUnit()
    {
        $this->expectException(SassScriptException::class);
        $this->value->convertToMatch(SassNumber::create(456, 'abc'));
    }

    public function testCanCoerceItsValueToUnitless()
    {
        $this->assertEquals(123, $this->value->coerceValue([], []));
    }

    public function testCanCoerceItsValueToCompatibleUnits()
    {
        $this->assertEquals(123, $this->value->coerceValue(['px'], []));
        $this->assertEquals(1.28125, $this->value->coerceValue(['in'], []));
    }

    public function testCantCoerceItsValueToIncompatibleUnits()
    {
        $this->expectException(SassScriptException::class);
        $this->value->coerceValue(['abc'], []);
    }

    public function testCantConvertItsValueToUnitless()
    {
        $this->expectException(SassScriptException::class);
        $this->value->convertValueToMatch(SassNumber::create(456));
    }

    public function testCanConvertItsValueToCompatibleUnits()
    {
        $this->assertEquals(123, $this->value->convertValueToMatch(SassNumber::create(456, 'px')));
        $this->assertEquals(1.28125, $this->value->convertValueToMatch(SassNumber::create(456, 'in')));
    }

    public function testCantConvertItsValueToIncompatibleUnit()
    {
        $this->expectException(SassScriptException::class);
        $this->value->convertValueToMatch(SassNumber::create(456, 'abc'));
    }

    public function testIsCompatibleWithTheSameUnit()
    {
        $this->assertTrue($this->value->compatibleWithUnit('px'));
    }

    public function testIsCompatibleWithACompatibleUnit()
    {
        $this->assertTrue($this->value->compatibleWithUnit('in'));
    }

    public function testIsIncompatibleWithAnIncompatibleUnit()
    {
        $this->assertFalse($this->value->compatibleWithUnit('abc'));
    }

    public function testEqualsTheSameNumber()
    {
        $this->assertSassEquals($this->value, SassNumber::create(123, 'px'));
    }

    public function testEqualsAnEquivalentNumber()
    {
        $this->assertSassEquals($this->value, SassNumber::create(1.28125, 'in'));
    }

    public function testDoesntEqualAUnitlessNumber()
    {
        $this->assertNotSassEquals($this->value, SassNumber::create(123));
    }

    public function testDoesntEqualANumberWithDifferentUnits()
    {
        $this->assertNotSassEquals($this->value, SassNumber::create(123, 'abc'));
        $this->assertNotSassEquals($this->value, SassNumber::withUnits(123, ['px', 'px']));
        $this->assertNotSassEquals($this->value, SassNumber::withUnits(123, ['px'], ['abc']));
        $this->assertNotSassEquals($this->value, SassNumber::withUnits(123, [], ['px']));
    }
}
