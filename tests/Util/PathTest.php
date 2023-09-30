<?php

namespace ScssPhp\ScssPhp\Tests\Util;

use League\Uri\Uri;
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

    /**
     * @dataProvider provideExtensionCases
     */
    public function testExtension(string $path, string $expected): void
    {
        self::assertEquals($expected, Path::extension($path));
    }

    public static function provideExtensionCases(): iterable
    {
        yield ['path/to/foo.dart', '.dart'];
        yield ['path/to/foo', ''];
        yield ['path.to/foo', ''];
        yield ['path/to/foo.dart.js', '.js'];
        yield ['~/.bashrc', ''];
        yield ['~/.notes.txt', '.txt'];
    }

    /**
     * @dataProvider provideWithoutExtensionCases
     */
    public function testWithoutExtension(string $path, string $expected): void
    {
        self::assertEquals($expected, Path::withoutExtension($path));
    }

    public static function provideWithoutExtensionCases(): iterable
    {
        yield ['path/to/foo.dart', 'path/to/foo'];
        yield ['path/to/foo', 'path/to/foo'];
        yield ['path.to/foo', 'path.to/foo'];
        yield ['path/to/foo.dart.js', 'path/to/foo.dart'];
        yield ['~/.bashrc', '~/.bashrc'];
        yield ['~/.notes.txt', '~/.notes'];
    }
}
