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
use ScssPhp\ScssPhp\Logger\StreamLogger;
use ScssPhp\ScssPhp\Node\Number;
use ScssPhp\ScssPhp\ValueConverter;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * API test
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 */
class ApiTest extends TestCase
{
    use ExpectDeprecationTrait;

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

    /**
     * @group legacy
     */
    public function testUserFunctionCoercedValue()
    {
        $this->scss = new Compiler();

        $this->scss->registerFunction('add-two', function ($args) {
            list($a, $b) = $args;
            return $a[1] + $b[1];
        }, ['number1', 'number2']);

        $this->expectDeprecation('Returning a PHP value from the "add-two" custom function is deprecated. A sass value must be returned instead.');

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

    /**
     * @group legacy
     */
    public function testUserFunctionWithoutArgumentDeclaration()
    {
        $this->scss = new Compiler();

        $this->expectDeprecation('Omitting the argument declaration when registering custom function is deprecated and won\'t be supported in ScssPhp 2.0 anymore.');

        $this->scss->registerFunction('get-null', function () {
            return Compiler::$null;
        });

    }

    /**
     * @group legacy
     */
    public function testUserFunctionOverride()
    {
        $compiler = new Compiler();

        $this->expectDeprecation('The "blue" function is a core sass function. Overriding it with a custom implementation through "ScssPhp\ScssPhp\Compiler::registerFunction" is deprecated and won\'t be supported in ScssPhp 2.0 anymore.');

        $compiler->registerFunction('blue', function ($args) {
            return Compiler::$null;
        }, []);
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

    /**
     * @group legacy
     */
    public function testWrongCaseInFunctionName()
    {
        $this->scss = new Compiler();

        $logStream = fopen("php://memory", 'r+');
        assert($logStream !== false);
        $this->scss->setLogger(new StreamLogger($logStream));

        $this->expectDeprecation("Calling built-in functions with a non-standard name is deprecated since Scssphp 1.8.0 and will not work anymore in 2.0 (they will be treated as CSS function calls instead).\nUse \"fade-out\" instead of \"faDe-out\".");

        $result = $this->compile('a { b: faDe-out(#e7e8ea, 0.75) }');

        rewind($logStream);
        $output = stream_get_contents($logStream);
        fclose($logStream);

        $this->assertEquals("a {\n  b: rgba(231, 232, 234, 0.25);\n}", $result);
        $this->assertEquals("DEPRECATION WARNING: Calling built-in functions with a non-standard name is deprecated since Scssphp 1.8.0 and will not work anymore in 2.0 (they will be treated as CSS function calls instead).\nUse \"fade-out\" instead of \"faDe-out\".\n         on line 1 of (unknown file)", trim($output));
    }

    /**
     * @group legacy
     */
    public function testWrongCaseAtBeginningOfFunctionName()
    {
        $this->scss = new Compiler();

        $logStream = fopen("php://memory", 'r+');
        assert($logStream !== false);
        $this->scss->setLogger(new StreamLogger($logStream));

        $this->expectDeprecation("Calling built-in functions with a non-standard name is deprecated since Scssphp 1.8.0 and will not work anymore in 2.0 (they will be treated as CSS function calls instead).\nUse \"fade-out\" instead of \"Fade-out\".");

        $result = $this->compile('a { b: Fade-out(#e7e8ea, 0.75) }');

        rewind($logStream);
        $output = stream_get_contents($logStream);
        fclose($logStream);

        $this->assertEquals("a {\n  b: rgba(231, 232, 234, 0.25);\n}", $result);
        $this->assertEquals("DEPRECATION WARNING: Calling built-in functions with a non-standard name is deprecated since Scssphp 1.8.0 and will not work anymore in 2.0 (they will be treated as CSS function calls instead).\nUse \"fade-out\" instead of \"Fade-out\".\n         on line 1 of (unknown file)", trim($output));
    }

    /**
     * @group legacy
     */
    public function testOmittedDashInFunctionName()
    {
        $this->scss = new Compiler();

        $logStream = fopen("php://memory", 'r+');
        assert($logStream !== false);
        $this->scss->setLogger(new StreamLogger($logStream));

        $this->expectDeprecation("Calling built-in functions with a non-standard name is deprecated since Scssphp 1.8.0 and will not work anymore in 2.0 (they will be treated as CSS function calls instead).\nUse \"fade-out\" instead of \"fadeout\".");

        $result = $this->compile('a { b: fadeout(#e7e8ea, 0.75) }');

        rewind($logStream);
        $output = stream_get_contents($logStream);
        fclose($logStream);

        $this->assertEquals("a {\n  b: rgba(231, 232, 234, 0.25);\n}", $result);
        $this->assertEquals("DEPRECATION WARNING: Calling built-in functions with a non-standard name is deprecated since Scssphp 1.8.0 and will not work anymore in 2.0 (they will be treated as CSS function calls instead).\nUse \"fade-out\" instead of \"fadeout\".\n         on line 1 of (unknown file)", trim($output));
    }

    /**
     * @group legacy
     */
    public function testAdditionalDashInFunctionName()
    {
        $this->scss = new Compiler();

        $logStream = fopen("php://memory", 'r+');
        assert($logStream !== false);
        $this->scss->setLogger(new StreamLogger($logStream));

        $this->expectDeprecation("Calling built-in functions with a non-standard name is deprecated since Scssphp 1.8.0 and will not work anymore in 2.0 (they will be treated as CSS function calls instead).\nUse \"transparentize\" instead of \"trans-parentize\".");

        $result = $this->compile('a { b: trans-parentize(#e7e8ea, 0.75) }');

        rewind($logStream);
        $output = stream_get_contents($logStream);
        fclose($logStream);

        $this->assertEquals("a {\n  b: rgba(231, 232, 234, 0.25);\n}", $result);
        $this->assertEquals("DEPRECATION WARNING: Calling built-in functions with a non-standard name is deprecated since Scssphp 1.8.0 and will not work anymore in 2.0 (they will be treated as CSS function calls instead).\nUse \"transparentize\" instead of \"trans-parentize\".\n         on line 1 of (unknown file)", trim($output));
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

    /**
     * @group legacy
     */
    public function testImportCssCustomCallback()
    {
        $this->scss = new Compiler();

        $this->scss->addImportPath(function ($path) {
            return __DIR__ . '/inputs/' . str_replace('.css', '.scss', $path);
        });

        $this->expectDeprecation('Returning a file to import for CSS or external references in custom importer callables is deprecated and will not be supported anymore in ScssPhp 2.0. This behavior is not compliant with the Sass specification. Update your "closure{%s}" importer.');

        $this->assertEquals(
            trim(file_get_contents(__DIR__ . '/outputs/variables.css')),
            $this->compile('@import "variables.css";')
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

    public function testSourceMapWithoutSourcePath()
    {
        $source = <<<'SCSS'
@import "test.css";

body {
  background-color: orange;

  h1 {
    border: 2rem dashed black;
  }
}

SCSS;

        $compiler = new Compiler();
        $compiler->setSourceMap(Compiler::SOURCE_MAP_FILE);
        $compiler->setSourceMapOptions(['sourceMapURL' => 'test.css.map']);

        $result = $compiler->compileString($source);

        $this->assertStringEndsWith('/*# sourceMappingURL=test.css.map */', $result->getCss());
        $this->assertNotEmpty($result->getSourceMap());
    }

    public function testGetStringText()
    {
        $compiler = new Compiler();
        $string = ValueConverter::parseValue('"foobar"');

        $this->assertEquals('foobar', $compiler->getStringText($compiler->assertString($string)));
    }

    /**
     * @group legacy
     */
    public function testCompile()
    {
        $compiler = new Compiler();

        $this->expectDeprecation('The "ScssPhp\ScssPhp\Compiler::compile" method is deprecated. Use "compileString" instead.');

        $css = $compiler->compile('a { b: c}');
        $this->assertSame("a {\n  b: c;\n}\n", $css);
    }

    /**
     * @group legacy
     */
    public function testDeprecatedChildCompiler()
    {
        $this->expectDeprecation('Registering custom functions by extending the Compiler and using the lib* discovery mechanism is deprecated and will be removed in 2.0. Replace the "ScssPhp\ScssPhp\Tests\DeprecatedChildCompiler::libDeprecatedChild" method with registering the "deprecated_child" function through "Compiler::registerFunction".');
        $this->expectDeprecation('Overriding the "blue" core function by extending the Compiler is deprecated and will be unsupported in 2.0. Remove the "ScssPhp\ScssPhp\Tests\DeprecatedChildCompiler::libBlue" method.');
        $this->scss = new DeprecatedChildCompiler();

        $this->assertEquals(
            "a {\n  b: true;\n  c: 255;\n  d: 1;\n}",
            $this->compile("a {\n  b: deprecated_child();\n  c: red(#f00);\n  d: blue(#f00);\n}")
        );
    }

    public function compile($str)
    {
        return trim($this->scss->compileString($str)->getCss());
    }
}

class DeprecatedChildCompiler extends Compiler
{
    protected function libDeprecatedChild($args)
    {
        return self::$true;
    }

    protected function libBlue($args)
    {
        return new Number(1, '');
    }
}
