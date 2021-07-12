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

use ScssPhp\ScssPhp\Util\NumberUtil;

/**
 * A specialized subclass of {@see SassNumber} for numbers that have exactly one numerator unit.
 *
 * @internal
 */
final class SingleUnitSassNumber extends SassNumber
{
    /**
     * @var string
     */
    private $unit;

    /**
     * @param int|float  $value
     * @param string     $unit
     * @param array|null $asSlash
     *
     * @phpstan-param array{0: SassNumber, 1: SassNumber}|null $asSlash
     */
    public function __construct($value, string $unit, array $asSlash = null)
    {
        parent::__construct($value, $asSlash);
        $this->unit = $unit;
    }

    public function getNumeratorUnits(): array
    {
        return [$this->unit];
    }

    public function getDenominatorUnits(): array
    {
        return [];
    }

    protected function withValue($value): SassNumber
    {
        return new self($value, $this->unit);
    }

    public function withSlash(SassNumber $numerator, SassNumber $denominator): SassNumber
    {
        return new self($this->getValue(), $this->unit, array($numerator, $denominator));
    }

    public function hasUnits(): bool
    {
        return true;
    }

    public function hasUnit(string $unit): bool
    {
        return $unit === $this->unit;
    }

    public function compatibleWithUnit(string $unit): bool
    {
        return self::getConversionFactor($this->unit, $unit) !== null;
    }

    public function coerceToMatch(SassNumber $other, ?string $name = null, ?string $otherName = null): SassNumber
    {
        return $this->convertToMatch($other, $name, $otherName);
    }

    public function coerceValueToMatch(SassNumber $other, ?string $name = null, ?string $otherName = null)
    {
        return $this->convertValueToMatch($other, $name, $otherName);
    }

    public function convertToMatch(SassNumber $other, ?string $name = null, ?string $otherName = null): SassNumber
    {
        if ($other instanceof SingleUnitSassNumber) {
            $coerced = $this->tryCoerceToUnit($other->unit);

            if ($coerced !== null) {
                return $coerced;
            }
        }

        // Call the parent to generate a consistent error message.
        return parent::convertToMatch($other, $name, $otherName);
    }

    public function convertValueToMatch(SassNumber $other, ?string $name = null, ?string $otherName = null)
    {
        if ($other instanceof SingleUnitSassNumber) {
            $coerced = $this->tryCoerceValueToUnit($other->unit);

            if ($coerced !== null) {
                return $coerced;
            }
        }

        // Call the parent to generate a consistent error message.
        return parent::convertValueToMatch($other, $name, $otherName);
    }

    public function coerce(array $newNumeratorUnits, array $newDenominatorUnits, ?string $name = null): SassNumber
    {
        if (\count($newNumeratorUnits) === 1 && \count($newDenominatorUnits) === 0) {
            $coerced = $this->tryCoerceToUnit($newNumeratorUnits[0]);

            if ($coerced !== null) {
                return $coerced;
            }
        }

        // Call the parent to generate a consistent error message.
        return parent::coerce($newNumeratorUnits, $newDenominatorUnits, $name);
    }

    public function coerceValue(array $newNumeratorUnits, array $newDenominatorUnits, ?string $name = null)
    {
        if (\count($newNumeratorUnits) === 1 && \count($newDenominatorUnits) === 0) {
            $coerced = $this->tryCoerceValueToUnit($newNumeratorUnits[0]);

            if ($coerced !== null) {
                return $coerced;
            }
        }

        // Call the parent to generate a consistent error message.
        return parent::coerceValue($newNumeratorUnits, $newDenominatorUnits, $name);
    }

    public function coerceValueToUnit(string $unit, ?string $name = null)
    {
        $coerced = $this->tryCoerceValueToUnit($unit);

        if ($coerced !== null) {
            return $coerced;
        }

        // Call the parent to generate a consistent error message.
        return parent::coerceValueToUnit($unit, $name);
    }

    public function unaryMinus(): Value
    {
        return new self(-$this->getValue(), $this->unit);
    }

    public function equals(Value $other): bool
    {
        if ($other instanceof SingleUnitSassNumber) {
            $factor = self::getConversionFactor($other->unit, $this->unit);

            return $factor !== null && NumberUtil::fuzzyEquals($this->getValue() * $factor, $other->getValue());
        }

        return false;
    }

    /**
     * @param string $unit
     *
     * @return SassNumber|null
     */
    private function tryCoerceToUnit(string $unit): ?SassNumber
    {
        if ($unit === $this->unit) {
            return $this;
        }

        $factor = self::getConversionFactor($unit, $this->unit);

        if ($factor === null) {
            return null;
        }

        return new SingleUnitSassNumber($this->getValue() * $factor, $unit);
    }

    /**
     * @param string $unit
     *
     * @return float|int|null
     */
    private function tryCoerceValueToUnit(string $unit)
    {
        $factor = self::getConversionFactor($unit, $this->unit);

        if ($factor === null) {
            return null;
        }

        return $this->getValue() * $factor;
    }
}
