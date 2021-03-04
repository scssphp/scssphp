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

namespace ScssPhp\ScssPhp\Tests\Value\SassString;

use ScssPhp\ScssPhp\Tests\Value\ValueTestCase;
use ScssPhp\ScssPhp\Value\SassString;

/**
 * @testdox A quoted ASCII string
 */
class QuotedAsciiTest extends ValueTestCase
{
    /**
     * @var SassString
     */
    private $value;

    protected function setUp(): void
    {
        $this->value = self::parseValue('"foobar"');
    }

    public function testHasTheCorrectText()
    {
        $this->assertEquals('foobar', $this->value->getText());
    }

    public function testHasNoQuotes()
    {
        $this->assertTrue($this->value->hasQuotes());
    }

    public function testEqualsTheSameString()
    {
        $this->assertSassEquals($this->value, new SassString('foobar', false));
        $this->assertSassEquals($this->value, new SassString('foobar', true));
    }
}
