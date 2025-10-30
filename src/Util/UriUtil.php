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
        if ($url->getScheme() !== null) {
            return $url;
        }

        if ($url->getScheme() === null && $url->getAuthority() === null && $url->getPath() !== '' && !str_starts_with($url->getPath(), '/')) {
            if ($baseUrl->getScheme() === null && $baseUrl->getAuthority() === null && !str_starts_with($url->getAuthority(), '/')) {
                // TODO
            }
        }

        $resolvedUri = BaseUri::from($baseUrl)->resolve($url)->getUri();

        \assert($resolvedUri instanceof UriInterface);

        return $resolvedUri;
    }
}
