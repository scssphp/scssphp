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
 * @testdox A number with numerator and denominator units
 */
class ComplexUnitNumberTest extends ValueTestCase
{
    /**
     * @var SassNumber
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('123px / 5ms');
    }

    public function testHasThoseUnits()
    {
        $this->assertEquals(['px'], $this->value->getNumeratorUnits());
        $this->assertEquals(['ms'], $this->value->getDenominatorUnits());
        $this->assertTrue($this->value->hasUnits());
    }

    public function testHasNoUnitsAssertion()
    {
        $this->expectException(SassScriptException::class);
        $this->value->assertNoUnits();
    }

    /**
     * @testdox reports false for hasUnit()
     */
    public function testReportsFalseForHasUnit()
    {
        $this->assertFalse($this->value->hasUnit('px'));

        $this->expectException(SassScriptException::class);
        $this->value->assertUnit('px');
    }

    public function testCanBeCoercedToUnitless()
    {
        $this->assertSassEquals($this->value->coerce([], []), SassNumber::withUnits(24.6));
    }

    public function testCanBeCoercedToCompatibleUnits()
    {
        $this->assertEquals($this->value->coerce(['px'], ['ms']), $this->value);
        $this->assertEquals($this->value->coerce(['in'], ['s']), SassNumber::withUnits(256.25, ['in'], ['s']));
    }

    public function testCanCoerceToMatchAnotherNumber()
    {
        $this->assertEquals($this->value->coerceToMatch(SassNumber::withUnits(456, ['in'], ['s'])), SassNumber::withUnits(256.25, ['in'], ['s']));
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
        $this->assertEquals($this->value->convertToMatch(SassNumber::withUnits(456, ['px'], ['ms'])), $this->value);
        $this->assertEquals($this->value->convertToMatch(SassNumber::withUnits(456, ['in'], ['s'])), SassNumber::withUnits(256.25, ['in'], ['s']));
    }

    public function testCantBeConvertedToIncompatibleUnit()
    {
        $this->expectException(SassScriptException::class);
        $this->value->convertToMatch(SassNumber::create(456, 'abc'));
    }

    public function testCanCoerceItsValueToUnitless()
    {
        $this->assertEquals(24.6, $this->value->coerceValue([], []));
    }

    public function testCanCoerceItsValueToCompatibleUnits()
    {
        $this->assertEquals(24.6, $this->value->coerceValue(['px'], ['ms']));
        $this->assertEquals(256.25, $this->value->coerceValue(['in'], ['s']));
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
        $this->assertEquals(24.6, $this->value->convertValueToMatch(SassNumber::withUnits(456, ['px'], ['ms'])));
        $this->assertEquals(256.25, $this->value->convertValueToMatch(SassNumber::withUnits(456, ['in'], ['s'])));
    }

    public function testCantConvertItsValueToIncompatibleUnit()
    {
        $this->expectException(SassScriptException::class);
        $this->value->convertValueToMatch(SassNumber::create(456, 'abc'));
    }

    public function testIsIncompatibleWithTheNumeratorUnit()
    {
        $this->assertFalse($this->value->compatibleWithUnit('px'));
    }

    public function testIsIncompatibleWithTheDenominatorUnit()
    {
        $this->assertFalse($this->value->compatibleWithUnit('ms'));
    }

    public function testEqualsTheSameNumber()
    {
        $this->assertSassEquals($this->value, SassNumber::withUnits(24.6, ['px'], ['ms']));
    }

    public function testEqualsAnEquivalentNumber()
    {
        $this->assertSassEquals($this->value, SassNumber::withUnits(256.25, ['in'], ['s']));
    }

    public function testDoesntEqualAUnitlessNumber()
    {
        $this->assertNotSassEquals($this->value, SassNumber::create(24.6));
    }

    public function testDoesntEqualANumberWithDifferentUnits()
    {
        $this->assertNotSassEquals($this->value, SassNumber::create(24.6, 'px'));
        $this->assertNotSassEquals($this->value, SassNumber::withUnits(24.6, ['ms'], ['px']));
        $this->assertNotSassEquals($this->value, SassNumber::withUnits(24.6, [], ['ms']));
        $this->assertNotSassEquals($this->value, SassNumber::withUnits(24.6, ['in'], ['s']));
    }
}
