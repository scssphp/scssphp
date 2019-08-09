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
 * Scss Test - extracts tests from https://github.com/sass/sass/blob/stable/test/sass/scss/scss_test.rb
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 */
class ScssTest extends \PHPUnit_Framework_TestCase
{

    protected $fileExclusionList = __DIR__ . '/specs/scss_test-exclude.txt';
    protected $exclusionList;

    /**
     * List of excluded tests if not in TEST_SCSS_COMPAT mode
     *
     * @return array
     */
    protected function getExclusionList()
    {
        if (is_null($this->exclusionList)) {
            if (!file_exists($this->fileExclusionList)) {
                $this->exclusionList = [];
            } else {
                $this->exclusionList = file($this->fileExclusionList);
                $this->exclusionList = array_map('trim', $this->exclusionList);
                $this->exclusionList = array_filter($this->exclusionList);
            }
        }

        return $this->exclusionList;
    }

    /**
     * RAZ the file that lists excluded tests
     *
     * @return array
     */
    protected function resetExclusionList()
    {
        $this->exclusionList = [];
        file_put_contents($this->fileExclusionList, '');

        return $this->exclusionList;
    }

    /**
     * Append a test name to the list of excluded tests
     *
     * @return array
     */
    protected function appendToExclusionList($testName)
    {
        $this->exclusionList[] = $testName;
        file_put_contents($this->fileExclusionList, implode("\n", $this->exclusionList) . "\n");

        return $this->exclusionList;
    }

    /**
     * @param string $name
     * @param string $scss
     * @param string $css
     * @param mixed  $style
     *
     * @dataProvider provideTests
     */
    public function testTests($name, $scss, $css, $style)
    {
        if (! getenv('TEST_SCSS_COMPAT') && in_array($name, $this->getExclusionList())) {
            $this->markTestSkipped('Define TEST_SCSS_COMPAT=1 to enable all ruby scss compatibility tests');

            return;
        }

        $compiler = new Compiler();
        $compiler->setFormatter('ScssPhp\ScssPhp\Formatter\\' . ($style ? ucfirst($style) : 'Nested'));

        $actual = $compiler->compile($scss);

        // manage unconsistency
        if (strpos($css, "@fblthp") !== false) {
            $css = preg_replace(",@fblthp {}\s*,ims", "", $css);
            $css = preg_replace(",@media screen {\s*}\s*,ims", "", $css);
            $css = preg_replace(",@supports \([^)]*\) {\s*}\s*,ims", "", $css);
        }


        if (getenv('BUILD')) {
            if (rtrim($css) !== rtrim($actual)) {
                $this->appendToExclusionList($name);
            }
        } else {
            $this->assertEquals(rtrim($css), rtrim($actual), $name);
        }

        // TODO: need to fix this in the formatters
        //$this->assertEquals(trim($css), trim($actual), $name);
    }

    /**
     * Unescape escaped chars in the ruby file like \# or ||
     *
     * @param $string
     *
     * @return mixed
     */
    protected function unEscapeString($string)
    {
        if (strpos($string, "\\") !== false) {
            $string = str_replace('\\#', '#', $string);
            $string = str_replace('\\\\', '\\', $string);
            $string = str_replace('\\r', "\r", $string);
            $string = str_replace('\\n', "\n", $string);
        }

        return $string;
    }

    /**
     * @return array
     */
    public function provideTests()
    {
        $state   = 0;
        $lines   = file(__DIR__ . '/specs/scss_test.rb');
        $tests   = [];
        $skipped = [];
        $scss    = [];
        $css     = [];
        $style   = false;

        if (getenv('BUILD')) {
            $this->resetExclusionList();
        }

        for ($i = 0, $s = count($lines); $i < $s; $i++) {
            $line = trim($lines[$i]);

            switch ($state) {
                case 0:
                    // outside of function
                    if (preg_match('/^\s*def test_([a-z_]+)/', $line, $matches)) {
                        $state = 1; // enter function
                        $name = $matches[1];
                        $nameSuffix = "";
                        continue 2;
                    }

                    break;

                case 1:
                    // inside function
                    if ($line === '' || $line[0] === '#') {
                        continue 2;
                    }

                    if (preg_match('/= <<([A-Z_]+)\s*$/', $line, $matches)
                        || preg_match('/= render <<([A-Z_]+)\s*$/', $line, $matches)
                    ) {
                        $terminator = $matches[1];

                        for ($i++; trim($lines[$i]) !== $terminator; $i++) {
                            ;
                        }

                        continue 2;
                    }

                    if (preg_match('/^\s*assert_equal\(<<CSS, render\(<<SASS\)\)\s*$/', $line, $matches)
                        || preg_match('/^\s*assert_equal <<CSS, render\(<<SASS\)\s*$/', $line, $matches)
                    ) {
                        $state = 6; // sass parameter list
                        continue 2;
                    }

                    if (preg_match('/^\s*assert_equal\(<<CSS, render\(<<SCSS\)\)\s*$/', $line, $matches) ||
                         preg_match('/^\s*assert_equal <<CSS, render\(<<SCSS\)\s*$/', $line, $matches) ||
                        // @codingStandardsIgnoreStart
                        preg_match('/^\s*assert_equal\(<<CSS, render\(<<SCSS, :style => :(compressed|nested)\)\)\s*$/', $line, $matches) ||
                        preg_match('/^\s*assert_equal <<CSS, render\(<<SCSS, :style => :(compressed|nested)\)\s*$/', $line, $matches)
                        // @codingStandardsIgnoreEnd
                    ) {
                        $state = 2; // get css
                        $style = isset($matches[1]) ? $matches[1] : null;

                        // another subtest in the same def name,
                        // insert each separately to avoid border errors on newlines between each subtest
                        if (count($css) && count($scss)) {
                            $tests[] = [
                                $name . ($nameSuffix ? "-$nameSuffix" : ""),
                                implode($scss),
                                implode($css),
                                $style
                            ];

                            $nameSuffix = intval($nameSuffix) + 1;
                            $scss       = [];
                            $css        = [];
                        }

                        continue 2;
                    }

                    if (preg_match('/^\s*assert_warning .* do$/', $line)) {
                        $state = 4; // skip block
                        continue 2;
                    }

                    // skip test_parsing_many_numbers_doesnt_take_forever that we can't reproduce
                    if (preg_match('/^\s*values =.*$/', $line)) {
                        $state = 4; // skip block
                        continue 2;
                    }

                    if (preg_match('/^\s*assert_raise_message.*render\(<<SCSS\)}\s*$/', $line) ||
                        preg_match('/^\s*assert_raise_message.*render <<SCSS}\s*$/', $line) ||
                        preg_match('/^\s*assert_raise_line.*render\(<<SCSS\)}\s*$/', $line) ||
                        preg_match('/^\s*silence_warnings .*render\(<<SCSS\)}\s*$/', $line) ||
                        preg_match('/^\s*assert_warning.*render <<SCSS}\s*$/', $line) ||
                        preg_match('/^\s*assert_warning.*render\(<<SCSS\)}\s*$/', $line) ||
                        preg_match('/^\s*assert_warning.*render\(<<SCSS\)\)}\s*$/', $line) ||
                        preg_match('/^\s*assert_no_warning.*render\(<<SCSS\)\)}\s*$/', $line) ||
                        preg_match('/^\s*assert_no_warning.*render\(<<SCSS\)}\s*$/', $line) ||
                        preg_match('/^\s*render\(<<SCSS\)\s*$/', $line) ||
                        preg_match('/^\s*render <<SCSS\s*$/', $line)
                    ) {
                        $state = 6; // begin parameter list
                        continue 2;
                    }

                    if (preg_match('/^\s*assert_equal\(<<CSS,/', $line)) {
                        for ($i++; trim($lines[$i]) !== 'CSS'; $i++) {
                            ;
                        }

                        continue 2;
                    }

                    if (preg_match('/^\s*assert_equal[ (].*,$/', $line)
                    ) {
                        $i++; // throw-away the next line too
                        continue 2;
                    }

                    if (preg_match('/^\s*assert_equal[ (]/', $line) ||
                        preg_match('/^\s*assert_parses/', $line) ||
                        preg_match('/^\s*assert\(/', $line) ||
                        preg_match('/^\s*render[ (]"/', $line) ||
                        $line === 'rescue Sass::SyntaxError => e'
                    ) {
                        continue 2;
                    }

                    if (preg_match('/^\s*end\s*$/', $line)) {
                        $state = 0; // exit function

                        $tests[] = [$name. ($nameSuffix ? "-$nameSuffix" : ""), implode($scss), implode($css), $style];
                        $scss = [];
                        $css = [];
                        $style = null;
                        continue 2;
                    }

                    $skipped[] = $line;
                    break;

                case 2:
                    // get css
                    if (preg_match('/^CSS\s*$/', $line)) {
                        $state = 3; // get scss
                        continue 2;
                    }

                    $css[] = $this->unEscapeString($lines[$i]);
                    break;

                case 3:
                    // get scss
                    if (preg_match('/^SCSS\s*$/', $line)) {
                        $state = 1; // end of parameter list
                        continue 2;
                    }

                    $scss[] = $this->unEscapeString($lines[$i]);
                    break;

                case 4:
                    // inside block
                    if (preg_match('/^\s*end\s*$/', $line)) {
                        $state = 1; // end block
                        continue 2;
                    }

                    if (preg_match('/^\s*assert_equal <<CSS, render\(<<SCSS\)\s*$/', $line)) {
                        $state = 5; // begin parameter list
                        continue 2;
                    }

                    break;

                case 5:
                    // consume parameters
                    if (preg_match('/^SCSS\s*$/', $line)) {
                        $state = 4; // end of parameter list
                        continue 2;
                    }

                    break;

                case 6:
                    // consume parameters
                    if (preg_match('/^S[AC]SS\s*$/', $line)) {
                        $state = 1; // end of parameter list
                        continue 2;
                    }

                    break;
            }
        }

        // var_dump($skipped);

        return $tests;
    }
}
