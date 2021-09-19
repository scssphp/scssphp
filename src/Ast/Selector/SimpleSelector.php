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

namespace ScssPhp\ScssPhp\Ast\Selector;

use ScssPhp\ScssPhp\Exception\SassFormatException;
use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Logger\LoggerInterface;
use ScssPhp\ScssPhp\Parser\SelectorParser;

/**
 * An abstract superclass for simple selectors.
 */
abstract class SimpleSelector extends Selector
{
    /**
     * Parses a simple selector from $contents.
     *
     * If passed, $url is the name of the file from which $contents comes.
     * $allowParent controls whether a {@see ParentSelector} is allowed in this
     * selector.
     *
     * @throws SassFormatException if parsing fails.
     */
    public static function parse(string $contents, ?LoggerInterface $logger = null, ?string $url = null, bool $allowParent = true): SimpleSelector
    {
        return (new SelectorParser($contents, $logger, $url, $allowParent))->parseSimpleSelector();
    }

    /**
     * The minimum possible specificity that this selector can have.
     *
     * Pseudo selectors that contain selectors, like `:not()` and `:matches()`,
     * can have a range of possible specificities.
     *
     * Specificity is represented in base 1000. The spec says this should be
     * "sufficiently high"; it's extremely unlikely that any single selector
     * sequence will contain 1000 simple selectors.
     */
    public function getMinSpecificity(): int
    {
        return 1000;
    }

    /**
     * The maximum possible specificity that this selector can have.
     *
     * Pseudo selectors that contain selectors, like `:not()` and `:matches()`,
     * can have a range of possible specificities.
     */
    public function getMaxSpecificity(): int
    {
        return $this->getMinSpecificity();
    }

    /**
     * Returns a new {@see SimpleSelector} based on $this, as though it had been
     * written with $suffix at the end.
     *
     * Assumes $suffix is a valid identifier suffix. If this wouldn't produce a
     * valid SimpleSelector, throws a {@see SassScriptException}.
     *
     * @throws SassScriptException
     */
    public function addSuffix(string $suffix): SimpleSelector
    {
        throw new SassScriptException("Invalid parent selector \"$this\"");
    }
}
