<?php

namespace ScssPhp\ScssPhp\Tests\Util;

use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;
use ScssPhp\ScssPhp\Util\UriUtil;
use PHPUnit\Framework\TestCase;

class UriUtilTest extends TestCase
{
    /**
     * @dataProvider provideResolveUriCases
     */
    public function testResolve(UriInterface $base, string $expected, string $relative): void
    {
        self::assertEquals($expected, UriUtil::resolve($base, $relative)->toString());
    }

    /**
     * @dataProvider provideResolveUriCases
     */
    public function testResolveUri(UriInterface $base, string $expected, string $relative): void
    {
        self::assertEquals($expected, UriUtil::resolve($base, Uri::new($relative))->toString());
    }

    /**
     * Test cases from https://github.com/dart-lang/sdk/blob/eaa7368470e2570bab8c9526803f8f20bdf29906/tests/corelib/uri_test.dart#L80
     *
     * @return iterable<array{UriInterface, string, string}>
     */
    public static function provideResolveUriCases(): iterable
    {
        $base = Uri::new('http://a/b/c/d;p?q');
        // From RFC 3986.
        yield [$base, 'g:h', 'g:h'];
        yield [$base, 'http://a/b/c/g', 'g'];
        yield [$base, 'http://a/b/c/g', './g'];
        yield [$base, 'http://a/b/c/g/', 'g/'];
        yield [$base, 'http://a/g', '/g'];
        yield [$base, 'http://g', '//g'];
        yield [$base, 'http://a/b/c/d;p?y', '?y'];
        yield [$base, 'http://a/b/c/g?y', 'g?y'];
        yield [$base, 'http://a/b/c/d;p?q#s', '#s'];
        yield [$base, 'http://a/b/c/g#s', 'g#s'];
        yield [$base, 'http://a/b/c/g?y#s', 'g?y#s'];
        yield [$base, 'http://a/b/c/;x', ';x'];
        yield [$base, 'http://a/b/c/g;x', 'g;x'];
        yield [$base, 'http://a/b/c/g;x?y#s', 'g;x?y#s'];
        yield [$base, 'http://a/b/c/d;p?q', ''];
        yield [$base, 'http://a/b/c/', '.'];
        yield [$base, 'http://a/b/c/', './'];
        yield [$base, 'http://a/b/', '..'];
        yield [$base, 'http://a/b/', '../'];
        yield [$base, 'http://a/b/g', '../g'];
        yield [$base, 'http://a/', '../..'];
        yield [$base, 'http://a/', '../../'];
        yield [$base, 'http://a/g', '../../g'];
        yield [$base, 'http://a/g', '../../../g'];
        yield [$base, 'http://a/g', '../../../../g'];
        yield [$base, 'http://a/g', '/./g'];
        yield [$base, 'http://a/g', '/../g'];
        yield [$base, 'http://a/b/c/g.', 'g.'];
        yield [$base, 'http://a/b/c/.g', '.g'];
        yield [$base, 'http://a/b/c/g..', 'g..'];
        yield [$base, 'http://a/b/c/..g', '..g'];
        yield [$base, 'http://a/b/g', './../g'];
        yield [$base, 'http://a/b/c/g/', './g/.'];
        yield [$base, 'http://a/b/c/g/h', 'g/./h'];
        yield [$base, 'http://a/b/c/h', 'g/../h'];
        yield [$base, 'http://a/b/c/g;x=1/y', 'g;x=1/./y'];
        yield [$base, 'http://a/b/c/y', 'g;x=1/../y'];
        yield [$base, 'http://a/b/c/g?y/./x', 'g?y/./x'];
        yield [$base, 'http://a/b/c/g?y/../x', 'g?y/../x'];
        yield [$base, 'http://a/b/c/g#s/./x', 'g#s/./x'];
        yield [$base, 'http://a/b/c/g#s/../x', 'g#s/../x'];
        // yield [$base, 'http:g', 'http:g']; // league/uri validates the usage of hierarchical vs non-hierarchical URIs for some known schemes. Replaced by a test using a "foo" scheme below.

        // Additional tests (not from RFC 3986).
        yield [Uri::new('foo://a/b/c/d;p?q'), 'foo:g', 'foo:g'];
        yield [$base, 'http://a/b/g;p/h;s', '../g;p/h;s'];

        // Test non-URI base (no scheme, no authority, relative path).
        $base = Uri::new('a/b/c?_#_');
        yield [$base, 'a/b/g?q#f', 'g?q#f'];
        yield [$base, '../', '../../..'];
        yield [$base, 'a/b/', '.'];
        yield [$base, 'c', '../../c'];

        $base = Uri::new('s:a/b');
        yield [$base, 's:/c', '../c'];
    }
}
