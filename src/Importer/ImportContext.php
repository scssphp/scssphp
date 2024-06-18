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

namespace ScssPhp\ScssPhp\Importer;

use League\Uri\Contracts\UriInterface;

/**
 * @internal
 */
final class ImportContext
{
    private static bool $fromImport = false;

    private static bool $hasContainingUrl = false;

    private static ?UriInterface $containingUrl;

    /**
     * Whether the Sass compiler is currently evaluating an `@import` rule.
     * ///
     * /// When evaluating `@import` rules, URLs should canonicalize to an import-only
     * /// file if one exists for the URL being canonicalized. Otherwise,
     * /// canonicalization should be identical for `@import` and `@use` rules. It's
     * /// admittedly hacky to set this globally, but `@import` will eventually be
     * /// removed, at which point we can delete this and have one consistent behavior.
     */
    public static function isFromImport(): bool
    {
        return self::$fromImport;
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     * @return T
     */
    public static function inImportRule(callable $callback)
    {
        $oldFromImport = self::$fromImport;
        self::$fromImport = true;

        try {
            return $callback();
        } finally {
            self::$fromImport = $oldFromImport;
        }
    }

    public static function getContainingUrl(): ?UriInterface
    {
        if (!self::$hasContainingUrl) {
            throw new \LogicException('containingUrl may only be accessed within a call to canonicalize().');
        }

        return self::$containingUrl;
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     * @return T
     */
    public static function withContainingUrl(?UriInterface $url, callable $callback)
    {
        $oldContainingUrl = self::$containingUrl;
        $oldHasContainingUrl = self::$hasContainingUrl;

        self::$containingUrl = $url;
        self::$hasContainingUrl = true;

        try {
            return $callback();
        } finally {
            self::$containingUrl = $oldContainingUrl;
            self::$hasContainingUrl = $oldHasContainingUrl;
        }
    }
}
