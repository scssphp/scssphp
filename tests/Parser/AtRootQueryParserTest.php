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

use ScssPhp\ScssPhp\Ast\Sass\AtRootQuery;
use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Exception\SassFormatException;

class AtRootQueryParserTest extends TestCase
{
    public function testWithSingle()
    {
        $query = AtRootQuery::parse('(with: a)');

        $this->assertTrue($query->getInclude());
        $this->assertEquals(['a'], $query->getNames());
    }

    public function testWithMultiple()
    {
        $query = AtRootQuery::parse('(with: a b c)');

        $this->assertTrue($query->getInclude());
        $this->assertEquals(['a', 'b', 'c'], $query->getNames());
    }

    public function testWithoutSingle()
    {
        $query = AtRootQuery::parse('(without: a)');

        $this->assertFalse($query->getInclude());
        $this->assertEquals(['a'], $query->getNames());
    }

    /**
     * @dataProvider provideInvalidQueries
     */
    public function testInvalidQuery(string $query)
    {
        $this->expectException(SassFormatException::class);

        AtRootQuery::parse($query);
    }

    public static function provideInvalidQueries(): iterable
    {
        yield ['with: a'];
        yield ['(with: a'];
        yield ['(wit: a)'];
        yield ['(with a)'];
        yield ['(with: 2)'];
    }
}
