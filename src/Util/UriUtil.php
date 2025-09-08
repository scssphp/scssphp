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

use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;

/**
 * @internal
 */
final class UriUtil
{
    public static function resolve(UriInterface $baseUrl, string $reference): UriInterface
    {
        $baseUri = Uri::new($baseUrl);

        return !$baseUri->isAbsolute()
            ? Uri::new($reference)
            : $baseUri->resolve($reference);
    }

    public static function resolveUri(UriInterface $baseUrl, UriInterface $url): UriInterface
    {
        $baseUri = Uri::new($baseUrl);

        return !$baseUri->isAbsolute()
            ? $url
            : $baseUri->resolve($url);
    }
}
