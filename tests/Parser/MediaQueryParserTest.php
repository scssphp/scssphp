<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Tests\Parser;

use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Ast\Css\CssMediaQuery;

class MediaQueryParserTest extends TestCase
{
    public function testParsesOnlyType()
    {
        $this->assertEquals(
            [CssMediaQuery::type('screen')],
            CssMediaQuery::parseList('screen')
        );
    }

    public function testParsesTypeAndModifier()
    {
        $this->assertEquals(
            [CssMediaQuery::type('screen', 'only')],
            CssMediaQuery::parseList('only screen')
        );
    }

    public function testParsesTypeAndFeature()
    {
        $this->assertEquals(
            [CssMediaQuery::type('screen', null, ['( a: b )'])],
            CssMediaQuery::parseList('screen and ( a: b )')
        );
    }

    public function testParsesTypeAndModifierAndFeature()
    {
        $this->assertEquals(
            [CssMediaQuery::type('screen', 'not', ['( a: b )'])],
            CssMediaQuery::parseList('not screen and ( a: b )')
        );
    }

    public function testParsesOnlyFeature()
    {
        $this->assertEquals(
            [CssMediaQuery::condition(['(a: b)'])],
            CssMediaQuery::parseList('(a: b)')
        );
    }

    public function testParsesMultipleFeatures()
    {
        $this->assertEquals(
            [CssMediaQuery::condition(['(a: b)', '(c: d)'], true)],
            CssMediaQuery::parseList('(a: b) and (c: d)')
        );
    }

    public function testParsesMultipleQueries()
    {
        $this->assertEquals(
            [CssMediaQuery::type('print'), CssMediaQuery::condition(['(a: b)', '(c: d)'], true)],
            CssMediaQuery::parseList('print, (a: b) and (c: d)')
        );
    }
}
