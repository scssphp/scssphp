<?php

/**
 * SCSSPHP
 *
 * @copyright 2018-2020 Anthon Pang
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Tests\Value;

use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Collection\Map;
use ScssPhp\ScssPhp\Value\ListSeparator;
use ScssPhp\ScssPhp\Value\SassBoolean;
use ScssPhp\ScssPhp\Value\SassColor;
use ScssPhp\ScssPhp\Value\SassFunction;
use ScssPhp\ScssPhp\Value\SassList;
use ScssPhp\ScssPhp\Value\SassMap;
use ScssPhp\ScssPhp\Value\SassNull;
use ScssPhp\ScssPhp\Value\SassNumber;
use ScssPhp\ScssPhp\Value\SassString;
use ScssPhp\ScssPhp\Value\Value;

abstract class ValueTestCase extends TestCase
{
    protected static function parseValue(string $source): Value
    {
        // TODO switch to an actual equivalent of dart-sass's parseValue test utility once the compiler uses value objects
        switch ($source) {
            case 'null':
                return SassNull::create();
            case 'true':
                return SassBoolean::create(true);
            case 'false':
                return SassBoolean::create(false);
            case '123':
                return SassNumber::create(123);
            case '123.456':
                return SassNumber::create(123.456);
            case '123.000000000001':
                return SassNumber::create(123.000000000001);
            case '123px':
                return SassNumber::create(123, 'px');
            case '123px / 5ms':
                return SassNumber::withUnits(123 / 5, ['px'], ['ms']);
            case 'blue':
                return SassColor::rgb(0, 0, 255);
            case '#123456':
                return SassColor::rgb(0x12, 0x34, 0x56);
            case 'hsl(120, 42%, 42%)':
                return SassColor::hsl(120, 42, 42);
            case 'rgba(255, 0, 0, 0)':
                return SassColor::rgb(255, 0, 0, 0);
            case 'rgba(10, 20, 30, 0.7)':
                return SassColor::rgb(10, 20, 30, 0.7);
            case 'grey':
                return SassColor::rgb(0x80, 0x80, 0x80);
            case "get-function('red')":
                return new SassFunction('red');
            case 'foobar':
                return new SassString('foobar', false);
            case 'a👭b👬c':
                return new SassString('a👭b👬c', false);
            case '"foobar"':
                return new SassString('foobar', true);
            case '()':
                return new SassList([], ListSeparator::UNDECIDED);
            case 'a, b, c':
                return new SassList([
                    new SassString('a', false),
                    new SassString('b', false),
                    new SassString('c', false),
                ], ListSeparator::COMMA);
            case 'a b c':
                return new SassList([
                    new SassString('a', false),
                    new SassString('b', false),
                    new SassString('c', false),
                ], ListSeparator::SPACE);
            case '[a, b, c]':
                return new SassList([
                    new SassString('a', false),
                    new SassString('b', false),
                    new SassString('c', false),
                ], ListSeparator::COMMA, true);
            case 'list.slash(a, b, c)':
                return new SassList([
                    new SassString('a', false),
                    new SassString('b', false),
                    new SassString('c', false),
                ], ListSeparator::SLASH);
            case '[1]':
                return new SassList([SassNumber::create(1)], ListSeparator::UNDECIDED, true);
            case '(1,)':
                return new SassList([SassNumber::create(1)], ListSeparator::COMMA, false);
            case '(a: b, c: d)':
                $map = new Map();
                $map->put(new SassString('a', false), new SassString('b', false));
                $map->put(new SassString('c', false), new SassString('d', false));

                return SassMap::create($map);
            case 'map-remove((a: b), a)':
                return SassMap::create(new Map());

            default:
                throw new \UnexpectedValueException('Unsupported source for the fake parseValue implementation: ' . $source);
        }
    }

    protected function assertSassEquals(Value $value, Value $expected)
    {
        $this->assertTrue($value->equals($expected), "$value should be equal to $expected");
    }

    protected function assertNotSassEquals(Value $value, Value $expected)
    {
        $this->assertFalse($value->equals($expected), "$value should not be equal to $expected");
    }
}
