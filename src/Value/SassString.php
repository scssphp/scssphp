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

use ScssPhp\ScssPhp\Util\SerializationUtil;

final class SassString extends Value
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var bool
     */
    private $quotes;

    public function __construct(string $text, bool $quotes = true)
    {
        $this->text = $text;
        $this->quotes = $quotes;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function hasQuotes(): bool
    {
        return $this->quotes;
    }

    public function assertString(?string $name = null): SassString
    {
        return $this;
    }

    public function isBlank(): bool
    {
        return !$this->quotes && $this->text === '';
    }

    public function isSpecialNumber(): bool
    {
        if ($this->quotes) {
            return false;
        }

        if (\strlen($this->text) < \strlen('min(_)')) {
            return false;
        }

        $first = $this->text[0];

        if ($first === 'c' || $first === 'C') {
            $second = $this->text[1];

            if ($second === 'l' || $second === 'L') {
                return ($this->text[2] === 'a' || $this->text[2] === 'A')
                    && ($this->text[3] === 'm' || $this->text[3] === 'M')
                    && ($this->text[4] === 'p' || $this->text[4] === 'P')
                    && $this->text[5] === '(';
            }

            if ($second === 'a' || $second === 'A') {
                return ($this->text[2] === 'l' || $this->text[2] === 'L')
                    && ($this->text[3] === 'c' || $this->text[3] === 'C')
                    && $this->text[4] === '(';
            }

            return false;
        }

        if ($first === 'v' || $first === 'V') {
            return ($this->text[1] === 'a' || $this->text[1] === 'A')
                && ($this->text[2] === 'r' || $this->text[2] === 'R')
                && $this->text[3] === '(';
        }

        if ($first === 'e' || $first === 'E') {
            return ($this->text[1] === 'n' || $this->text[1] === 'N')
                && ($this->text[2] === 'v' || $this->text[2] === 'V')
                && $this->text[3] === '(';
        }

        if ($first === 'm' || $first === 'M') {
            $second = $this->text[1];

            if ($second === 'a' || $second === 'A') {
                return ($this->text[2] === 'x' || $this->text[2] === 'X')
                    && $this->text[3] === '(';
            }

            if ($second === 'i' || $second === 'I') {
                return ($this->text[2] === 'n' || $this->text[2] === 'N')
                    && $this->text[3] === '(';
            }

            return false;
        }

        return false;
    }

    public function isVar(): bool
    {
        if ($this->quotes) {
            return false;
        }

        if (\strlen($this->text) < \strlen('var(--_)')) {
            return false;
        }

        return ($this->text[0] === 'v' || $this->text[0] === 'V')
            && ($this->text[1] === 'a' || $this->text[1] === 'A')
            && ($this->text[2] === 'r' || $this->text[2] === 'R')
            && $this->text[3] === '(';
    }

    public function equals(Value $other): bool
    {
        return $other instanceof SassString && $this->text === $other->text;
    }

    /**
     * @inheritDoc
     *
     * @param bool $quote Whether to render quotes for quoted string. This parameter is internal.
     */
    public function toCssString(bool $quote = true): string
    {
        if ($quote && $this->quotes) {
            return SerializationUtil::serializeQuotedString($this->text);
        }

        return SerializationUtil::serializeUnquotedString($this->text);
    }

    public function __toString(): string
    {
        return $this->toCssString();
    }
}
