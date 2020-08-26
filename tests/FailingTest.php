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
 * Failing tests
 *
 * {@internal
 *     Minimal tests as reported in github issues.
 * }}
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 */
class FailingTest extends TestCase
{
    /**
     * @param string $id
     * @param string $scss
     * @param string $expected
     *
     * @dataProvider provideFailing
     */
    public function testFailing($id, $scss, $expected)
    {
        static $init = false;

        if (! getenv('TEST_SCSS_COMPAT')) {
            $this->markTestSkipped('Define TEST_SCSS_COMPAT=1 to enable ruby scss compatibility tests');

            return;
        }

        $output = $this->compile($scss);

        $this->assertEquals(rtrim($expected), rtrim($output), $id);
    }

    /**
     * @return array
     */
    public function provideFailing()
    {
        // @codingStandardsIgnoreStart
        return [
/*************************************************************
            [
                '', <<<'END_OF_SCSS'
END_OF_SCSS
                , <<<END_OF_EXPECTED
END_OF_EXPECTED
            ],
*************************************************************/
        ];
        // @codingStandardsIgnoreEnd
    }

    private function compile($str)
    {
        $scss = new Compiler();

        return trim($scss->compile($str));
    }
}
