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

use ScssPhp\ScssPhp\Value\SassNumber;
use PHPUnit\Framework\TestCase;

class SassNumberTest extends TestCase
{
    public function testCreateCanCreateAUnitlessNumber()
    {
        $number = SassNumber::create(123.456);
        $this->assertEquals(123.456, $number->getValue());
        $this->assertFalse($number->hasUnits());
    }

    public function testCreateCanCreateANumberWithANumerator()
    {
        $number = SassNumber::create(123.456, 'px');
        $this->assertEquals(123.456, $number->getValue());
        $this->assertTrue($number->hasUnit('px'));
    }

    public function testWithUnitsCanCreateAUnitlessNumber()
    {
        $number = SassNumber::withUnits(123.456);
        $this->assertEquals(123.456, $number->getValue());
        $this->assertFalse($number->hasUnits());
    }

    public function testWithUnitsCanCreateANumberWithUnits()
    {
        $number = SassNumber::withUnits(123.456, ['px', 'em'], ['ms', 'kHz']);
        $this->assertEquals(123.456, $number->getValue());
        $this->assertEquals(['px', 'em'], $number->getNumeratorUnits());
        $this->assertEquals(['ms', 'kHz'], $number->getDenominatorUnits());
    }
}
