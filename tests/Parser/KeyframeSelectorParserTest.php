<?php

namespace ScssPhp\ScssPhp\Tests\Parser;

use ScssPhp\ScssPhp\Parser\KeyframeSelectorParser;
use PHPUnit\Framework\TestCase;

class KeyframeSelectorParserTest extends TestCase
{
    /**
     * @dataProvider provideSelectors
     */
    public function testParse(array $expected, string $selector)
    {
        $this->assertEquals($expected, (new KeyframeSelectorParser($selector))->parse());
    }

    public static function provideSelectors(): iterable
    {
        yield [['from'], 'from'];
        yield [['from', '5%', 'to'], 'from, 5% ,to'];
        yield [['3.5%'], '3.5%'];
        yield [['+2.5%'], '+2.5%'];
        yield [['+2.5e5%'], '+2.5e5%'];
        yield [['+2.5e5%'], '+2.5E5%'];
        yield [['2.5e+5%'], '2.5E+5%'];
        yield [['2.5e-5%'], '2.5e-5%'];
    }
}
