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

namespace ScssPhp\ScssPhp\Tests\SourceSpan;

use League\Uri\Uri;
use ScssPhp\ScssPhp\SourceSpan\SimpleSourceLocation;
use PHPUnit\Framework\TestCase;

class SimpleSourceLocationTest extends TestCase
{
    public function testForConstructorOffsetMayNotBeNegative(): void
    {
        $this->expectException(\OutOfRangeException::class);
        new SimpleSourceLocation(-1);
    }

    public function testForConstructorLineMayNotBeNegative(): void
    {
        $this->expectException(\OutOfRangeException::class);
        new SimpleSourceLocation(0, line: -1);
    }

    public function testForConstructorColumnMayNotBeNegative(): void
    {
        $this->expectException(\OutOfRangeException::class);
        new SimpleSourceLocation(0, column: -1);
    }

    public function testForDistanceSourceUrlsMustMatch(): void
    {
        $location = new SimpleSourceLocation(15, line: 2, column: 6, sourceUrl: Uri::new('foo.dart'));

        $this->expectException(\InvalidArgumentException::class);
        $location->distance(new SimpleSourceLocation(0));
    }

    public function testForCompareToSourceUrlsMustMatch(): void
    {
        $location = new SimpleSourceLocation(15, line: 2, column: 6, sourceUrl: Uri::new('foo.dart'));

        $this->expectException(\InvalidArgumentException::class);
        $location->compareTo(new SimpleSourceLocation(0));
    }

    public function testDistanceReturnsTheAbsoluteDistanceBetweenLocations(): void
    {
        $location = new SimpleSourceLocation(15, line: 2, column: 6, sourceUrl: Uri::new('foo.dart'));
        $other = new SimpleSourceLocation(20, sourceUrl: $location->getSourceUrl());

        self::assertEquals(5, $location->distance($other));
        self::assertEquals(5, $other->distance($location));
    }

    public function testPointSpanReturnsAnEmptySpanAtLocation(): void
    {
        $location = new SimpleSourceLocation(15, line: 2, column: 6, sourceUrl: Uri::new('foo.dart'));

        $span = $location->pointSpan();

        self::assertEquals($location, $span->getStart());
        self::assertEquals($location, $span->getEnd());
        self::assertEmpty($span->getText());
    }

    public function testCompareToSortsByOffset(): void
    {
        $location = new SimpleSourceLocation(15, line: 2, column: 6, sourceUrl: Uri::new('foo.dart'));

        $other = new SimpleSourceLocation(20, $location->getSourceUrl());

        self::assertLessThan(0, $location->compareTo($other));
        self::assertGreaterThan(0, $other->compareTo($location));
    }

    public function testCompareToConsidersEqualLocationsEqual(): void
    {
        $location = new SimpleSourceLocation(15, line: 2, column: 6, sourceUrl: Uri::new('foo.dart'));

        self::assertEquals(0, $location->compareTo($location));
    }
}
