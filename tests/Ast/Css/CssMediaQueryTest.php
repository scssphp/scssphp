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

namespace ScssPhp\ScssPhp\Tests\Ast\Css;

use ScssPhp\ScssPhp\Ast\Css\CssMediaQuery;
use PHPUnit\Framework\TestCase;

class CssMediaQueryTest extends TestCase
{
    /**
     * @dataProvider provideMergedQueries
     */
    public function testMerging($expected, string $first, string $other)
    {
        $firstQuery = self::parseQuery($first);
        $otherQuery = self::parseQuery($other);

        $this->assertEquals($expected, $firstQuery->merge($otherQuery));
    }

    public static function provideMergedQueries(): iterable
    {
        // Those test cases are extracted from sass-spec: spec/non_conformant/scss/media/nesting/*.hrx

        yield [self::parseQuery('screen and (color)'), 'screen', '(color)'];
        yield [self::parseQuery('screen and (color)'), 'screen', 'all and (color)'];
        yield [self::parseQuery('screen and (color)'), '(color)', 'screen'];
        yield [self::parseQuery('screen and (color)'), 'all and (color)', 'screen'];
        // Different features can be intersected.
        yield [self::parseQuery('(max-width: 300px) and (min-width: 200px)'), '(max-width: 300px)', '(min-width: 200px)'];
        yield [self::parseQuery('(max-width: 300px) and (min-width: 200px)'), '(max-width: 300px)', 'all and (min-width: 200px)'];
        yield [self::parseQuery('(max-width: 300px) and (min-width: 200px)'), 'all and (max-width: 300px)', '(min-width: 200px)'];
        yield [self::parseQuery('all and (max-width: 300px) and (min-width: 200px)'), 'all and (max-width: 300px)', 'all and (min-width: 200px)'];
        yield [self::parseQuery('screen and (max-width: 300px) and (min-width: 200px)'), 'screen and (max-width: 300px)', 'screen and (min-width: 200px)'];
        // Unlike `not`, the `only` keyword is preserved through intersection.
        yield [self::parseQuery('only screen and (color)'), 'only screen', '(color)'];
        yield [self::parseQuery('only screen and (color)'), 'only screen', 'all and (color)'];
        // The intersection of `not screen` and `print` is just `print`.
        yield [self::parseQuery('print'), 'not screen', 'print'];
        yield [self::parseQuery('print and (grid)'), 'print and (grid)', 'not screen'];
        // The intersection of `not screen` with `not screen and (color)` is the
        // narrower `not screen and (color)`.
        yield [self::parseQuery('not screen and (color)'), 'not screen', 'not screen and (color)'];
        // The intersection of two different media types is empty, so they're eliminated.
        yield [CssMediaQuery::MERGE_RESULT_EMPTY, 'screen', 'print'];
        // The intersection of `not screen` and `screen` is empty.
        yield [CssMediaQuery::MERGE_RESULT_EMPTY, 'screen', 'not screen'];
        // That's true even if `screen` has features.
        yield [CssMediaQuery::MERGE_RESULT_EMPTY, 'screen and (color)', 'not screen'];
        // No way to represent those non-empty intersections
        yield [CssMediaQuery::MERGE_RESULT_UNREPRESENTABLE, 'not screen', '(color)'];
        yield [CssMediaQuery::MERGE_RESULT_UNREPRESENTABLE, 'not screen', 'all and (color)'];
        yield [CssMediaQuery::MERGE_RESULT_UNREPRESENTABLE, 'not screen and (color)', 'screen'];
        yield [CssMediaQuery::MERGE_RESULT_UNREPRESENTABLE, 'not screen and (color)', 'not screen and (grid)'];
        yield [CssMediaQuery::MERGE_RESULT_UNREPRESENTABLE, 'not screen', 'not print'];
    }

    private static function parseQuery(string $query): CssMediaQuery
    {
        $queries = CssMediaQuery::parseList($query);

        assert(\count($queries) === 1);

        return $queries[0];
    }
}
