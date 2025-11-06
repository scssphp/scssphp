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

namespace ScssPhp\ScssPhp\Util;

use League\Uri\BaseUri;
use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;

/**
 * @internal
 */
final class UriUtil
{
    public static function resolve(UriInterface $baseUrl, string $reference): UriInterface
    {
        return self::resolveUri($baseUrl, Uri::new($reference));
    }

    public static function resolveUri(UriInterface $baseUrl, UriInterface $url): UriInterface
    {
        // Relative path reference handling
        if ($url->getScheme() === null && $url->getAuthority() === null && $url->getPath() !== '' && $url->getPath()[0] !== '/') {
            // non-RFC3986 behavior in Dart-Sass when resolving relative reference with a base url with no authority and a relative path (where they consider the base path as absolute)
            if ($baseUrl->getScheme() !== null && $baseUrl->getAuthority() === null && $baseUrl->getPath() !== '' && $baseUrl->getPath()[0] !== '/') {
                return self::resolveLeagueUri($baseUrl->withPath('/' . $baseUrl->getPath()), $url);
            }

            // Pure path resolution between 2 relative path URLs
            if ($baseUrl->getScheme() === null && $baseUrl->getAuthority() === null && $baseUrl->getPath() !== '' && $baseUrl->getPath()[0] !== '/') {
                $mergedPath = self::normalizeRelativePath(self::mergePaths($baseUrl->getPath(), $url->getPath()));

                return $url->withPath($mergedPath);
            }
        }

        return self::resolveLeagueUri($baseUrl, $url);
    }

    private static function resolveLeagueUri(UriInterface $baseUrl, UriInterface $url): UriInterface
    {
        $resolvedUri = BaseUri::from($baseUrl)->resolve($url)->getUri();

        \assert($resolvedUri instanceof UriInterface);

        return $resolvedUri;
    }

    /**
     * @param non-empty-string $base
     * @param non-empty-string $reference
     *
     * @return non-empty-string
     */
    private static function mergePaths(string $base, string $reference): string
    {
        \assert($reference[0] !== '/');

        $baseEnd = strrpos($base, '/');

        if ($baseEnd === false) {
            return $reference;
        }

        return substr($base, 0, $baseEnd + 1) . $reference;
    }

    /**
     * Removes all `.` segments and any non-leading `..` segments.
     *
     * Removing the ".." from a "bar/foo/.." sequence results in "bar/"
     * (trailing "/"). If the entire path is removed (because it contains as
     * many ".." segments as real segments), the result is "./".
     * This is different from an empty string, which represents "no path"
     * when you resolve it against a base URI with a path with a non-empty
     * final segment.
     *
     * @param non-empty-string $path
     */
    private static function normalizeRelativePath(string $path): string
    {
        \assert($path[0] !== '/');

        if ($path[0] !== '.' && !str_contains($path, '/.')) {
            return $path;
        }

        $output = [];
        $appendSlash = false;

        foreach (explode('/', $path) as $segment) {
            $appendSlash = false;

            if ('..' === $segment) {
                if ($output !== [] && ListUtil::last($output) !== '..') {
                    array_pop($output);
                    $appendSlash = true;
                } else {
                    $output[] = '..';
                }
            } elseif ('.' === $segment) {
                $appendSlash = true;
            } else {
                $output[] = $segment;
            }
        }

        if ($output === [] || $output === ['']) {
            return './';
        }

        if ($appendSlash || ListUtil::last($output) === '..') {
            $output[] = '';
        }

        return implode('/', $output);
    }
}
