<?php

namespace ScssPhp\ScssPhp\Tests\Util;

use ScssPhp\ScssPhp\Util\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    /**
     * @dataProvider provideAbsoluteCases
     */
    public function testAbsolute($path, $expected)
    {
        $this->assertSame($expected, Path::isAbsolute($path));
    }

    public static function provideAbsoluteCases()
    {
        yield ['', false];
        yield ['.', false];
        yield ['..', false];
        yield ['~', false];
        yield ['/', true];
        yield ['/a', true];
        yield ['a/b', false];
        yield ['\\', \DIRECTORY_SEPARATOR === '\\'];
        yield ['\\\\share\a', \DIRECTORY_SEPARATOR === '\\'];
        yield ['c:\\', \DIRECTORY_SEPARATOR === '\\'];
        yield ['c:\\a', \DIRECTORY_SEPARATOR === '\\'];
        yield ['c:/a', \DIRECTORY_SEPARATOR === '\\'];
        yield ['d:/', \DIRECTORY_SEPARATOR === '\\'];
        yield ['c:', false];
        yield ['cd:/a', false];
        yield ['cd/a', false];
        yield ['cd\\a', false];
    }

    /**
     * @dataProvider provideJoinCases
     */
    public function testJoin($part1, $part2, $expected)
    {
        $this->assertSame($expected, Path::join($part1, $part2));
    }

    public static function provideJoinCases()
    {
        yield ['', '', ''];
        yield ['foo', '', 'foo'];
        yield ['foo/', '', 'foo/'];
        yield ['', 'foo', 'foo'];
        yield ['', 'foo/', 'foo/'];
        yield ['foo', 'bar', 'foo' . \DIRECTORY_SEPARATOR . 'bar'];
        yield ['foo', 'bar/', 'foo' . \DIRECTORY_SEPARATOR . 'bar/'];
        yield ['foo/', 'bar', 'foo/bar'];
        yield ['/foo/', 'bar', '/foo/bar'];
        yield ['/foo/', '/bar', '/bar'];
        yield ['foo', '/bar', '/bar'];
    }
}
