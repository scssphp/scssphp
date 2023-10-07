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
use ScssPhp\ScssPhp\Ast\Sass\Statement\Stylesheet;
use ScssPhp\ScssPhp\Logger\DeprecationAwareLoggerInterface;
use ScssPhp\ScssPhp\Logger\QuietLogger;
use ScssPhp\ScssPhp\Util\UriUtil;

/**
 * An in-memory cache of parsed stylesheets that have been imported by Sass.
 *
 * @internal
 */
final class ImportCache
{
    /**
     * @var list<Importer>
     */
    private readonly array $importers;

    private readonly DeprecationAwareLoggerInterface $logger;

    /**
     * The canonicalized URLs for each non-canonical URL.
     *
     * The `forImport` in each key is true when this canonicalization is for an
     * `@import` rule. Otherwise, it's for a `@use` or `@forward` rule.
     *
     * This cache isn't used for relative imports, because they depend on the
     * specific base importer. That's stored separately in
     * {@see $relativeCanonicalizeCache}.
     *
     * @var array<string, array<0|1, CanonicalizeResult|SpecialCacheValue>>
     */
    private array $canonicalizeCache = [];

    /**
     * @var array<string, array<0|1, array<string, \SplObjectStorage<Importer, CanonicalizeResult|SpecialCacheValue>>>>
     */
    private array $relativeCanonicalizeCache = [];

    /**
     * The parsed stylesheets for each canonicalized import URL.
     *
     * @var array<string, Stylesheet|SpecialCacheValue>
     */
    private array $importCache = [];

    /**
     * The import results for each canonicalized import URL.
     *
     * @var array<string, ImporterResult>
     */
    private array $resultsCache = [];

    /**
     * @param list<Importer> $importers
     */
    public function __construct(array $importers, DeprecationAwareLoggerInterface $logger)
    {
        $this->importers = $importers;
        $this->logger = $logger;
    }

    public function canonicalize(UriInterface $url, ?Importer $baseImporter = null, ?UriInterface $baseUrl = null, bool $forImport = false): ?CanonicalizeResult
    {
        $urlCacheKey = (string) $url;
        $forImportCacheKey = (int) $forImport;

        if ($baseImporter !== null && $url->getScheme() === null) {
            $baseUrlCacheKey = (string) $baseUrl;

            $this->relativeCanonicalizeCache[$urlCacheKey][$forImportCacheKey][$baseUrlCacheKey] ??= self::createStorage();

            $relativeResult = $this->relativeCanonicalizeCache[$urlCacheKey][$forImportCacheKey][$baseUrlCacheKey][$baseImporter] ??= $this->doCanonicalize($baseImporter, self::resolveUri($baseUrl, $url), $baseUrl, $forImport) ?? SpecialCacheValue::null;

            if ($relativeResult !== SpecialCacheValue::null) {
                return $relativeResult;
            }
        }

        $cacheResult = $this->canonicalizeCache[$urlCacheKey][$forImportCacheKey] ??= $this->doCanonicalizeWithImporters($url, $baseUrl, $forImport) ?? SpecialCacheValue::null;

        if ($cacheResult !== SpecialCacheValue::null) {
            return $cacheResult;
        }

        return null;
    }

    private static function resolveUri(?UriInterface $baseUrl, UriInterface $url): UriInterface
    {
        if ($baseUrl === null) {
            return $url;
        }

        return UriUtil::resolveUri($baseUrl, $url);
    }

    /**
     * Creates a new storage for the importer-level cache
     *
     * This is in a dedicated method because phpstan cannot infer the generic types from the constructor.
     *
     * @return \SplObjectStorage<Importer, CanonicalizeResult|SpecialCacheValue>
     */
    private static function createStorage(): \SplObjectStorage
    {
        /** @var \SplObjectStorage<Importer, CanonicalizeResult|SpecialCacheValue> $storage */
        $storage = new \SplObjectStorage();
        return $storage;
    }

    private function doCanonicalizeWithImporters(UriInterface $url, ?UriInterface $baseUrl, bool $forImport): ?CanonicalizeResult
    {
        foreach ($this->importers as $importer) {
            $result = $this->doCanonicalize($importer, $url, $baseUrl, $forImport);

            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    private function doCanonicalize(Importer $importer, UriInterface $url, ?UriInterface $baseUrl, bool $forImport): ?CanonicalizeResult
    {
        $canonicalize = $forImport
            ? fn () => ImportContext::inImportRule(fn () => $importer->canonicalize($url))
            : fn () => $importer->canonicalize($url);

        $passContainingUrl = $baseUrl !== null && ($url->getScheme() === null || $importer->isNonCanonicalScheme($url->getScheme()));
        $result = ImportContext::withContainingUrl($passContainingUrl ? $baseUrl : null, $canonicalize);

        if ($result === null) {
            return null;
        }

        if ($result->getScheme() === null) {
            throw new \UnexpectedValueException("Importer $importer canonicalized $url to $result but canonical URLs must be absolute.");
        }

        if ($importer->isNonCanonicalScheme($result->getScheme())) {
            throw new \UnexpectedValueException("Importer $importer canonicalized $url to $result, which uses a scheme declared as non-canonical.");
        }

        return new CanonicalizeResult($importer, $result, $url);
    }

    /**
     * Tries to load the canonicalized $canonicalUrl using $importer.
     *
     * If $importer can import $canonicalUrl, returns the imported {@see Stylesheet}.
     * Otherwise returns `null`.
     *
     * If passed, the $originalUrl represents the URL that was canonicalized
     * into $canonicalUrl. It's used to resolve a relative canonical URL, which
     * importers may return for legacy reasons.
     *
     * If $quiet is `true`, this will disable logging warnings when parsing the
     * newly imported stylesheet.
     *
     * Caches the result of the import and uses cached results if possible.
     */
    public function importCanonical(Importer $importer, UriInterface $canonicalUrl, ?UriInterface $originalUrl = null, bool $quiet = false): ?Stylesheet
    {
        $result = $this->importCache[(string) $canonicalUrl] ??= $this->doImportCanonical($importer, $canonicalUrl, $originalUrl, $quiet) ?? SpecialCacheValue::null;

        if ($result !== SpecialCacheValue::null) {
            return $result;
        }

        return null;
    }

    private function doImportCanonical(Importer $importer, UriInterface $canonicalUrl, ?UriInterface $originalUrl = null, bool $quiet = false): ?Stylesheet
    {
        $result = $importer->load($canonicalUrl);

        if ($result === null) {
            return null;
        }

        $this->resultsCache[(string) $canonicalUrl] = $result;

        return Stylesheet::parse($result->getContents(), $result->getSyntax(), $quiet ? new QuietLogger() : $this->logger, $originalUrl);
    }

    public function humanize(UriInterface $canonicalUrl): UriInterface
    {
        $shortestUrl = null;
        $shortestLength = \PHP_INT_MAX;

        foreach ($this->canonicalizeCache as $cacheValues) {
            foreach ($cacheValues as $cacheValue) {
                if ($cacheValue === SpecialCacheValue::null) {
                    continue;
                }

                if ($cacheValue->canonicalUrl->toString() !== $canonicalUrl->toString()) {
                    continue;
                }

                $originalUrlLength = \strlen($cacheValue->originalUrl->getPath());

                if ($shortestUrl === null || $originalUrlLength < $shortestLength) {
                    $shortestUrl = $cacheValue->originalUrl;
                    $shortestLength = $originalUrlLength;
                }
            }
        }

        if ($shortestUrl !== null) {
            // TODO check if basename is safe to use for the URL context
            return UriUtil::resolve($shortestUrl, basename($canonicalUrl->getPath()));
        }

        return $canonicalUrl;
    }

    public function sourceMapUrl(UriInterface $canonicalUrl): UriInterface
    {
        return ($this->resultsCache[(string) $canonicalUrl] ?? null)?->getSourceMapUrl() ?? $canonicalUrl;
    }
}
