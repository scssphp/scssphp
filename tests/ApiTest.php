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

namespace ScssPhp\ScssPhp\Tests;

use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Node\Number;
use ScssPhp\ScssPhp\ValueConverter;

/**
 * API test
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 */
class ApiTest extends TestCase
{
    /**
     * @var Compiler
     */
    private $scss;

    public function testUserFunction()
    {
        $this->scss = new Compiler();

        $this->scss->registerFunction('add-two', function ($args) {
            list($a, $b) = $args;
            return new Number($a[1] + $b[1], '');
        }, ['number1', 'number2']);

        $this->assertEquals(
            'result: 30;',
            $this->compile('result: add-two(10, 20);')
        );
    }

    public function testUserFunctionNull()
    {
        $this->scss = new Compiler();

        $this->scss->registerFunction('get-null', function ($args) {
            return Compiler::$null;
        }, []);

        $this->assertEquals(
            '',
            $this->compile('result: get-null();')
        );
    }

    public function testUserFunctionKwargs()
    {
        $this->scss = new Compiler();

        $this->scss->registerFunction(
            'divide',
            function ($args, $kwargs) {
                return new Number($kwargs['dividend'][1] / $kwargs['divisor'][1], '');
            },
            ['dividend', 'divisor']
        );

        $this->assertEquals(
            'result: 15;',
            $this->compile('result: divide($divisor: 2, $dividend: 30);')
        );
    }

    public function testImportCustomCallback()
    {
        $this->scss = new Compiler();

        $this->scss->addImportPath(function ($path) {
            return __DIR__ . '/inputs/' . str_replace('.foo', '.scss', $path);
        });

        $this->assertEquals(
            trim(file_get_contents(__DIR__ . '/outputs/variables.css')),
            $this->compile('@import "variables.foo";')
        );
    }

    public function testImportAbsolutePath()
    {
        $this->scss = new Compiler();

        $basePath = __DIR__ . \DIRECTORY_SEPARATOR . 'inputs';

        $this->scss->addVariables(['base-path' => ValueConverter::fromPhp($basePath)]);
        $this->scss->addImportPath(__DIR__ . \DIRECTORY_SEPARATOR . 'inputs');

        $this->assertEquals(
            trim(file_get_contents(__DIR__ . '/outputs/variables.css')),
            $this->compile('@import $base-path + "/variables.scss";')
        );
    }

    /**
     * @dataProvider provideSetVariables
     */
    public function testSetVariables($expected, $scss, $variables)
    {
        $this->scss = new Compiler();

        $this->scss->replaceVariables(array_map('ScssPhp\ScssPhp\ValueConverter::parseValue', $variables));

        $this->assertEquals($expected, $this->compile($scss));
    }

    public function provideSetVariables()
    {
        return [
            [
                ".magic {\n  color: red;\n  width: 760px;\n}",
                '.magic { color: $color; width: $base - 200; }',
                [
                    'color' => 'red',
                    'base'  => '960px',
                ],
            ],
            [
                ".logo {\n  color: gray;\n}",
                '.logo { color: desaturate($primary, 100%); }',
                [
                    'primary' => '#ff0000',
                ],
            ],
            // !default
            [
                ".default {\n  color: red;\n}",
                '$color: red !default;' . "\n" . '.default { color: $color; }',
                [
                ],
            ],
            // no !default
            [
                ".default {\n  color: red;\n}",
                '$color: red;' . "\n" . '.default { color: $color; }',
                [
                    'color' => 'blue',
                ],
            ],
            // override !default
            [
                ".default {\n  color: blue;\n}",
                '$color: red !default;' . "\n" . '.default { color: $color; }',
                [
                    'color' => 'blue',
                ],
            ],
        ];
    }

    public function testCompileWithoutCharset()
    {
        $this->scss = new Compiler();
        $this->scss->setCharset(false);

        self::assertEquals(
            "a {\n  b: \"à\";\n}",
            $this->compile('a { b: "à" }')
        );
    }

    public function testCompileWithCharset()
    {
        $this->scss = new Compiler();
        $this->scss->setCharset(true);

        self::assertEquals(
            "@charset \"UTF-8\";\na {\n  b: \"à\";\n}",
            $this->compile('a { b: "à" }')
        );
    }

    public function testCompileByteOrderMarker()
    {
        $this->scss = new Compiler();

        // test that BOM is stripped/ignored
        $this->assertEquals(
            '@import "main.css";',
            $this->compile("\xEF\xBB\xBF@import \"main.css\";")
        );
    }

    public function testGetStringText()
    {
        $compiler = new Compiler();
        $string = ValueConverter::parseValue('"foobar"');

        $this->assertEquals('foobar', $compiler->getStringText($compiler->assertString($string)));
    }

    public function compile($str)
    {
        return trim($this->scss->compileString($str)->getCss());
    }
}
