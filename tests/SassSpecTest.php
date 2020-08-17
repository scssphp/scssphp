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

/**
 * Sass Spec Test - extracts tests from https://github.com/sass/sass-spec
 *
 * @author Cerdic <cedric@yterium.com>
 */
class SassSpecTest extends TestCase
{
    protected static $scss;
    protected static $exclusionList;
    protected static $warningExclusionList;

    protected static $fileExclusionList = __DIR__ . '/specs/sass-spec-exclude.txt';
    protected static $fileWarningExclusionList = __DIR__ . '/specs/sass-spec-exclude-warning.txt';

    /**
     * List of excluded tests if not in TEST_SCSS_COMPAT mode
     *
     * @return string
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
     * Check the presence of a test in an exclusion list
     * @param $testName
     * @param $exclusionList
     * @return bool
     */
    protected function matchExclusionList($testName, $exclusionList) {
        $exclusionListString = implode("\n", $exclusionList) . "\n";
        $testName = preg_replace(",^\d+/\d+\.\s*,", "", $testName);
        if (strpos($exclusionListString, ". $testName\n")) {
            return true;
        }
        return false;
    }

    /**
     * List of tests excluding the assertion on warnings if not in TEST_SCSS_COMPAT mode
     *
     * @return array
     */
    protected function getWarningExclusionList()
    {
        if (is_null(static::$warningExclusionList)) {
            if (!file_exists(static::$fileWarningExclusionList)) {
                static::$warningExclusionList = [];
            } else {
                static::$warningExclusionList = file(static::$fileWarningExclusionList);
                static::$warningExclusionList = array_map('trim', static::$warningExclusionList);
                static::$warningExclusionList = array_filter(static::$warningExclusionList);
            }
        }

        return static::$warningExclusionList;
    }

    /**
     * RAZ the file that lists excluded tests
     *
     * @return array
     */
    protected function resetExclusionList()
    {
        static::$exclusionList = [];
        static::$warningExclusionList = [];
        file_put_contents(static::$fileExclusionList, '');
        file_put_contents(static::$fileWarningExclusionList, '');

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
     * Append a test name to the list of excluded tests
     *
     * @return array
     */
    protected function appendToWarningExclusionList($testName)
    {
        static::$warningExclusionList[] = $testName;
        file_put_contents(static::$fileWarningExclusionList, implode("\n", static::$warningExclusionList) . "\n");

        return static::$warningExclusionList;
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
        if (is_null(static::$scss)) {
            @ini_set('memory_limit', "256M");

            static::$scss = new Compiler();
            static::$scss->setFormatter('ScssPhp\ScssPhp\Formatter\Expanded');
        }

        if (! getenv('TEST_SASS_SPEC') && $this->matchExclusionList($name, $this->getExclusionList())) {
            $this->markTestSkipped('Define TEST_SASS_SPEC=1 to enable all sass-spec compatibility tests');

            return;
        }

        list($options, $scss, $includes) = $input;
        list($css, $warning, $error) = $output;
        // short colors are expanded for comparison purpose
        $css = preg_replace(",#([0-9a-f])([0-9a-f])([0-9a-f])\b,i", "#\\1\\1\\2\\2\\3\\3", $css);

        // ignore tests expecting an error for the moment
        if (! strlen($error)) {
            $fp_err_stream = fopen("php://memory", 'r+');
            static::$scss->setErrorOuput($fp_err_stream);

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
                    $this->assertNull(null);
                    return;
                    //throwException($e);
                }
            } else {
                $actual = static::$scss->compile($scss);
            }

            // short colors are expanded for comparison purpose
            $actual = preg_replace(",#([0-9a-f])([0-9a-f])([0-9a-f])\b,i", "#\\1\\1\\2\\2\\3\\3", $actual);

            // Get the warnings/errors
            rewind($fp_err_stream);
            $output = stream_get_contents($fp_err_stream);
            fclose($fp_err_stream);

            // clean after the test
            if ($includes) {
                static::$scss->setImportPaths([]);
                passthru("rm -fR $basedir");
            }

            if (getenv('BUILD')) {
                if (rtrim($css) !== rtrim($actual)) {
                    $this->appendToExclusionList($name);
                } elseif ($warning && rtrim($output) !== rtrim($warning)) {
                    $this->appendToWarningExclusionList($name);
                }
                $this->assertNull(null);
            } else {
                $this->assertEquals(rtrim($css), rtrim($actual), $name);

                if ($warning) {
                    if (getenv('TEST_SASS_SPEC') || !$this->matchExclusionList($name, $this->getWarningExclusionList())) {
                        $this->assertEquals(rtrim($warning), rtrim($output));
                    }
                }
            }
        } else {
            if (getenv('BUILD')) {
                $this->appendToExclusionList($name);
                $this->assertNull(null);
            } else {
                $this->markTestSkipped('Specs expecting an error are not supported for now.');
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
                $baseDir = '';

                $parts = explode('<===>', $subTest);

                foreach ($parts as $part) {
                    $part   = explode("\n", $part);
                    $first  = array_shift($part);
                    $first  = ltrim($first, ' ');
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

                            $baseDir = $subDir;
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
                            if ($what && (substr($what, -5) === '.scss' || substr($what, -4) === '.css')) {
                                $includes[$first] = $part;
                            }
                            break;
                    }
                }

                if ($baseDir and $includes) {
                    $tempIncludes = $includes;
                    $includes = [];

                    foreach ($tempIncludes as $k => $v) {
                        if (strpos($k, "$baseDir/") === 0) {
                            $k = substr($k, strlen("$baseDir/"));
                        }

                        $includes[$k] = $v;
                    }
                }

                // exception : a lot of spec test have an _assert_helpers.scss besides the .hrx
                // and are using an @import('../assert_helpers') withour any declaration in the hrx
                if (file_exists($f = $fileDir . '/_assert_helpers.scss')) {
                    $includes['../_assert_helpers.scss'] = file_get_contents($f);
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
