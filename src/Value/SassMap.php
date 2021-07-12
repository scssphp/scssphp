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

use ScssPhp\ScssPhp\Collection\Map;
use ScssPhp\ScssPhp\Exception\SassScriptException;

final class SassMap extends Value
{
    /**
     * @phpstan-var Map<Value>
     */
    private $contents;

    /**
     * @phpstan-param Map<Value> $contents
     */
    private function __construct(Map $contents)
    {
        $this->contents = Map::unmodifiable($contents);
    }

    /**
     * @return SassMap
     */
    public static function createEmpty(): SassMap
    {
        return new self(new Map());
    }

    /**
     * @phpstan-param Map<Value> $contents
     */
    public static function create(Map $contents): SassMap
    {
        return new self($contents);
    }

    /**
     * The returned Map is unmodifiable.
     *
     * @return Map
     *
     * @phpstan-return Map<Value>
     */
    public function getContents(): Map
    {
        return $this->contents;
    }

    public function getListSeparator(): string
    {
        return count($this->contents) === 0 ? ListSeparator::UNDECIDED : ListSeparator::COMMA;
    }

    public function asList(): array
    {
        $result = [];

        foreach ($this->contents as $key => $value) {
            $result[] = new SassList([$key, $value], ListSeparator::SPACE);
        }

        return $result;
    }

    public function assertMap(?string $name = null): SassMap
    {
        return $this;
    }

    public function tryMap(): ?SassMap
    {
        return $this;
    }

    public function equals(Value $other): bool
    {
        if ($other instanceof SassList) {
            return \count($this->contents) === 0 && \count($other->asList()) === 0;
        }

        if (!$other instanceof SassMap) {
            return false;
        }

        if ($this->contents === $other->contents) {
            return true;
        }

        if (\count($this->contents) !== \count($other->contents)) {
            return false;
        }

        foreach ($this->contents as $key => $value) {
            $otherValue = $other->contents->get($key);

            if ($otherValue === null) {
                return false;
            }

            if (!$value->equals($otherValue)) {
                return false;
            }
        }

        return true;
    }

    public function toCssString(): string
    {
        throw new SassScriptException("$this is not a valid CSS value.");
    }

    public function __toString(): string
    {
        $output = '(';

        foreach ($this->contents as $key => $value) {
            $output .= self::writeMapElement($key);
            $output .= ': ';
            $output .= self::writeMapElement($value);
        }

        $output .= ')';

        return $output;
    }

    /**
     * @param Value $value
     *
     * @return string
     */
    private function writeMapElement(Value $value): string
    {
        $needsParens = $value instanceof SassList
            && ListSeparator::COMMA === $value->getListSeparator()
            && !$value->hasBrackets();

        $output = '';

        if ($needsParens) {
            $output .= '(';
        }

        $output .= $value;

        if ($needsParens) {
            $output .= ')';
        }

        return $output;
    }
}
