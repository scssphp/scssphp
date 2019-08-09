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
    protected static $exclusionList;

    protected static $fileExclusionList = __DIR__ . '/specs/sass-spec-exclude.txt';

    /**
     * List of excluded tests if not in TEST_SCSS_COMPAT mode
     *
     * @return array
     */
    protected function getExclusionList()
    {
        if (is_null(static::$exclusionList)) {
            if (!file_exists(static::$fileExclusionList)) {
                static::$exclusionList = [];
            } else {
                static::$exclusionList = file(static::$fileExclusionList);
                static::$exclusionList = array_map('trim', static::$exclusionList);
                static::$exclusionList = array_filter(static::$exclusionList);
            }
        }

        return static::$exclusionList;
    }

    /**
     * RAZ the file that lists excluded tests
     *
     * @return array
     */
    protected function resetExclusionList()
    {
        static::$exclusionList = [];
        file_put_contents(static::$fileExclusionList, '');

        return static::$exclusionList;
    }

    /**
     * Append a test name to the list of excluded tests
     *
     * @return array
     */
    protected function appendToExclusionList($testName)
    {
        static::$exclusionList[] = $testName;
        file_put_contents(static::$fileExclusionList, implode("\n", static::$exclusionList) . "\n");

        return static::$exclusionList;
    }

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

        if (! getenv('TEST_SASS_SPEC') && in_array($name, $this->getExclusionList())) {
            $this->markTestSkipped('Define TEST_SASS_SPEC=1 to enable all sass-spec compatibility tests');

            return;
        }

        list($options, $scss, $includes) = $input;
        list($css, $warning, $error) = $output;

        // ignore tests expecting an error for the moment
        if (! strlen($error)) {
            $fp_err_stream = fopen("php://memory", 'r+');
            static::$scss->setErrorOuput($fp_err_stream);

            if ($warning) {
                $css = "STDERR::\n" . trim($warning) . "\n----------\n" . $css;
            }

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

            if (getenv('BUILD')) {
                try {
                    $actual = static::$scss->compile($scss);
                } catch (\Exception $e) {
                    $this->appendToExclusionList($name);
                    fclose($fp_err_stream);
                    return;
                    //throwException($e);
                }
            } else {
                $actual = static::$scss->compile($scss);
            }

            // Get the warnings/errors
            rewind($fp_err_stream);
            $output = stream_get_contents($fp_err_stream);
            fclose($fp_err_stream);

            if ($output) {
                $actual = "STDERR::\n" . trim($output) . "\n----------\n" . $actual;
            }

            // clean after the test
            if ($includes) {
                static::$scss->setImportPaths([]);
                passthru("rm -fR $basedir");
            }

            if (getenv('BUILD')) {
                if (rtrim($css) !== rtrim($actual)) {
                    $this->appendToExclusionList($name);
                }
            } else {
                $this->assertEquals(rtrim($css), rtrim($actual), $name);
            }
        } else {
            if (getenv('BUILD')) {
                $this->appendToExclusionList($name);
            }
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
        $dir    = __DIR__ . '/specs/sass-spec/spec';
        $specs  = [];
        $subdir = '';

        if (getenv('BUILD')) {
            $this->resetExclusionList();
        }


        for ($depth = 0; $depth < 7; $depth++) {
            $specs = array_merge($specs, glob($dir . $subdir . '/*.hrx'));
            $subdir .= '/*';
        }

        $tests = [];
        $skippedTests = [];

        foreach ($specs as $fileName) {
            $spec         = file_get_contents($fileName);
            $fileDir      = dirname($fileName);
            $fileName     = substr($fileName, strlen($dir) +1);
            $baseTestName = substr($fileName, 0, -4);
            $subTests     = explode(
                '================================================================================',
                $spec
            );

            $generalOptions = '';
            if (file_exists($f = $fileDir . '/options.yml') || file_exists($f = dirname($fileDir) . '/options.yml')) {
                $generalOptions = file_get_contents($f);
            }


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
                    $part   = explode("\n", $part);
                    $first  = array_shift($part);
                    $first   = ltrim($first, ' ');
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

                if (!$options && $generalOptions) {
                    $options = $generalOptions;
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
                    $tests[$baseTestName . $subNname] = $test;
                }
            }
        }

        $nb_tests = count($tests);
        ksort($tests);
        $tests = array_values($tests);

        foreach ($tests as $k => $test) {
            $rang = ($k+1) . "/" . $nb_tests . '. ';
            $tests[$k][0] = $rang . $tests[$k][0];
        }

        //var_dump($skippedTests);
        return $tests;
    }
}
