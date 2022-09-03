<?php

namespace ScssPhp\ScssPhp\Tests\Util;

use ScssPhp\ScssPhp\Util\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    /**
     * @dataProvider provideCommonAbsolutePaths
     */
    public function testAbsolutePath(string $path)
    {
        $this->assertTrue(Path::isAbsolute($path));
    }

    /**
     * @dataProvider provideCommonNonAbsolutePaths
     */
    public function testNonAbsolutePath(string $path)
    {
        $this->assertFalse(Path::isAbsolute($path));
    }
    /**
     * @dataProvider provideWindowsOnlyAbsolutePath
     */
    public function testWindowsOnlyAbsolutePath(string $path)
    {
        $this->assertSame(\DIRECTORY_SEPARATOR === '\\', Path::isAbsolute($path));
    }

    /**
     * @dataProvider provideCommonAbsolutePaths
     * @dataProvider provideWindowsOnlyAbsolutePath
     */
    public function testWindowsAbsolutePath(string $path)
    {
        $this->assertTrue(Path::isWindowsAbsolute($path));
    }

    /**
     * @dataProvider provideCommonNonAbsolutePaths
     */
    public function testWindowsNonAbsolutePath(string $path)
    {
        $this->assertFalse(Path::isWindowsAbsolute($path));
    }

    public static function provideCommonAbsolutePaths()
    {
        yield ['/'];
        yield ['/a'];
    }

    public static function provideCommonNonAbsolutePaths()
    {
        yield [''];
        yield ['.'];
        yield ['..'];
        yield ['~'];
        yield ['a/b'];
        yield ['c:'];
        yield ['cd:/a'];
        yield ['c::/a'];
        yield ['cd/a'];
        yield ['cd\\a'];
        yield ['$:/a'];
        yield ['$:\\a'];
    }

    public static function provideWindowsOnlyAbsolutePath()
    {
        yield ['\\'];
        yield ['\\\\share\a'];
        yield ['c:\\'];
        yield ['c:\\a'];
        yield ['c:/a'];
        yield ['d:/'];
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
