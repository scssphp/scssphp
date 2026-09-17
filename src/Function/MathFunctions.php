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

namespace ScssPhp\ScssPhp\Function;

use ScssPhp\ScssPhp\Deprecation;
use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Util\NumberUtil;
use ScssPhp\ScssPhp\Value\SassBoolean;
use ScssPhp\ScssPhp\Value\SassNull;
use ScssPhp\ScssPhp\Value\SassNumber;
use ScssPhp\ScssPhp\Value\SassString;
use ScssPhp\ScssPhp\Value\Value;
use ScssPhp\ScssPhp\Warn;

/**
 * @internal
 */
final class MathFunctions
{
    /**
     * @param list<Value> $arguments
     */
    public static function abs(array $arguments): Value
    {
        $number = $arguments[0]->assertNumber('number');
        // TODO implement the deprecation for the % unit once modules are implemented to provided the replacement

        return SassNumber::withUnits(abs($number->getValue()), $number->getNumeratorUnits(), $number->getDenominatorUnits());
    }

    /**
     * @param list<Value> $arguments
     */
    public static function ceil(array $arguments): Value
    {
        return self::numberFunction($arguments, ceil(...));
    }

    /**
     * @param list<Value> $arguments
     */
    public static function floor(array $arguments): Value
    {
        return self::numberFunction($arguments, floor(...));
    }

    /**
     * @param list<Value> $arguments
     */
    public static function max(array $arguments): Value
    {
        $max = null;

        foreach ($arguments[0]->asList() as $value) {
            $number = $value->assertNumber();

            if ($max === null || $max->lessThan($number)->isTruthy()) {
                $max = $number;
            }
        }

        if ($max !== null) {
            return $max;
        }

        throw new SassScriptException('At least one argument must be passed.');
    }

    /**
     * @param list<Value> $arguments
     */
    public static function min(array $arguments): Value
    {
        $min = null;

        foreach ($arguments[0]->asList() as $value) {
            $number = $value->assertNumber();

            if ($min === null || $min->greaterThan($number)->isTruthy()) {
                $min = $number;
            }
        }

        if ($min !== null) {
            return $min;
        }

        throw new SassScriptException('At least one argument must be passed.');
    }

    /**
     * @param list<Value> $arguments
     */
    public static function round(array $arguments): Value
    {
        return self::numberFunction($arguments, round(...));
    }

    /**
     * @param list<Value> $arguments
     */
    public static function clamp(array $arguments): Value
    {
        $min = $arguments[0]->assertNumber('min');
        $number = $arguments[1]->assertNumber('number');
        $max = $arguments[2]->assertNumber('max');

        // Call this purely for its error-checking side effect.
        $number->convertValueToMatch($min, 'number', 'min');
        $max->convertValueToMatch($min, 'max', 'min');

        if ($min->greaterThanOrEquals($max)->isTruthy()) {
            return $min;
        }
        if ($min->greaterThanOrEquals($number)->isTruthy()) {
            return $min;
        }
        if ($number->greaterThanOrEquals($max)->isTruthy()) {
            return $max;
        }

        return $number;
    }

    /**
     * @param list<Value> $arguments
     */
    public static function hypot(array $arguments): Value
    {
        $numbers = array_map(fn(Value $argument) => $argument->assertNumber(), $arguments[0]->asList());

        if ($numbers === []) {
            throw new SassScriptException('At least one argument must be passed.');
        }

        $subtotal = 0.0;
        foreach ($numbers as $i => $number) {
            $value = $number->convertValueToMatch($numbers[0], 'numbers[' . ($i + 1) . ']', 'numbers[1]');
            $subtotal += $value ** 2;
        }

        return SassNumber::withUnits(sqrt($subtotal), $numbers[0]->getNumeratorUnits(), $numbers[0]->getDenominatorUnits());
    }

    /**
     * @param list<Value> $arguments
     */
    public static function log(array $arguments): Value
    {
        $number = $arguments[0]->assertNumber('number');
        $base = $arguments[1]->realNull()?->assertNumber('base');

        if ($number->hasUnits()) {
            throw new SassScriptException("\$number: Expected $number to have no units.");
        }

        if ($base !== null && $base->hasUnits()) {
            throw new SassScriptException("\$base: Expected $base to have no units.");
        }

        return NumberUtil::log($number, $base);
    }

    /**
     * @param list<Value> $arguments
     */
    public static function pow(array $arguments): Value
    {
        $base = $arguments[0]->assertNumber('base');
        $exponent = $arguments[1]->assertNumber('exponent');

        return NumberUtil::pow($base, $exponent);
    }

    /**
     * @param list<Value> $arguments
     */
    public static function sqrt(array $arguments): Value
    {
        return NumberUtil::sqrt($arguments[0]->assertNumber('number'));
    }

    /**
     * @param list<Value> $arguments
     */
    public static function sin(array $arguments): Value
    {
        return NumberUtil::sin($arguments[0]->assertNumber('number'));
    }

    /**
     * @param list<Value> $arguments
     */
    public static function cos(array $arguments): Value
    {
        return NumberUtil::cos($arguments[0]->assertNumber('number'));
    }

    /**
     * @param list<Value> $arguments
     */
    public static function tan(array $arguments): Value
    {
        return NumberUtil::tan($arguments[0]->assertNumber('number'));
    }

    /**
     * @param list<Value> $arguments
     */
    public static function asin(array $arguments): Value
    {
        return NumberUtil::asin($arguments[0]->assertNumber('number'));
    }

    /**
     * @param list<Value> $arguments
     */
    public static function acos(array $arguments): Value
    {
        return NumberUtil::acos($arguments[0]->assertNumber('number'));
    }

    /**
     * @param list<Value> $arguments
     */
    public static function atan(array $arguments): Value
    {
        return NumberUtil::atan($arguments[0]->assertNumber('number'));
    }

    /**
     * @param list<Value> $arguments
     */
    public static function atan2(array $arguments): Value
    {
        $y = $arguments[0]->assertNumber('y');
        $x = $arguments[1]->assertNumber('x');

        return NumberUtil::atan2($y, $x);
    }

    /**
     * @param list<Value> $arguments
     */
    public static function div(array $arguments): Value
    {
        $number1 = $arguments[0];
        $number2 = $arguments[1];

        if (!$number1 instanceof SassNumber || !$number2 instanceof SassNumber) {
            Warn::warning("math.div() will only support number arguments in a future release.\nUse list.slash() instead for a slash separator.");
        }

        return $number1->dividedBy($number2);
    }

    /**
     * @param list<Value> $arguments
     */
    public static function compatible(array $arguments): Value
    {
        $number1 = $arguments[0]->assertNumber('number1');
        $number2 = $arguments[1]->assertNumber('number2');

        return SassBoolean::create($number1->isComparableTo($number2));
    }

    /**
     * @param list<Value> $arguments
     */
    public static function isUnitless(array $arguments): Value
    {
        $number = $arguments[0]->assertNumber('number');

        return SassBoolean::create(!$number->hasUnits());
    }

    /**
     * @param list<Value> $arguments
     */
    public static function unit(array $arguments): Value
    {
        $number = $arguments[0]->assertNumber('number');

        return new SassString($number->getUnitString(), true);
    }

    /**
     * @param list<Value> $arguments
     */
    public static function percentage(array $arguments): Value
    {
        $number = $arguments[0]->assertNumber('number');
        $number->assertNoUnits('number');

        return SassNumber::create($number->getValue() * 100, '%');
    }

    /**
     * @param list<Value> $arguments
     */
    public static function random(array $arguments): Value
    {
        if ($arguments[0] instanceof SassNull) {
            // TODO use a better algorithm to generate a random float.
            $max = mt_getrandmax();

            return SassNumber::create(mt_rand(0, $max - 1) / $max);
        }

        $limit = $arguments[0]->assertNumber('limit');

        if ($limit->hasUnits()) {
            $unitString = $limit->getUnitString();

            // TODO update the message when implementing modules and deprecating division.
            Warn::forDeprecation(
                <<<TXT
                random() will no longer ignore \$limit units ($limit) in a future release.

                Recommendation: random(\$limit / 1$unitString) * 1$unitString

                To preserve current behavior: random(\$limit / 1$unitString)

                More info: https://sass-lang.com/d/function-units
                TXT,
                Deprecation::functionUnits
            );
        }

        $limitScalar = $limit->assertInt('limit');
        if ($limitScalar < 1) {
            throw new SassScriptException("\$limit: Must be greater than 0, was $limit.");
        }

        return SassNumber::create(mt_rand(1, $limitScalar));
    }

    /**
     * Implements a callable that transforms a number's value
     * using $transform and preserves its units.
     *
     * @param list<Value> $arguments
     * @param callable(float): float $transform
     *
     * @param-immediately-invoked-callable $transform
     */
    private static function numberFunction(array $arguments, callable $transform): Value
    {
        $number = $arguments[0]->assertNumber('number');

        return SassNumber::withUnits($transform($number->getValue()), $number->getNumeratorUnits(), $number->getDenominatorUnits());
    }
}
