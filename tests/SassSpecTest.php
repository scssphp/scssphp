<?php
/**
 * SCSSPHP
 *
 * @copyright 2012-2019 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Tests;

use ScssPhp\ScssPhp\Compiler;

/**
 * Sass Spec Test - extracts tests from https://github.com/sass/sass-spec
 *
 * @author Cerdic <cedric@yterium.com>
 */
class SassSpecTest extends \PHPUnit_Framework_TestCase
{
    protected static $scss;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        if (is_null(static::$scss)) {
            @ini_set('memory_limit', "256M");

            static::$scss = new Compiler();
            static::$scss->setFormatter('ScssPhp\ScssPhp\Formatter\Expanded');
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
    }

    /**
     * @param string $name
     * @param string $scss
     * @param string $css
     * @param string $options
     * @param string $error
     * @param string $warning
     *
     * @dataProvider provideTests
     */
    public function testTests($name, $input, $output)
    {
        static $init = false;

        if (! getenv('TEST_SASS_SPEC')) {
            $this->markTestSkipped('Define TEST_SASS_SPEC=1 to enable sass-spec compatibility tests');

            return;
        }

        list($options, $scss, $includes) = $input;
        list($css, $warning, $error) = $output;

        // ignore tests expecting an error for the moment
        if (! strlen($error)) {
            // this test needs @import of includes files, build a dir with files and set the ImportPaths
            if ($includes) {
                $basedir = sys_get_temp_dir() . '/sass-spec/' . preg_replace(",^\d+/\d+\.\s*,", "", $name);

                foreach ($includes as $f => $c) {
                    $f = $basedir . '/' . $f;

                    if (! is_dir(dirname($f))) {
                        passthru("mkdir -p " . dirname($f));
                    }

                    file_put_contents($f, $c);
                }

                static::$scss->setImportPaths([$basedir]);
            }

            $actual = static::$scss->compile($scss);

            // clean after the test
            if ($includes) {
                static::$scss->setImportPaths([]);
                passthru("rm -fR $basedir");
            }

            $this->assertEquals(rtrim($css), rtrim($actual), $name);
            // TODO : warning?
        }
    }

    protected function reformatOutput($css)
    {
        $css = str_replace("}\n\n", "}\n", $css);
        $css = str_replace(",\n", ", ", $css);

        return $css;
    }

    /**
     * @return array
     */
    public function provideTests()
    {
        $dir    = dirname(__DIR__) . '/vendor/sass/sass-spec/spec';
        $specs  = [];
        $subdir = '';

        for ($depth = 0; $depth < 7; $depth++) {
            $specs = array_merge($specs, glob($dir . $subdir . '/*.hrx'));
            $subdir .= '/*';
        }

        $tests = [];
        $skippedTests = [];

        foreach ($specs as $fileName) {
            $spec         = file_get_contents($fileName);
            $fileName     = substr($fileName, strlen($dir) +1);
            $baseTestName = substr($fileName, 0, -4);
            $subTests     = explode(
                '================================================================================',
                $spec
            );

            foreach ($subTests as $subTest) {
                $subNname  = '';
                $input     = '';
                $output    = '';
                $options   = '';
                $error     = '';
                $warning   = '';
                $includes  = [];
                $hasInput  = false;
                $hasOutput = false;

                $parts = explode('<===>', $subTest);

                foreach ($parts as $part) {
                    $part   = ltrim($part, ' ');
                    $part   = explode("\n", $part);
                    $first  = array_shift($part);
                    $part   = implode("\n", $part);
                    $subDir = dirname($first);

                    if ($subDir == '.') {
                        $subDir = '';
                    }

                    $what = basename($first);

                    switch ($what) {
                        case 'options.yml':
                            if (! $subNname && $subDir) {
                                $subNname = '/' . $subDir;
                            }

                            $options = $part;
                            break;

                        case 'input.scss':
                            if (! $subNname && $subDir) {
                                $subNname = '/' . $subDir;
                            }

                            $hasInput = true;
                            $input = $part;
                            break;

                        case 'output.css':
                            if (! $hasOutput) {
                                $output = $this->reformatOutput($part);
                                $hasOutput = true;
                            }
                            break;

                        case 'output-libsass.css':
                            $output = $this->reformatOutput($part);
                            $hasOutput = true;
                            break;

                        case 'output-dart-sass.css':
                            // ignore output-dart-sass
                            break;

                        case 'error':
                            $error = $part;
                            break;

                        case 'warning':
                            $warning = $part;
                            break;

                        default:
                            if ($what && substr($what, -5) === '.scss') {
                                $includes[$first] = $part;
                            }
                            break;
                    }
                }

                $sizeLimit = 1024 * 1024;
                $test = [$baseTestName . $subNname, [$options, $input, $includes], [$output, $warning, $error]];
                if (! $hasInput ||
                    (!$hasOutput && ! $error) ||
                    strpos($options, ':todo:') !== false ||
                    strpos($baseTestName, 'libsass-todo-tests') !== false ||
                    strlen($input) > $sizeLimit
                ) {
                    $skippedTests[] = $test;
                } else {
                    $tests[] = $test;
                }
            }
        }

        $nb_tests = count($tests);

        foreach ($tests as $k => $test) {
            $rang = ($k+1) . "/" . $nb_tests . '. ';
            $tests[$k][0] = $rang . $tests[$k][0];
        }

        //var_dump($skippedTests);
        return $tests;
    }
}
