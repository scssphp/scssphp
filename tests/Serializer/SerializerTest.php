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

namespace ScssPhp\ScssPhp\Tests\Serializer;

use ScssPhp\ScssPhp\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Value\SassString;

class SerializerTest extends TestCase
{
    /**
     * @dataProvider provideQuotedStrings
     */
    public function testSerializeQuotedString($expected, $input)
    {
        $this->assertSame($expected, Serializer::serializeValue(new SassString($input)));
    }

    public static function provideQuotedStrings(): iterable
    {
        yield [
            '"foo"',
            'foo',
        ];
        yield [
            '"fo\'o"',
            'fo\'o',
        ];
        yield [
            '\'fo"o\'',
            'fo"o',
        ];
        yield [
            '"f\\"o\'o"',
            'f"o\'o',
        ];
        yield [
            '"fo\\\\o"',
            'fo\\o',
        ];
        yield [
            "\"fo\to\"",
            "fo\to",
        ];
        yield [
            '"fo\ao"',
            "fo\no",
        ];
        yield [
            '"fo\a 1o"',
            "fo\n1o",
        ];
        yield [
            '"fo\a Ao"',
            "fo\nAo",
        ];
        yield [
            '"fo\a  o"',
            "fo\n o",
        ];
        yield [
            "\"fo\\a \to\"",
            "fo\n\to",
        ];
        yield [
            '"fo\a co"',
            "fo\nco",
        ];
        yield [
            '"foo\a"',
            "foo\n",
        ];
    }

    /**
     * @dataProvider provideUnquotedStrings
     */
    public function testSerializeUnquotedString($expected, $input)
    {
        $this->assertSame($expected, Serializer::serializeValue(new SassString($input, false)));
    }

    public static function provideUnquotedStrings(): iterable
    {
        yield [
            'foo',
            'foo',
        ];
        yield [
            'fo\'o',
            'fo\'o',
        ];
        yield [
            'fo"o',
            'fo"o',
        ];
        yield [
            'f"o\'o',
            'f"o\'o',
        ];
        yield [
            'fo\\o',
            'fo\\o',
        ];
        yield [
            "fo\to",
            "fo\to",
        ];
        yield [
            'fo o',
            "fo\no",
        ];
        yield [
            'fo 1o',
            "fo\n1o",
        ];
        yield [
            'fo A o',
            "fo\nA o",
        ];
        yield [
            'fo o',
            "fo\n   o",
        ];
        yield [
            "fo \to",
            "fo\n\to",
        ];
    }
}
