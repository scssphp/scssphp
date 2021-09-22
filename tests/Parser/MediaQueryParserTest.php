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
            [new CssMediaQuery('screen')],
            CssMediaQuery::parseList('screen')
        );
    }

    public function testParsesTypeAndModifier()
    {
        $this->assertEquals(
            [new CssMediaQuery('screen', 'only')],
            CssMediaQuery::parseList('only screen')
        );
    }

    public function testParsesTypeAndFeature()
    {
        $this->assertEquals(
            [new CssMediaQuery('screen', null, ['( a: b )'])],
            CssMediaQuery::parseList('screen and ( a: b )')
        );
    }

    public function testParsesTypeAndModifierAndFeature()
    {
        $this->assertEquals(
            [new CssMediaQuery('screen', 'not', ['( a: b )'])],
            CssMediaQuery::parseList('not screen and ( a: b )')
        );
    }

    public function testParsesOnlyFeature()
    {
        $this->assertEquals(
            [new CssMediaQuery(null, null, ['(a: b)'])],
            CssMediaQuery::parseList('(a: b)')
        );
    }

    public function testParsesMultipleFeatures()
    {
        $this->assertEquals(
            [new CssMediaQuery(null, null, ['(a: b)', '(c: d)'])],
            CssMediaQuery::parseList('(a: b) and (c: d)')
        );
    }

    public function testParsesMultipleQueries()
    {
        $this->assertEquals(
            [new CssMediaQuery('print'), new CssMediaQuery(null, null, ['(a: b)', '(c: d)'])],
            CssMediaQuery::parseList('print, (a: b) and (c: d)')
        );
    }
}
