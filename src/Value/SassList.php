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

namespace ScssPhp\ScssPhp\Value;

use ScssPhp\ScssPhp\Exception\SassScriptException;

class SassList extends Value
{
    /**
     * @var Value[]
     * @phpstan-var list<Value>
     */
    private $contents;

    /**
     * @var string
     * @phpstan-var ListSeparator::*
     */
    private $separator;

    /**
     * @var bool
     */
    private $brackets;

    /**
     * @param string $separator
     * @param bool   $brackets
     *
     * @return SassList
     *
     * @phpstan-param ListSeparator::* $separator
     */
    public static function createEmpty(string $separator = ListSeparator::UNDECIDED, bool $brackets = false): SassList
    {
        return new self(array(), $separator, $brackets);
    }

    /**
     * @param Value[] $contents
     * @param string  $separator
     * @param bool    $brackets
     *
     * @phpstan-param list<Value> $contents
     * @phpstan-param ListSeparator::* $separator
     */
    public function __construct(array $contents, string $separator, bool $brackets = false)
    {
        if ($separator === ListSeparator::UNDECIDED && count($contents) > 1) {
            throw new \InvalidArgumentException('A list with more than one element must have an explicit separator.');
        }

        $this->contents = $contents;
        $this->separator = $separator;
        $this->brackets = $brackets;
    }

    public function getListSeparator(): string
    {
        return $this->separator;
    }

    public function hasBrackets(): bool
    {
        return $this->brackets;
    }

    public function asList(): array
    {
        return $this->contents;
    }

    public function isBlank(): bool
    {
        foreach ($this->contents as $element) {
            if (!$element->isBlank()) {
                return false;
            }
        }

        return true;
    }

    public function assertMap(?string $name = null): SassMap
    {
        if (\count($this->contents) === 0) {
            return SassMap::createEmpty();
        }

        return parent::assertMap($name);
    }

    public function tryMap(): ?SassMap
    {
        if (\count($this->contents) === 0) {
            return SassMap::createEmpty();
        }

        return null;
    }

    public function equals(Value $other): bool
    {
        if ($other instanceof SassMap) {
            return \count($this->contents) === 0 && \count($other->asList()) === 0;
        }

        if (!$other instanceof SassList) {
            return false;
        }

        if ($this->separator !== $other->separator || $this->brackets !== $other->brackets) {
            return false;
        }

        $otherContent = $other->contents;
        $length = \count($this->contents);

        if ($length !== \count($otherContent)) {
            return false;
        }

        for ($i = 0; $i < $length; ++$i) {
            if (!$this->contents[$i]->equals($otherContent[$i])) {
                return false;
            }
        }

        return true;
    }

    public function toCssString(): string
    {
        if (!$this->brackets && count($this->contents) === 0) {
            throw new SassScriptException("() is not a valid CSS value.");
        }

        $output = '';

        if ($this->brackets) {
            $output .= '[';
        }

        switch ($this->separator) {
            case ListSeparator::SPACE:
                $separator = ' ';
                break;

            default:
                $separator = ', ';
        }

        $isFirst = true;

        foreach ($this->contents as $element) {
            if ($element->isBlank()) {
                continue;
            }

            if ($isFirst) {
                $isFirst = false;
            } else {
                $output .= $separator;
            }

            $output .= $element->toCssString();
        }

        if ($this->brackets) {
            $output .= ']';
        }

        return $output;
    }

    public function __toString(): string
    {
        $output = '';

        if ($this->brackets) {
            $output .= '[';
        } elseif (count($this->contents) === 0) {
            return '()';
        }

        $singleton = count($this->contents) === 1 && $this->separator === ListSeparator::COMMA;

        if ($singleton && !$this->brackets) {
            $output .= '(';
        }

        switch ($this->separator) {
            case ListSeparator::SPACE:
                $separator = ' ';
                break;

            default:
                $separator = ', ';
        }

        $isFirst = true;

        foreach ($this->contents as $element) {
            if ($isFirst) {
                $isFirst = false;
            } else {
                $output .= $separator;
            }

            $needsParens = self::elementNeedsParens($this->separator, $element);

            if ($needsParens) {
                $output .= '(';
            }

            $output .= $element->toCssString();

            if ($needsParens) {
                $output .= ')';
            }
        }

        if ($singleton) {
            $output .= ',';

            if (!$this->brackets) {
                $output .= ')';
            }
        }

        if ($this->brackets) {
            $output .= ']';
        }

        return $output;
    }

    /**
     * Returns whether the value needs parentheses as an element in a list with the given separator.
     *
     * @param string $separator
     * @param Value $value
     *
     * @return bool
     *
     * @phpstan-param ListSeparator::* $separator
     */
    private static function elementNeedsParens(string $separator, Value $value): bool
    {
        if (!$value instanceof self) {
            return false;
        }

        if (count($value->contents) < 2) {
            return false;
        }

        if ($value->brackets) {
            return false;
        }

        if ($separator === ListSeparator::COMMA) {
            return $value->separator === ListSeparator::COMMA;
        }

        return $value->separator !== ListSeparator::UNDECIDED;
    }
}
