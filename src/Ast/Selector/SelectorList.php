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
use ScssPhp\ScssPhp\Logger\LoggerInterface;
use ScssPhp\ScssPhp\Parser\SelectorParser;
use ScssPhp\ScssPhp\Util\EquatableUtil;
use ScssPhp\ScssPhp\Value\ListSeparator;
use ScssPhp\ScssPhp\Value\SassList;
use ScssPhp\ScssPhp\Value\SassString;
use ScssPhp\ScssPhp\Visitor\SelectorVisitor;

/**
 * A selector list.
 *
 * A selector list is composed of {@see ComplexSelector}s. It matches an element
 * that matches any of the component selectors.
 */
final class SelectorList extends Selector
{
    /**
     * The components of this selector.
     *
     * This is never empty.
     *
     * @var list<ComplexSelector>
     * @readonly
     */
    private $components;

    /**
     * Parses a selector list from $contents.
     *
     * If passed, $url is the name of the file from which $contents comes.
     * $allowParent and $allowPlaceholder control whether {@see ParentSelector}s or
     * {@see PlaceholderSelector}s are allowed in this selector, respectively.
     *
     * @throws SassFormatException if parsing fails.
     */
    public static function parse(string $contents, ?LoggerInterface $logger = null, ?string $url = null, bool $allowParent = true, bool $allowPlaceholder = true): SelectorList
    {
        return (new SelectorParser($contents, $logger, $url, $allowParent, $allowPlaceholder))->parse();
    }

    /**
     * @param list<ComplexSelector> $components
     */
    public function __construct(array $components)
    {
        if ($components === []) {
            throw new \InvalidArgumentException('components may not be empty.');
        }

        $this->components = $components;
    }

    /**
     * @return list<ComplexSelector>
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    public function isInvisible(): bool
    {
        foreach ($this->components as $component) {
            if (!$component->isInvisible()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns a SassScript list that represents this selector.
     *
     * This has the same format as a list returned by `selector-parse()`.
     */
    public function asSassList(): SassList
    {
        return new SassList(array_map(static function (ComplexSelector $complex) {
            return new SassList(array_map(static function($component) {
                return new SassString((string) $component, false);
            }, $complex->getComponents()), ListSeparator::SPACE);
        }, $this->components), ListSeparator::COMMA);
    }

    public function accept(SelectorVisitor $visitor)
    {
        return $visitor->visitSelectorList($this);
    }

    public function equals(object $other): bool
    {
        return $other instanceof SelectorList && EquatableUtil::listEquals($this->components, $other->components);
    }

    /**
     * Whether this contains a {@see ParentSelector}.
     */
    private function containsParentSelector(): bool
    {
        foreach ($this->components as $component) {
            if (self::complexContainsParentSelector($component)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether $complex contains a {@see ParentSelector}.
     */
    private static function complexContainsParentSelector(ComplexSelector $complex): bool
    {
        foreach ($complex->getComponents() as $component) {
            if (!$component instanceof CompoundSelector) {
                continue;
            }

            foreach ($component->getComponents() as $simple) {
                if ($simple instanceof ParentSelector) {
                    return true;
                }

                if (!$simple instanceof PseudoSelector) {
                    continue;
                }

                $selector = $simple->getSelector();
                if ($selector !== null && $selector->containsParentSelector()) {
                    return true;
                }
            }
        }

        return false;
    }
}
