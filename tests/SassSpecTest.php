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
use ScssPhp\ScssPhp\Deprecation;
use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\Logger\StreamLogger;

/**
 * Sass Spec Test - extracts tests from https://github.com/sass/sass-spec
 *
 * @author Cerdic <cedric@yterium.com>
 */
class SassSpecTest extends TestCase
{
    private const EXCLUSION_LIST_FILE = __DIR__ . '/specs/sass-spec-exclude.txt';
    private const WARNING_EXCLUSION_LIST_FILE = __DIR__ . '/specs/sass-spec-exclude-warning.txt';

    /**
     * @var string[]|null
     */
    private static ?array $exclusionList = null;
    /**
     * @var string[]|null
     */
    private static ?array $warningExclusionList = null;

    private ?string $dirToClean = null;

    private ?string $oldCwd = null;

    /**
     * @after
     */
    protected function restoreCwd(): void
    {
        if ($this->oldCwd === null) {
            return;
        }

        chdir($this->oldCwd);
    }

    /**
     * @after
     */
    protected function cleanDirectory(): void
    {
        if (!$this->dirToClean) {
            return;
        }

        self::removeDirectory($this->dirToClean);
    }

    private function sassSpecDir(): string
    {
        return dirname(__DIR__) . '/vendor/sass/sass-spec/spec';
    }

    /**
     * List of excluded tests if not in TEST_SASS_SPEC mode
     *
     * @return string[]
     */
    private function getExclusionList(): array
    {
        if (is_null(self::$exclusionList)) {
            if (!file_exists(self::EXCLUSION_LIST_FILE)) {
                self::$exclusionList = [];
            } else {
                self::$exclusionList = array_filter(array_map('trim', file(self::EXCLUSION_LIST_FILE)));
            }
        }

        return self::$exclusionList;
    }

    /**
     * Remove order/total. prefix from testName
     */
    private function canonicalTestName(string $testName): string
    {
        $testName = preg_replace(",^\d+/\d+\.\s*,", "", $testName);
        return trim($testName);
    }

    /**
     * Checks the presence of a test in an exclusion list.
     *
     * @param string[] $exclusionList
     */
    private function matchExclusionList(string $testName, array $exclusionList): bool
    {
        if (in_array($this->canonicalTestName($testName), $exclusionList)) {
            return true;
        }
        return false;
    }

    /**
     * List of tests excluding the assertion on warnings if not in TEST_SASS_SPEC mode
     *
     * @return string[]
     */
    private function getWarningExclusionList(): array
    {
        if (is_null(self::$warningExclusionList)) {
            if (!file_exists(self::WARNING_EXCLUSION_LIST_FILE)) {
                self::$warningExclusionList = [];
            } else {
                self::$warningExclusionList = array_filter(array_map('trim', file(self::WARNING_EXCLUSION_LIST_FILE)));
            }
        }

        return self::$warningExclusionList;
    }

    /**
     * RAZ the file that lists excluded tests
     */
    private function resetExclusionList(): void
    {
        self::$exclusionList = [];
        self::$warningExclusionList = [];
        file_put_contents(self::EXCLUSION_LIST_FILE, '');
        file_put_contents(self::WARNING_EXCLUSION_LIST_FILE, '');
    }

    /**
     * Append a test name to the list of excluded tests
     */
    private function appendToExclusionList(string $testName): void
    {
        self::$exclusionList[] = $this->canonicalTestName($testName);
        file_put_contents(self::EXCLUSION_LIST_FILE, implode("\n", self::$exclusionList) . "\n");
    }

    /**
     * Append a test name to the list of excluded tests
     */
    private function appendToWarningExclusionList(string $testName): void
    {
        self::$warningExclusionList[] = $this->canonicalTestName($testName);
        file_put_contents(self::WARNING_EXCLUSION_LIST_FILE, implode("\n", self::$warningExclusionList) . "\n");
    }

    /**
     * Do some normalization on css output, for comparison purpose
     *
     * @param string $css
     * @return string
     */
    private static function normalizeOutput(string $css): string
    {
        $css = preg_replace('/(\r?\n)+/', "\n", $css);
        $css = preg_replace('/[-_\/a-zA-Z0-9]+(input\.s[ca]ss)/', '$1', $css);

        return rtrim($css);
    }

    /**
     * @dataProvider provideTests
     */
    public function testTests(string $name, array $input, array $testCaseOutput): void
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
        $compiler->setVerbose(true);
        $compiler->setSilenceDeprecations([Deprecation::mixedDecls]);

        list($options, $scss, $includes, $inputDir, $indented) = $input;
        list($css, $warning, $error, $alternativeCssOutputs, $alternativeWarnings) = $testCaseOutput;

        if (preg_match('/:(todo|ignore_for):\r?\n *+- dart-sass\r?\n/', $options)) {
            self::markTestSkipped('This test does not apply to dart-sass so it does not apply to our port either.');
        }

        $fullInputs = $scss . "\n" . implode("\n", $includes);

        if (str_contains($fullInputs, '@forward ') || str_contains($fullInputs, '@use ')) {
            $this->markTestSkipped('Sass modules are not supported.');
        }

        if (! getenv('TEST_SASS_SPEC') && $this->matchExclusionList($name, $this->getExclusionList())) {
            $this->markTestSkipped('Define TEST_SASS_SPEC=1 to enable all sass-spec compatibility tests');
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
        $css = self::normalizeOutput($css);

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

        $inputPath = $basedir . ($indented ? '/input.sass' : '/input.scss');
        file_put_contents($inputPath, $scss);

        // The display of pretty paths depends on the current working directory.
        // The sass-spec runner uses the base directory of the test as working directory.
        $this->oldCwd = getcwd() ?: null;
        chdir($basedir);

        // SassSpec use @import "core_functions/.../..."
        $compiler->addImportPath($this->sassSpecDir());

        $fp_err_stream = fopen("php://memory", 'r+');
        $compiler->setLogger(new StreamLogger($fp_err_stream));

        if (! strlen($error)) {
            if (getenv('BUILD')) {
                try {
                    $actual = $compiler->compileFile($inputPath)->getCss();
                } catch (\Throwable) {
                    $this->appendToExclusionList($name);
                    fclose($fp_err_stream);
                    $this->assertNull(null);
                    return;
                }
            } else {
                $actual = $compiler->compileFile($inputPath)->getCss();
            }

            // normalize css for comparison purpose
            $actual = self::normalizeOutput($actual);

            // Get the warnings/errors
            rewind($fp_err_stream);
            $output = stream_get_contents($fp_err_stream);
            fclose($fp_err_stream);
            $output = self::normalizeOutput($output);

            // if several outputs check if we match one alternative if not the first
            if (
                $actual !== $css
                && $alternativeCssOutputs
            ) {
                foreach ($alternativeCssOutputs as $alternativeCss) {
                    $alternativeCss = self::normalizeOutput($alternativeCss);
                    if ($actual === $alternativeCss) {
                        $css = $alternativeCss;
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
                    $compiler->compileFile($inputPath);
                    throw new \Exception('Expecting a SassException for error tests');
                } catch (SassException) {
                    // TODO assert the error message ?
                    // Keep the test
                } catch (\Throwable) {
                    $this->appendToExclusionList($name);
                }
                $this->assertNull(null);
            } else {
                $this->expectException(SassException::class);
                $compiler->compileFile($inputPath);
                // TODO assert the error message ?
            }

            fclose($fp_err_stream);
        }
    }

    private static function prepareWarning(string $warning, string $baseTestName, string $baseDir): string
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

        return self::normalizeOutput($warning);
    }

    public function provideTests(): iterable
    {
        $dir = $this->sassSpecDir();
        $specs = [];
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
            $spec = file_get_contents($fileName);
            $fileDir = dirname($fileName);
            $fileName = substr($fileName, strlen($dir) + 1);
            $baseTestName = substr($fileName, 0, -4);
            $subTests = explode(
                '================================================================================',
                $spec
            );

            $generalOptions = '';

            if (file_exists($f = $fileDir . '/options.yml') || file_exists($f = dirname($fileDir) . '/options.yml')) {
                $generalOptions = file_get_contents($f);
            }
            $globalIncludes  = [];

            foreach ($subTests as $subTest) {
                $subNname = '';
                $input = '';
                $includes = [];
                $output = '';
                $alternativeOutputs = [];
                $options = '';
                $error = '';
                $warning = '';
                $alternativeWarnings = [];
                $hasInput = false;
                $hasOutput = false;
                $baseDir = '';
                $indented = false;

                $parts = explode('<===>', $subTest);

                foreach ($parts as $part) {
                    $partLines = explode("\n", $part);
                    $first = array_shift($partLines);
                    $first = ltrim($first, ' ');
                    $part = implode("\n", $partLines);
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
                            $indented = $what === 'input.sass';
                            $input = $part;
                            break;

                        case 'output.css':
                            if (! $hasOutput) {
                                $output = $part;
                                $hasOutput = true;
                            }
                            break;

                        case 'output-libsass.css':
                            $alternativeOutputs['libsass'] = $part;
                            break;

                        case 'output-dart-sass.css':
                            $alternativeOutputs['dart-sass'] = $part;
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
                            if ($what && (str_ends_with($what, '.scss') || str_ends_with($what, '.sass') || str_ends_with($what, '.css'))) {
                                if (str_contains($first, '/')) {
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
                    [$options, $input, $includes, $baseDir, $indented],
                    [$output, $warning, $error, $alternativeOutputs, $alternativeWarnings]
                ];

                if (
                    ! $hasInput ||
                    (! $hasOutput && ! $error) ||
                    strlen($input) > $sizeLimit
                ) {
                    $skippedTests[] = $test;
                    // this is probably an include only section, so move them all to globalIncludes
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

        if (getenv("DEBUG_SKIPPED")) {
            var_dump($skippedTests);
        }

        return $testCases;
    }

    private static function removeDirectory(string $dir): void
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
