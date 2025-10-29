<?php

namespace ScssPhp\ScssPhp\Tests;

use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\Value\SassList;
use ScssPhp\ScssPhp\Value\SassNumber;
use ScssPhp\ScssPhp\ValueConverter;
use PHPUnit\Framework\TestCase;

class ValueConverterTest extends TestCase
{
    public function testParseValueWithTrailingComment(): void
    {
        $value = 'd, /* comment */ e /* trailing comment */';

        $convertedValue = ValueConverter::parseValue($value);

        $this->assertInstanceOf(SassList::class, $convertedValue);
        $this->assertCount(2, $convertedValue->asList());
    }

    public function testParseValueWithTrailingContent(): void
    {
        $value = 'd, /* comment */ e; trailing';

        $this->expectException(SassException::class);
        $this->expectExceptionMessage('expected ")".');

        ValueConverter::parseValue($value);
    }

    /**
     * @dataProvider providePhpConversionCases
     */
    public function testPhpConversion(string $expectedSassValue, mixed $phpValue): void
    {
        $this->assertEquals(ValueConverter::parseValue($expectedSassValue), ValueConverter::fromPhp($phpValue));
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function providePhpConversionCases(): iterable
    {
        yield ['null', null];
        yield ['true', true];
        yield ['false', false];
        yield ['25', 25];
        yield ['25', 25.0];
        yield ['25px', SassNumber::create(25, 'px')];
        yield ['""', ''];
        yield ['"foobar"', 'foobar'];
        yield ['"#fff"', '#fff'];
        yield ['()', []];
        yield ['(25,)', [25]];
        yield ['("foo": 25)', ['foo' => 25]];
        yield ['("1": "hello")', [1 => "hello"]];
    }

    public function testUnsupportedPhpValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot convert the value of type "stdClass" to a Sass value.');

        ValueConverter::fromPhp(new \stdClass());
    }
}
