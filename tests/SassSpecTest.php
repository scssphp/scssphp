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

    static $scss;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        if (is_null(static::$scss)) {
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
            if (! $init) {
                $init = true;

                $this->markTestSkipped('Define TEST_SASS_SPEC=1 to enable sass-spec compatibility tests');
            }

            return;
        }

        list($options, $scss) = $input;
        list($css, $warning, $error) = $output;

        // ignore tests expecting an error for the moment
        if (! strlen($error)) {
            $actual = static::$scss->compile($scss);
            $this->assertEquals(rtrim($css), rtrim($actual), $name);
            // TODO : warning?
        }
    }

    protected function reformatOutput($css) {
        $css = str_replace("}\n\n", "}\n", $css);
        return $css;
    }

    /**
     * @return array
     */
    public function provideTests()
    {
        $dir = dirname(__DIR__) . '/vendor/sass/sass-spec/spec';

        $specs = [];
        $subdir = '';
        for ($depth = 0; $depth < 7; $depth++) {
            $specs = array_merge( $specs, glob($dir . $subdir . '/*.hrx'));
            $subdir .= '/*';
        }

        $tests = [];
        $skippedTests = [];
        foreach ($specs as $fileName) {
            $spec = file_get_contents($fileName);
            $fileName = substr($fileName, strlen($dir) +1);
            $baseTestName = substr($fileName, 0, -4);

            $subTests = explode('================================================================================', $spec);
            foreach ($subTests as $subTest) {

                $subNname = '';
                $input = '';
                $output = '';
                $options = '';
                $error = '';
                $warning = '';
                $hasInput = false;
                $hasOutput = false;

                $parts = explode('<===> ', $subTest);
                foreach ($parts as $part) {
                    $part = explode("\n", $part);
                    $first = array_shift($part);
                    $part = implode("\n", $part);
                    $subDir = dirname($first);
                    if ($subDir == '.') {
                        $subDir = '';
                    }
                    if (! $subNname && $subDir) {
                        $subNname = '/' . $subDir;
                    }
                    $what = basename($first);
                    switch ($what) {
                        case 'options.yml':
                            $options = $part;
                            break;
                        case 'input.scss':
                            $hasInput = true;
                            $input = $part;
                            break;
                        case 'output.css':
                            $output = $this->reformatOutput($part);
                            $hasOutput = true;
                            break;
                        case 'error':
                            $error = $part;
                            break;
                        case 'warning':
                            $warning = $part;
                            break;
                    }
                }

                $sizeLimit = 1024 * 1024;
                $test = [$baseTestName . $subNname, [$options, $input], [$output, $warning, $error]];
                if (! $hasInput
                    || (!$hasOutput && ! $error)
                    || strpos($options, ':todo:') !== false
                    || strpos($baseTestName, 'libsass-todo-tests') !== false
                    || strlen($input) > $sizeLimit) {
                    $skippedTests[] = $test;
                }
                else {
                    $tests[] = $test;
                }
            }
        }

        $nb_tests = count($tests);
        foreach ($tests as $k=>$test) {
            $rang = ($k+1) . "/" . $nb_tests . '. ';
            $tests[$k][0] = $rang . $tests[$k][0];
        }

        //var_dump($skippedTests);
        return $tests;
    }
}
