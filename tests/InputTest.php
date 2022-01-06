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
use ScssPhp\ScssPhp\Logger\QuietLogger;

/**
 * Input test - runs all the tests in inputs/ and compares their output to outputs/
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 */
class InputTest extends TestCase
{
    /**
     * @var Compiler
     */
    private $scss;

    private static $inputDir = 'inputs';
    private static $outputDir = 'outputs';

    /**
     * @dataProvider fileNameProvider
     * @param string $inFname
     * @param string $outFname
     */
    public function testInputFile($inFname, $outFname)
    {
        chdir(__DIR__);

        $this->scss = new Compiler();
        $this->scss->addImportPath(self::$inputDir);
        $this->scss->setLogger(new QuietLogger());

        if (getenv('BUILD')) {
            $this->buildInput($inFname, $outFname);
            $this->assertNull(null);
            return;
        }

        if (! is_readable($outFname)) {
            $this->fail("$outFname is missing, consider building tests with \"make rebuild-outputs\".");
        }

        $input = file_get_contents($inFname);
        $output = file_get_contents($outFname);

        $css = $this->scss->compileString($input, $inFname)->getCss();
        $this->assertEquals($output, $css);
    }

    public function fileNameProvider()
    {
        return array_map(
            function ($a) {
                return [$a, InputTest::outputNameFor($a)];
            },
            self::findInputNames()
        );
    }

    /**
     * @param string $inFname
     * @param string $outFname
     *
     * @return void
     */
    private function buildInput($inFname, $outFname)
    {
        $css = $this->scss->compileString(file_get_contents($inFname), $inFname)->getCss();

        file_put_contents($outFname, $css);
    }

    private static function findInputNames()
    {
        $files = glob(__DIR__ . '/' . self::$inputDir . '/*');
        $files = array_filter($files, 'is_file');

        $filesKeys = array_map(
            function ($a) {
                return substr($a, strlen(__DIR__) + 1);
            },
            $files
        );

        return array_combine($filesKeys, $files);
    }

    public static function outputNameFor($input)
    {
        $front = preg_quote(__DIR__ . '/', '/');
        $out = preg_replace("/^$front/", '', $input);

        $in = preg_quote(self::$inputDir . '/', '/');
        $out = preg_replace("/$in/", self::$outputDir . '/', $out);
        $out = preg_replace('/.scss$/', '.css', $out);

        return __DIR__ . '/' . $out;
    }
}
