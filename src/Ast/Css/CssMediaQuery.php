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

namespace ScssPhp\ScssPhp\Ast\Css;

use ScssPhp\ScssPhp\Exception\SassFormatException;
use ScssPhp\ScssPhp\Logger\LoggerInterface;
use ScssPhp\ScssPhp\Parser\MediaQueryParser;

/**
 * A plain CSS media query, as used in `@media` and `@import`.
 *
 * @internal
 */
final class CssMediaQuery
{
    /**
     * The modifier, probably either "not" or "only".
     *
     * This may be `null` if no modifier is in use.
     *
     * @var string|null
     * @readonly
     */
    private $modifier;

    /**
     * The media type, for example "screen" or "print".
     *
     * This may be `null`. If so, {@see $features} will not be empty.
     *
     * @var string|null
     * @readonly
     */
    private $type;

    /**
     * Feature queries, including parentheses.
     *
     * @var string[]
     * @readonly
     */
    private $features;

    /**
     * Parses a media query from $contents.
     *
     * If passed, $url is the name of the file from which $contents comes.
     *
     * @return list<CssMediaQuery>
     *
     * @throws SassFormatException if parsing fails
     */
    public static function parseList(string $contents, ?LoggerInterface $logger = null, ?string $url = null): array
    {
        return (new MediaQueryParser($contents, $logger, $url))->parse();
    }

    /**
     * @param string|null $type
     * @param string|null $modifier
     * @param string[]    $features
     */
    public function __construct(?string $type, ?string $modifier = null, array $features = [])
    {
        $this->modifier = $modifier;
        $this->type = $type;
        $this->features = $features;
    }

    /**
     * Creates a media query that only specifies features.
     *
     * @param string[] $features
     *
     * @return CssMediaQuery
     */
    public static function condition(array $features): CssMediaQuery
    {
        return new CssMediaQuery(null, null, $features);
    }

    public function getModifier(): ?string
    {
        return $this->modifier;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function getFeatures(): array
    {
        return $this->features;
    }

    /**
     * Whether this media query only specifies features.
     */
    public function isCondition(): bool
    {
        return $this->modifier === null && $this->type === null;
    }

    /**
     * Whether this media query matches all media types.
     */
    public function matchesAllTypes(): bool
    {
        return $this->type === null || strtolower($this->type) === 'all';
    }
}
