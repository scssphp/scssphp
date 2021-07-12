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
use ScssPhp\ScssPhp\Util\NumberUtil;
use ScssPhp\ScssPhp\Util\SerializationUtil;

abstract class SassNumber extends Value
{
    const PRECISION = 10;

    /**
     * @see https://www.w3.org/TR/css-values-3/
     */
    private const CONVERSIONS = [
        'in' => [
            'in' => 1,
            'pc' => 6,
            'pt' => 72,
            'px' => 96,
            'cm' => 2.54,
            'mm' => 25.4,
            'q'  => 101.6,
        ],
        'deg' => [
            'deg'  => 360,
            'grad' => 400,
            'rad'  => 6.28318530717958647692528676, // 2 * M_PI
            'turn' => 1,
        ],
        's' => [
            's'  => 1,
            'ms' => 1000,
        ],
        'Hz' => [
            'Hz'  => 1,
            'kHz' => 0.001,
        ],
        'dpi' => [
            'dpi'  => 1,
            'dpcm' => 1 / 2.54,
            'dppx' => 1 / 96,
        ],
    ];

    /**
     * A map from human-readable names of unit types to the units that fall into those types.
     */
    private const UNITS_BY_TYPE = [
        'length' => ['in', 'cm', 'pc', 'mm', 'q', 'pt', 'px'],
        'angle' => ['deg', 'grad', 'rad', 'turn'],
        'time' => ['s', 'ms'],
        'frequency' => ['Hz', 'kHz'],
        'pixel density' => ['dpi', 'dpcm', 'dppx']
    ];

    /**
     * A map from units to the human-readable names of those unit types.
     */
    private const TYPES_BY_UNIT = [
        'in' => 'length',
        'cm' => 'length',
        'pc' => 'length',
        'mm' => 'length',
        'q' => 'length',
        'pt' => 'length',
        'px' => 'length',
        'deg' => 'angle',
        'grad' => 'angle',
        'rad' => 'angle',
        'turn' => 'angle',
        's' => 'time',
        'ms' => 'time',
        'Hz' => 'frequency',
        'kHz' => 'frequency',
        'dpi' => 'pixel density',
        'dpcm' => 'pixel density',
        'dppx' => 'pixel density',
    ];

    /**
     * @var int|float
     */
    private $value;

    /**
     * The representation of this number as two slash-separated numbers, if it has one.
     *
     * @var SassNumber[]|null
     * @phpstan-var array{0: SassNumber, 1: SassNumber}|null
     */
    private $asSlash;

    /**
     * @param int|float  $value
     * @param array|null $asSlash
     *
     * @phpstan-param array{0: SassNumber, 1: SassNumber}|null $asSlash
     */
    protected function __construct($value, array $asSlash = null)
    {
        $this->value = $value;
        $this->asSlash = $asSlash;
    }

    /**
     * @param int|float   $value
     * @param string|null $unit
     *
     * @return self
     */
    final public static function create($value, ?string $unit = null): SassNumber
    {
        if ($unit === null) {
            return new UnitlessSassNumber($value);
        }

        return new SingleUnitSassNumber($value, $unit);
    }

    /**
     * @param int|float $value
     * @param string[]  $numeratorUnits
     * @param string[]  $denominatorUnits
     *
     * @phpstan-param list<string> $numeratorUnits
     * @phpstan-param list<string> $denominatorUnits
     *
     * @return self
     */
    final public static function withUnits($value, array $numeratorUnits, array $denominatorUnits): SassNumber
    {
        if (empty($numeratorUnits) && empty($denominatorUnits)) {
            return new UnitlessSassNumber($value);
        }

        if (empty($denominatorUnits) && \count($numeratorUnits) === 1) {
            return new SingleUnitSassNumber($value, $numeratorUnits[0]);
        }

        return new ComplexSassNumber($value, $numeratorUnits, $denominatorUnits);
    }

    /**
     * @return float|int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string[]
     * @phpstan-return list<string>
     */
    abstract public function getNumeratorUnits(): array;

    /**
     * @return string[]
     * @phpstan-return list<string>
     */
    abstract public function getDenominatorUnits(): array;

    /**
     * @return array|null
     *
     * @phpstan-return array{0: SassNumber, 1: SassNumber}|null
     *
     * @internal
     */
    final public function getAsSlash(): ?array
    {
        return $this->asSlash;
    }

    /**
     * Returns a SassNumber with this value and the same units.
     *
     * @param int|float $value
     *
     * @return self
     */
    abstract protected function withValue($value): SassNumber;

    /**
     * @param SassNumber $numerator
     * @param SassNumber $denominator
     *
     * @return SassNumber
     *
     * @internal
     */
    abstract public function withSlash(SassNumber $numerator, SassNumber $denominator): SassNumber;

    public function withoutSlash(): SassNumber
    {
        if ($this->asSlash === null) {
            return $this;
        }

        return $this->withValue($this->value);
    }

    public function assertNumber(?string $name = null): SassNumber
    {
        return $this;
    }

    /**
     * Returns true if the number has units.
     *
     * @return boolean
     */
    abstract public function hasUnits(): bool;

    /**
     * Checks whether the number has exactly this unit.
     *
     * @param string $unit
     *
     * @return bool
     */
    abstract public function hasUnit(string $unit): bool;

    /**
     * Checks whether the number is compatible with this unit.
     *
     * @param string $unit
     *
     * @return bool
     */
    abstract public function compatibleWithUnit(string $unit): bool;

    /**
     * @param string|null $varName
     *
     * @return void
     */
    public function assertNoUnits(?string $varName = null): void
    {
        if (!$this->hasUnits()) {
            return;
        }

        throw SassScriptException::forArgument(sprintf('Expected %s to have no units.', $this), $varName);
    }

    /**
     * @param string      $unit
     * @param string|null $varName
     *
     * @return void
     */
    public function assertUnit(string $unit, ?string $varName = null): void
    {
        if ($this->hasUnit($unit)) {
            return;
        }

        throw SassScriptException::forArgument(sprintf('Expected %s to have unit "%s".', $this, $unit), $varName);
    }

    /**
     * Returns a copy of this number, converted to the same units as $other.
     *
     * Unlike {@see convertToMatch}, this does not throw an error if this number is
     * unitless and $other is not, or vice versa. Instead, it treats all unitless
     * numbers as convertible to and from all units without changing the value.
     *
     * Note that {@see coerceValueToMatch} is generally more efficient if the value
     * is going to be accessed directly.
     *
     * @param SassNumber  $other
     * @param string|null $name      The argument name if this is a function argument
     * @param string|null $otherName The argument name for $other if this is a function argument
     *
     * @return SassNumber
     *
     * @throws SassScriptException if the units are not compatible
     */
    public function coerceToMatch(SassNumber $other, ?string $name = null, ?string $otherName = null): SassNumber
    {
        return self::withUnits($this->coerceValueToMatch($other, $name, $otherName), $other->getNumeratorUnits(), $other->getDenominatorUnits());
    }

    /**
     * Returns {@see value}, converted to the same units as $other.
     *
     * Unlike {@see convertValueToMatch}, this does not throw an error if this number
     * is unitless and $other is not, or vice versa. Instead, it treats all unitless
     * numbers as convertible to and from all units without changing the value.
     *
     * @param SassNumber  $other
     * @param string|null $name      The argument name if this is a function argument
     * @param string|null $otherName The argument name for $other if this is a function argument
     *
     * @return int|float
     *
     * @throws SassScriptException if the units are not compatible
     */
    public function coerceValueToMatch(SassNumber $other, ?string $name = null, ?string $otherName = null)
    {
        return $this->convertOrCoerceValue($other->getNumeratorUnits(), $other->getDenominatorUnits(), true, $name, $other, $otherName);
    }

    /**
     * Returns a copy of this number, converted to the same units as $other.
     *
     * Note that {@see convertValueToMatch} is generally more efficient if the value
     * is going to be accessed directly.
     *
     * @param SassNumber  $other
     * @param string|null $name      The argument name if this is a function argument
     * @param string|null $otherName The argument name for $other if this is a function argument
     *
     * @return SassNumber
     *
     * @throws SassScriptException if the units are not compatible or if either number is unitless but the other is not.
     */
    public function convertToMatch(SassNumber $other, ?string $name = null, ?string $otherName = null): SassNumber
    {
        return self::withUnits($this->convertValueToMatch($other, $name, $otherName), $other->getNumeratorUnits(), $other->getDenominatorUnits());
    }

    /**
     * Returns {@see value}, converted to the same units as $other.
     *
     * @param SassNumber  $other
     * @param string|null $name      The argument name if this is a function argument
     * @param string|null $otherName The argument name for $other if this is a function argument
     *
     * @return int|float
     *
     * @throws SassScriptException if the units are not compatible or if either number is unitless but the other is not.
     */
    public function convertValueToMatch(SassNumber $other, ?string $name = null, ?string $otherName = null)
    {
        return $this->convertOrCoerceValue($other->getNumeratorUnits(), $other->getDenominatorUnits(), false, $name, $other, $otherName);
    }

    /**
     * Returns a copy of this number, converted to the units represented by $newNumeratorUnits and $newDenominatorUnits.
     *
     * This does not throw an error if this number is unitless and
     * $newNumeratorUnits/$newDenominatorUnits are not empty, or vice versa. Instead,
     * it treats all unitless numbers as convertible to and from all units without
     * changing the value.
     *
     * Note that {@see coerceValue} is generally more efficient if the value
     * is going to be accessed directly.
     *
     * @param string[] $newNumeratorUnits
     * @param string[] $newDenominatorUnits
     * @param string|null $name      The argument name if this is a function argument
     *
     * @return SassNumber
     *
     * @phpstan-param list<string> $newNumeratorUnits
     * @phpstan-param list<string> $newDenominatorUnits
     *
     * @throws SassScriptException if this number's units are not compatible with $newNumeratorUnits and $newDenominatorUnits
     */
    public function coerce(array $newNumeratorUnits, array $newDenominatorUnits, ?string $name = null): SassNumber
    {
        return self::withUnits($this->coerceValue($newNumeratorUnits, $newDenominatorUnits, $name), $newNumeratorUnits, $newDenominatorUnits);
    }

    /**
     * Returns {@see value}, converted to the units represented by $newNumeratorUnits and $newDenominatorUnits.
     *
     * This does not throw an error if this number is unitless and
     * $newNumeratorUnits/$newDenominatorUnits are not empty, or vice versa. Instead,
     * it treats all unitless numbers as convertible to and from all units without
     * changing the value.
     *
     * @param string[] $newNumeratorUnits
     * @param string[] $newDenominatorUnits
     * @param string|null $name      The argument name if this is a function argument
     *
     * @return int|float
     *
     * @phpstan-param list<string> $newNumeratorUnits
     * @phpstan-param list<string> $newDenominatorUnits
     *
     * @throws SassScriptException if this number's units are not compatible with $newNumeratorUnits and $newDenominatorUnits
     */
    public function coerceValue(array $newNumeratorUnits, array $newDenominatorUnits, ?string $name = null)
    {
        return $this->convertOrCoerceValue($newNumeratorUnits, $newDenominatorUnits, true, $name);
    }

    /**
     * A shorthand for {@see coerceValue} with a single unit
     *
     * @param string      $unit
     * @param string|null $name      The argument name if this is a function argument
     *
     * @return int|float
     */
    public function coerceValueToUnit(string $unit, ?string $name = null)
    {
        return $this->coerceValue([$unit], [], $name);
    }

    /**
     * @param SassNumber $other
     *
     * @return bool
     *
     * @internal
     */
    public function isComparableTo(SassNumber $other): bool
    {
        if (!$this->hasUnits() || !$other->hasUnits()) {
            return true;
        }

        try {
            $this->greaterThan($other);
            return true;
        } catch (SassScriptException $e) {
            return false;
        }
    }

    public function greaterThan(Value $other): SassBoolean
    {
        if ($other instanceof SassNumber) {
            return SassBoolean::create($this->coerceUnits($other, [NumberUtil::class, 'fuzzyGreaterThan']));
        }

        throw new SassScriptException("Undefined operation \"$this > $other\".");
    }

    public function greaterThanOrEquals(Value $other): SassBoolean
    {
        if ($other instanceof SassNumber) {
            return SassBoolean::create($this->coerceUnits($other, [NumberUtil::class, 'fuzzyGreaterThanOrEquals']));
        }

        throw new SassScriptException("Undefined operation \"$this >= $other\".");
    }

    public function lessThan(Value $other): SassBoolean
    {
        if ($other instanceof SassNumber) {
            return SassBoolean::create($this->coerceUnits($other, [NumberUtil::class, 'fuzzyLessThan']));
        }

        throw new SassScriptException("Undefined operation \"$this < $other\".");
    }

    public function lessThanOrEquals(Value $other): SassBoolean
    {
        if ($other instanceof SassNumber) {
            return SassBoolean::create($this->coerceUnits($other, [NumberUtil::class, 'fuzzyLessThanOrEquals']));
        }

        throw new SassScriptException("Undefined operation \"$this > $other\".");
    }

    public function modulo(Value $other): Value
    {
        if ($other instanceof SassNumber) {
            return $this->withValue($this->coerceUnits($other, [NumberUtil::class, 'moduloLikeSass']));
        }

        throw new SassScriptException("Undefined operation \"$this % $other\".");
    }

    public function plus(Value $other): Value
    {
        if ($other instanceof SassNumber) {
            return $this->withValue($this->coerceUnits($other, function ($num1, $num2) {
                return $num1 + $num2;
            }));
        }

        if (!$other instanceof SassColor) {
            return parent::plus($other);
        }

        throw new SassScriptException("Undefined operation \"$this + $other\".");
    }

    public function minus(Value $other): Value
    {
        if ($other instanceof SassNumber) {
            return $this->withValue($this->coerceUnits($other, function ($num1, $num2) {
                return $num1 - $num2;
            }));
        }

        if (!$other instanceof SassColor) {
            return parent::plus($other);
        }

        throw new SassScriptException("Undefined operation \"$this - $other\".");
    }

    public function times(Value $other): Value
    {
        if ($other instanceof SassNumber) {
            if (!$other->hasUnits()) {
                return $this->withValue($this->value * $other->value);
            }

            return $this->multiplyUnits($this->value * $other->value, $other->getNumeratorUnits(), $other->getDenominatorUnits());
        }

        throw new SassScriptException("Undefined operation \"$this * $other\".");
    }

    public function dividedBy(Value $other): Value
    {
        if ($other instanceof SassNumber) {
            $value = NumberUtil::divideLikeSass($this->value, $other->value);

            if (!$other->hasUnits()) {
                return $this->withValue($value);
            }

            return $this->multiplyUnits($value, $other->getDenominatorUnits(), $other->getNumeratorUnits());
        }

        return parent::dividedBy($other);
    }

    public function unaryPlus(): Value
    {
        return $this;
    }

    public function equals(Value $other): bool
    {
        if (!$other instanceof SassNumber) {
            return false;
        }

        if (\count($this->getNumeratorUnits()) !== \count($other->getNumeratorUnits()) || \count($this->getDenominatorUnits()) !== \count($other->getDenominatorUnits())) {
            return false;
        }

        // In Sass, neither NaN nor Infinity are equal to themselves, while PHP defines INF==INF
        if (is_nan($this->value) || is_nan($other->value) || !is_finite($this->value) || !is_finite($other->value)) {
            return false;
        }

        if (!$this->hasUnits()) {
            return NumberUtil::fuzzyEquals($this->value, $other->value);
        }

        if (self::canonicalizeUnitList($this->getNumeratorUnits()) !== self::canonicalizeUnitList($other->getNumeratorUnits()) ||
            self::canonicalizeUnitList($this->getDenominatorUnits()) !== self::canonicalizeUnitList($other->getDenominatorUnits())
        ) {
            return false;
        }

        return NumberUtil::fuzzyEquals(
            $this->value * self::getCanonicalMultiplier($this->getNumeratorUnits()) / self::getCanonicalMultiplier($this->getDenominatorUnits()),
            $other->value * self::getCanonicalMultiplier($other->getNumeratorUnits()) / self::getCanonicalMultiplier($other->getDenominatorUnits())
        );
    }

    public function toCssString(): string
    {
        if ($this->asSlash !== null) {
            return $this->asSlash[0]->toCssString().'/'.$this->asSlash[1]->toCssString();
        }

        if (\count($this->getNumeratorUnits()) > 1 || \count($this->getDenominatorUnits()) > 0) {
            throw new SassScriptException("$this is not a valid CSS value.");
        }

        $unit = '';

        if (\count($this->getNumeratorUnits()) > 0) {
            $unit = $this->getNumeratorUnits()[0];
        }

        return SerializationUtil::serializeNumber($this->getValue()). $unit;
    }

    public function __toString(): string
    {
        if ($this->asSlash !== null) {
            return $this->asSlash[0]->__toString() . '/' . $this->asSlash[1]->__toString();
        }

        $unitString = '';

        if ($this->hasUnits()) {
            $unitString = self::getUnitString($this->getNumeratorUnits(), $this->getDenominatorUnits());
        }

        return SerializationUtil::serializeNumber($this->value). $unitString;
    }

    /**
     * @param string[] $units
     *
     * @return float|int
     */
    private static function getCanonicalMultiplier(array $units)
    {
        return array_reduce($units, function ($unit, $multiplier) {
            return $multiplier * self::getCanonicalMultiplierForUnit($unit);
        }, 1);
    }

    /**
     * @param string $unit
     *
     * @return float|int
     */
    private static function getCanonicalMultiplierForUnit(string $unit)
    {
        foreach (self::CONVERSIONS as $canonicalUnit => $conversions) {
            if (isset($conversions[$unit])) {
                return $conversions[$canonicalUnit] / $conversions[$unit];
            }
        }

        return 1;
    }

    /**
     * @param string[] $units
     *
     * @return string[]
     */
    private static function canonicalizeUnitList(array $units): array
    {
        if (\count($units) === 0) {
            return $units;
        }

        if (\count($units) === 1) {
            if (isset(self::TYPES_BY_UNIT[$units[0]])) {
                $type = self::TYPES_BY_UNIT[$units[0]];

                return [self::UNITS_BY_TYPE[$type][0]];
            }

            return $units;
        }

        $canonicalUnits = [];

        foreach ($units as $unit) {
            if (isset(self::TYPES_BY_UNIT[$unit])) {
                $type = self::TYPES_BY_UNIT[$unit];

                $canonicalUnits[] = self::UNITS_BY_TYPE[$type][0];
            } else {
                $canonicalUnits[] = $unit;
            }
        }

        sort($canonicalUnits);

        return $canonicalUnits;
    }

    /**
     * @param SassNumber $other
     * @param callable   $operation
     *
     * @return mixed
     *
     * @phpstan-template T
     * @phpstan-param callable(int|float, int|float): T $operation
     * @phpstan-return T
     */
    private function coerceUnits(SassNumber $other, callable $operation)
    {
        try {
            return \call_user_func($operation, $this->value, $other->coerceValueToMatch($this));
        } catch (SassScriptException $e) {
            // If the conversion fails, re-run it in the other direction. This will
            // generate an error message that prints [this] before [other], which is
            // more readable.
            $this->coerceValueToMatch($other);

            throw $e; // Should be unreadable as the coercion should throw.
        }
    }

    /**
     * @param string[]        $newNumeratorUnits
     * @param string[]        $newDenominatorUnits
     * @param bool            $coerceUnitless
     * @param string|null     $name      The argument name if this is a function argument
     * @param SassNumber|null $other
     * @param string|null     $otherName The argument name for $other if this is a function argument
     *
     * @return int|float
     *
     * @phpstan-param list<string> $newNumeratorUnits
     * @phpstan-param list<string> $newDenominatorUnits
     *
     * @throws SassScriptException if this number's units are not compatible with $newNumeratorUnits and $newDenominatorUnits
     */
    private function convertOrCoerceValue(array $newNumeratorUnits, array $newDenominatorUnits, bool $coerceUnitless, ?string $name = null, SassNumber $other = null, ?string $otherName = null)
    {
        assert($other === null || ($other->getNumeratorUnits() === $newNumeratorUnits && $other->getDenominatorUnits() === $newDenominatorUnits), sprintf("Expected %s to have units %s.", $other, self::getUnitString($newNumeratorUnits, $newDenominatorUnits)));

        if ($this->getNumeratorUnits() === $newNumeratorUnits && $this->getDenominatorUnits() === $newDenominatorUnits) {
            return $this->value;
        }

        $otherHasUnits = !empty($newNumeratorUnits) || !empty($newDenominatorUnits);

        if ($coerceUnitless && (!$otherHasUnits || !$this->hasUnits())) {
            return $this->value;
        }

        $value = $this->value;
        $oldNumerators = $this->getNumeratorUnits();

        foreach ($newNumeratorUnits as $newNumerator) {
            foreach ($oldNumerators as $key => $oldNumerator) {
                $conversionFactor = self::getConversionFactor($newNumerator, $oldNumerator);

                if (\is_null($conversionFactor)) {
                    continue;
                }

                $value *= $conversionFactor;
                unset($oldNumerators[$key]);
                continue 2;
            }

            throw $this->compatibilityException($otherHasUnits, $newNumeratorUnits, $newDenominatorUnits, $name, $other, $otherName);
        }

        $oldDenominators = $this->getDenominatorUnits();

        foreach ($newDenominatorUnits as $newDenominator) {
            foreach ($oldDenominators as $key => $oldDenominator) {
                $conversionFactor = self::getConversionFactor($newDenominator, $oldDenominator);

                if (\is_null($conversionFactor)) {
                    continue;
                }

                $value /= $conversionFactor;
                unset($oldDenominators[$key]);
                continue 2;
            }

            throw $this->compatibilityException($otherHasUnits, $newNumeratorUnits, $newDenominatorUnits, $name, $other, $otherName);
        }

        if (\count($oldNumerators) || \count($oldDenominators)) {
            throw $this->compatibilityException($otherHasUnits, $newNumeratorUnits, $newDenominatorUnits, $name, $other, $otherName);
        }

        return $value;
    }

    /**
     * @param bool            $otherHasUnits
     * @param string[]        $newNumeratorUnits
     * @param string[]        $newDenominatorUnits
     * @param string|null     $name
     * @param SassNumber|null $other
     * @param string|null     $otherName
     *
     * @return SassScriptException
     */
    private function compatibilityException(bool $otherHasUnits, array $newNumeratorUnits, array $newDenominatorUnits, ?string $name, SassNumber $other = null, ?string $otherName = null): SassScriptException
    {
        if ($other !== null) {
            $message = "$this and";

            if ($otherName !== null) {
                $message .= " \$$otherName:";
            }

            $message .= "$other have incompatible units";

            if (!$this->hasUnits() || !$otherHasUnits) {
                $message .= " (one has units and the other doesn't)";
            }

            return SassScriptException::forArgument("$message.", $name);
        }

        if (!$otherHasUnits) {
            return SassScriptException::forArgument("Expected $this to have no units.", $name);
        }

        if (\count($newNumeratorUnits) === 1 && \count($newDenominatorUnits) === 0 && isset(self::TYPES_BY_UNIT[$newNumeratorUnits[0]])) {
            $type = self::TYPES_BY_UNIT[$newNumeratorUnits[0]];
            $article = \in_array($type[0], ['a', 'e', 'i', 'o', 'u'], true) ? 'an' : 'a';
            $supportedUnits = implode(', ', self::UNITS_BY_TYPE[$type]);

            return SassScriptException::forArgument("Expected $this to have $article $type unit ($supportedUnits).", $name);
        }

        return SassScriptException::forArgument(sprintf('Expected %s to have unit%s %s.', $this, \count($newNumeratorUnits) + \count($newDenominatorUnits) !== 1 ? 's' : '', self::getUnitString($newNumeratorUnits, $newDenominatorUnits)), $name);
    }

    /**
     * @param int|float            $value
     * @param string[]             $otherNumerators
     * @param string[]             $otherDenominators
     *
     * @return SassNumber
     *
     * @phpstan-param list<string> $otherNumerators
     * @phpstan-param list<string> $otherDenominators
     */
    private function multiplyUnits($value, array $otherNumerators, array $otherDenominators): SassNumber
    {
        $newNumerators = array();

        foreach ($this->getNumeratorUnits() as $numerator) {
            foreach ($otherDenominators as $key => $denominator) {
                $conversionFactor = self::getConversionFactor($numerator, $denominator);

                if (\is_null($conversionFactor)) {
                    continue;
                }

                $value /= $conversionFactor;
                unset($otherDenominators[$key]);
                continue 2;
            }

            $newNumerators[] = $numerator;
        }

        $denominators = $this->getDenominatorUnits();

        foreach ($otherNumerators as $numerator) {
            foreach ($denominators as $key => $denominator) {
                $conversionFactor = self::getConversionFactor($numerator, $denominator);

                if (\is_null($conversionFactor)) {
                    continue;
                }

                $value /= $conversionFactor;
                unset($denominators[$key]);
                continue 2;
            }

            $newNumerators[] = $numerator;
        }

        $newDenominators = array_values(array_merge($denominators, $otherDenominators));

        return self::withUnits($value, $newNumerators, $newDenominators);
    }

    /**
     * Returns the number of [unit1]s per [unit2].
     *
     * Equivalently, `1unit2 * conversionFactor(unit1, unit2) = 1unit1`.
     *
     * @param string $unit1
     * @param string $unit2
     *
     * @return float|int|null
     */
    protected static function getConversionFactor(string $unit1, string $unit2)
    {
        if ($unit1 === $unit2) {
            return 1;
        }

        foreach (self::CONVERSIONS as $unitVariants) {
            if (isset($unitVariants[$unit1]) && isset($unitVariants[$unit2])) {
                return $unitVariants[$unit1] / $unitVariants[$unit2];
            }
        }

        return null;
    }

    /**
     * Returns unit(s) as the product of numerator units divided by the product of denominator units
     *
     * @param string[] $numerators
     * @param string[] $denominators
     *
     * @phpstan-param list<string> $numerators
     * @phpstan-param list<string> $denominators
     *
     * @return string
     */
    private static function getUnitString(array $numerators, array $denominators): string
    {
        if (!\count($numerators)) {
            if (\count($denominators) === 0) {
                return 'no units';
            }

            if (\count($denominators) === 1) {
                return $denominators[0] . '^-1';
            }

            return '(' . implode('*', $denominators) . ')^-1';
        }

        return implode('*', $numerators) . (\count($denominators) ? '/' . implode('*', $denominators) : '');
    }
}
