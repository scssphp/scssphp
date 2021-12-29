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
use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\Logger\StreamLogger;

/**
 * Sass Spec Test - extracts tests from https://github.com/sass/sass-spec
 *
 * @author Cerdic <cedric@yterium.com>
 */
class SassSpecTest extends TestCase
{
    private static $exclusionList;
    private static $warningExclusionList;

    private static $fileExclusionList = __DIR__ . '/specs/sass-spec-exclude.txt';
    private static $fileWarningExclusionList = __DIR__ . '/specs/sass-spec-exclude-warning.txt';
    private $dirToClean;

    /**
     * @after
     */
    protected function cleanDirection()
    {
        if (!$this->dirToClean) {
            return;
        }

        self::removeDirectory($this->dirToClean);
    }

    protected function sassSpecDir()
    {
        return dirname(__DIR__) . '/vendor/sass/sass-spec/spec';
    }

    /**
     * List of excluded tests if not in TEST_SASS_SPEC mode
     *
     * @return string
     */
    protected function getExclusionList()
    {
        if (is_null(self::$exclusionList)) {
            if (!file_exists(self::$fileExclusionList)) {
                self::$exclusionList = [];
            } else {
                self::$exclusionList = file(self::$fileExclusionList);
                self::$exclusionList = array_map('trim', self::$exclusionList);
                self::$exclusionList = array_filter(self::$exclusionList);
            }
        }

        return self::$exclusionList;
    }

    /**
     * Remove order/total. prefix from testName
     * @param $testName
     */
    protected function canonicalTestName($testName)
    {
        $testName = preg_replace(",^\d+/\d+\.\s*,", "", $testName);
        return trim($testName);
    }

    /**
     * Check the presence of a test in an exclusion list
     * @param $testName
     * @param $exclusionList
     * @return bool
     */
    protected function matchExclusionList($testName, $exclusionList)
    {
        if (in_array($this->canonicalTestName($testName), $exclusionList)) {
            return true;
        }
        return false;
    }

    /**
     * List of tests excluding the assertion on warnings if not in TEST_SASS_SPEC mode
     *
     * @return array
     */
    protected function getWarningExclusionList()
    {
        if (is_null(self::$warningExclusionList)) {
            if (!file_exists(self::$fileWarningExclusionList)) {
                self::$warningExclusionList = [];
            } else {
                self::$warningExclusionList = file(self::$fileWarningExclusionList);
                self::$warningExclusionList = array_map('trim', self::$warningExclusionList);
                self::$warningExclusionList = array_filter(self::$warningExclusionList);
            }
        }

        return self::$warningExclusionList;
    }

    /**
     * RAZ the file that lists excluded tests
     *
     * @return array
     */
    protected function resetExclusionList()
    {
        self::$exclusionList = [];
        self::$warningExclusionList = [];
        file_put_contents(self::$fileExclusionList, '');
        file_put_contents(self::$fileWarningExclusionList, '');

        return self::$exclusionList;
    }

    /**
     * Append a test name to the list of excluded tests
     *
     * @return array
     */
    protected function appendToExclusionList($testName)
    {
        self::$exclusionList[] = $this->canonicalTestName($testName);
        file_put_contents(self::$fileExclusionList, implode("\n", self::$exclusionList) . "\n");

        return self::$exclusionList;
    }

    /**
     * Append a test name to the list of excluded tests
     *
     * @return array
     */
    protected function appendToWarningExclusionList($testName)
    {
        self::$warningExclusionList[] = $this->canonicalTestName($testName);
        file_put_contents(self::$fileWarningExclusionList, implode("\n", self::$warningExclusionList) . "\n");

        return self::$warningExclusionList;
    }

    /**
     * Do some normalization on css output, for comparison purpose
     * @param string $css
     * @return string
     */
    protected function normalizeCssOutput($css)
    {
        // short colors are expanded for comparison purpose
        $css = preg_replace(",#([0-9a-f])([0-9a-f])([0-9a-f])\b,i", "#\\1\\1\\2\\2\\3\\3", $css);
        return rtrim($css);
    }

    /**
     * Check if two CSS outputs are equivalent
     * ie equals (or differing only by quotes if $disallowQuoteDifference=false)
     * @param string $computed
     * @param string $spec
     * @param bool $disallowQuoteDifference
     * @return bool
     */
    protected function checkCssEqual(&$computed, $spec, $disallowQuoteDifference = false)
    {

        if ($computed === $spec) {
            return true;
        }

        if (!$disallowQuoteDifference) {
            if (strlen($computed) !== strlen($spec)) {
                return false;
            }

            $diffLeft = $diffRight = [];
            for ($i = 0; $i < strlen($computed); $i++) {
                if ($computed[$i] === $spec[$i]) {
                    continue;
                }
                if (!in_array($computed[$i], ["'", '"']) or !in_array($spec[$i], ["'", '"'])) {
                    return false;
                }
                if (
                        count($diffLeft)  and end($diffLeft) === $computed[$i]
                    and count($diffRight) and end($diffRight) === $spec[$i]
                ) {
                    array_pop($diffLeft);
                    array_pop($diffRight);
                } else {
                    $diffLeft[] = $computed[$i];
                    $diffRight[] = $spec[$i];
                }
            }

            if (!count($diffLeft) && !count($diffRight)) {
                $computed = $spec;
                return true;
            }
        }

        return false;
    }

    /**
     * @dataProvider provideTests
     */
    public function testTests($name, $input, $output)
    {
        // Increase the memory_limit to at least 256M to run these tests.
        // This code takes care of not lowering it.
        $memoryLimit = trim(ini_get('memory_limit'));
        if ($memoryLimit != -1) {
            $unit = strtolower(substr($memoryLimit, -1, 1));
            $memoryInBytes = (int) $memoryLimit;

            switch ($unit) {
                case 'g':
                    $memoryInBytes *= 1024;
                    // no break (cumulative multiplier)
                case 'm':
                    $memoryInBytes *= 1024;
                    // no break (cumulative multiplier)
                case 'k':
                    $memoryInBytes *= 1024;
            }

            if ($memoryInBytes < 256 * 1024 * 1024) {
                @ini_set('memory_limit', "256M");
            }
        }

        $compiler = new Compiler();

        list($options, $scss, $includes, $inputDir) = $input;
        list($css, $warning, $error, $alternativeCssOutputs, $alternativeWarnings) = $output;

        $fullInputs = $scss . "\n" . implode("\n", $includes);

        if (false !== strpos($fullInputs, '@forward ') || false !== strpos($fullInputs, '@use ')) {
            $this->markTestSkipped('Sass modules are not supported.');
        }

        if (! getenv('TEST_SASS_SPEC') && $this->matchExclusionList($name, $this->getExclusionList())) {
            $this->markTestSkipped('Define TEST_SASS_SPEC=1 to enable all sass-spec compatibility tests');

            return;
        }

        if (
            strpos($name, 'libsass-closed-issues/issue_1801/import-cycle') ||
            strpos($name, 'libsass-todo-issues/issue_1801/simple-import-loop') ||
            // The loop in issue_221260 is not technically infinite, but we go over the xdebug
            // max nesting level in our CI setup before detecting the Sass error.
            strpos($name, 'libsass-todo-issues/issue_221260') ||
            strpos($name, 'libsass-todo-issues/issue_221262') ||
            strpos($name, 'libsass-todo-issues/issue_221292')
        ) {
            $this->markTestSkipped('This test seems to cause an infinite loop.');
        }

        // normalize css for comparison purpose
        $css = $this->normalizeCssOutput($css);

        // build a dir with files and set the ImportPaths
        $basedir = sys_get_temp_dir() . '/sass-spec/' . preg_replace(",^\d+/\d+\.\s*,", "", $name);
        $this->dirToClean = $basedir;

        foreach ($includes as $f => $c) {
            $f = $basedir . '/' . $f;

            if (! is_dir(dirname($f))) {
                mkdir(dirname($f), 0777, true);
            }

            file_put_contents($f, $c);
        }
        if ($inputDir) {
            $basedir .= '/' . $inputDir;
        }
        if (!is_dir($basedir)) {
            mkdir($basedir, 0777, true);
        }

        $inputPath = $basedir.'/input.scss';
        file_put_contents($inputPath, $scss);

        // SassSpec use @import "core_functions/.../..."
        $compiler->addImportPath($this->sassSpecDir());

        $fp_err_stream = fopen("php://memory", 'r+');
        $compiler->setLogger(new StreamLogger($fp_err_stream));

        if (! strlen($error)) {
            if (getenv('BUILD')) {
                try {
                    $actual = $compiler->compileString($scss, $inputPath)->getCss();
                } catch (\Exception $e) {
                    $this->appendToExclusionList($name);
                    fclose($fp_err_stream);
                    $this->assertNull(null);
                    return;
                } catch (\Throwable $e) {
                    $this->appendToExclusionList($name);
                    fclose($fp_err_stream);
                    $this->assertNull(null);
                    return;
                    //throwException($e);
                }
            } else {
                $actual = $compiler->compileString($scss, $inputPath)->getCss();
            }

            // normalize css for comparison purpose
            $actual = $this->normalizeCssOutput($actual);

            // Get the warnings/errors
            rewind($fp_err_stream);
            $output = stream_get_contents($fp_err_stream);
            fclose($fp_err_stream);

            $disallowQuoteDifference = getenv('DISALLOW_QUOTE_DIFFERENCE');

            // if several outputs check if we match one alternative if not the first
            if (
                !$this->checkCssEqual($actual, $css, $disallowQuoteDifference)
                and $alternativeCssOutputs
            ) {
                foreach ($alternativeCssOutputs as $acss) {
                    $acss = $this->normalizeCssOutput($acss);
                    if ($this->checkCssEqual($actual, $acss, $disallowQuoteDifference)) {
                        $css = $acss;
                        break;
                    }
                }
            }

            if (rtrim($output) !== rtrim($warning) && $alternativeWarnings) {
                foreach ($alternativeWarnings as $alternativeWarning) {
                    if (rtrim($output) === rtrim($alternativeWarning)) {
                        $warning = $alternativeWarning;
                        break;
                    }
                }
            }

            if (getenv('BUILD')) {
                if ($css !== $actual) {
                    $this->appendToExclusionList($name);
                } elseif (rtrim($output) !== rtrim($warning)) {
                    $this->appendToWarningExclusionList($name);
                }
                $this->assertNull(null);
            } else {
                $this->assertEquals($css, $actual, $name);

                if (
                    getenv('TEST_SASS_SPEC') ||
                    ! $this->matchExclusionList($name, $this->getWarningExclusionList())
                ) {
                    $this->assertEquals(rtrim($warning), rtrim($output));
                }
            }
        } else {
            if (getenv('BUILD')) {
                try {
                    $compiler->compileString($scss, $inputPath);
                    throw new \Exception('Expecting a SassException for error tests');
                } catch (SassException $e) {
                    // TODO assert the error message ?
                    // Keep the test
                } catch (\Exception $e) {
                    $this->appendToExclusionList($name);
                } catch (\Throwable $e) {
                    $this->appendToExclusionList($name);
                }
                $this->assertNull(null);
            } else {
                $this->expectException(SassException::class);
                $compiler->compileString($scss, $inputPath);
                // TODO assert the error message ?
            }

            fclose($fp_err_stream);
        }
    }

    protected function reformatOutput($css)
    {
        $css = str_replace("}\n\n", "}\n", $css);
        $css = str_replace(",\n", ", ", $css);

        return $css;
    }

    private static function prepareWarning($warning, $baseTestName, $baseDir)
    {
        // Remove normalized absolute paths present in some warnings and errors
        // due to https://github.com/sass/libsass/issues/2861
        // Our own implementation always uses the expected relative path.
        $baseTestDir = dirname($baseTestName);
        $baseTestDir = preg_replace(
            '/(^|\/)libsass-[a-z]+-issues(\/|$)/',
            '$1libsass-issues$2',
            $baseTestDir
        );
        $warning = str_replace(
            rtrim("/sass/spec/$baseTestDir/$baseDir", '/') . '/',
            '',
            $warning
        );

        // Normalize paths in the output, as done by the official runner
        return preg_replace('/[-_\/a-zA-Z0-9]+(input\.s[ca]ss)/', '$1', $warning);
    }

    /**
     * @return array
     */
    public function provideTests()
    {
        $dir    = $this->sassSpecDir();
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
            $fileName     = substr($fileName, strlen($dir) + 1);
            $baseTestName = substr($fileName, 0, -4);
            $subTests     = explode(
                '================================================================================',
                $spec
            );

            $generalOptions = '';

            if (file_exists($f = $fileDir . '/options.yml') || file_exists($f = dirname($fileDir) . '/options.yml')) {
                $generalOptions = file_get_contents($f);
            }
            $globalIncludes  = [];

            foreach ($subTests as $subTest) {
                $subNname  = '';
                $input     = '';
                $includes  = [];
                $output    = '';
                $alternativeOutputs = [];
                $options   = '';
                $error     = '';
                $warning   = '';
                $alternativeWarnings = [];
                $hasInput  = false;
                $hasOutput = false;
                $baseDir = '';
                $hasSupportedInput = false;

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
                        case 'input.sass':
                            if (! $subNname && $subDir) {
                                $subNname = '/' . $subDir;
                            }
                            if ($hasInput) {
                                echo "{$baseTestName}{$subNname} double input.scss ? Skipping...\n";
                                continue 3; // 1 switch + 2 foreach
                            }

                            $baseDir = $subDir;
                            $hasInput = true;
                            $hasSupportedInput = $what === 'input.scss';
                            $input = $part;
                            break;

                        case 'output.css':
                            if (! $hasOutput) {
                                $output = $this->reformatOutput($part);
                                $hasOutput = true;
                            }
                            break;

                        case 'output-libsass.css':
                            $alternativeOutputs['libsass'] = $this->reformatOutput($part);
                            break;

                        case 'output-dart-sass.css':
                            $alternativeOutputs['dart-sass'] = $this->reformatOutput($part);
                            break;

                        case 'error':
                            $error = $part;
                            break;

                        case 'warning':
                            $warning = self::prepareWarning($part, $baseTestName, $baseDir);
                            break;

                        case 'warning-libsass':
                            $alternativeWarnings['libsass'] = self::prepareWarning($part, $baseTestName, $baseDir);
                            break;

                        case 'warning-dart-sass':
                            $alternativeWarnings['dart-sass'] = self::prepareWarning($part, $baseTestName, $baseDir);
                            break;

                        default:
                            if ($what && (substr($what, -5) === '.scss' || substr($what, -5) === '.sass' || substr($what, -4) === '.css')) {
                                if (strpos($first, '/') !== false) {
                                    $includes[$first] = $part;
                                } else {
                                    $globalIncludes[$first] = $part;
                                }
                            }
                            break;
                    }
                }

                $includes = array_merge($globalIncludes, $includes);

                if (!$hasOutput and count($alternativeOutputs)) {
                    $output = array_shift($alternativeOutputs);
                    $hasOutput = true;
                }

                if ($output && !empty($alternativeOutputs['dart-sass'])) {
                    $alternativeOutputs['default'] = $output;
                    $output = $alternativeOutputs['dart-sass'];
                    unset($alternativeOutputs['dart-sass']);
                }

                if (!$options && $generalOptions) {
                    $options = $generalOptions;
                }

                $sizeLimit = 1024 * 1024;
                $test = [
                    $baseTestName . $subNname,
                    [$options, $input, $includes, $baseDir],
                    [$output, $warning, $error, $alternativeOutputs, $alternativeWarnings]
                ];

                if ($hasInput && !$hasSupportedInput) {
                    // this is a test using the sass indented syntax for the input
                    $skippedTests[] = $test;
                } elseif (
                    ! $hasInput ||
                    (! $hasOutput && ! $error) ||
                    strlen($input) > $sizeLimit
                ) {
                    $skippedTests[] = $test;
                    // this is probably a include only section, so move them all to globalIncludes
                    $globalIncludes = $includes;
                } else {
                    $tests[$baseTestName . $subNname] = $test;
                }
            }
        }

        $nb_tests = count($tests);
        ksort($tests);
        $tests = array_values($tests);

        $testCases = array();

        foreach ($tests as $k => $test) {
            $testName = ($k + 1) . '/' . $nb_tests . '. ' . $test[0];
            $test[0] = $testName;
            $testCases[$testName] = $test;
        }

        //var_dump($skippedTests);
        return $testCases;
    }

    private static function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;

            if (is_link($path)) {
                unlink($path);
            } elseif (is_dir($path)) {
                self::removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
