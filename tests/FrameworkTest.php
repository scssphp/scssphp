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

class FrameworkTest extends TestCase
{
    protected static $frameworks = [
        [
            "frameworkVersion" => "twbs/bootstrap4.3",
            "inputdirectory" => "../vendor/twbs/bootstrap/scss/",
            "inputfiles" => "bootstrap.scss",
        ],
        [
            "frameworkVersion" => "zurb/foundation6.5",
            "inputdirectory" => "../vendor/zurb/foundation/assets/",
            "inputfiles" => "foundation.scss",
        ],
    ];

    /**
     * @dataProvider frameworkProvider
     */
    public function testFramework($frameworkVersion, $inputdirectory, $inputfiles)
    {
        chdir(__DIR__);

        $scss = new Compiler();
        $scss->addImportPath(__DIR__ . '/' . $inputdirectory);

        $input = file_get_contents(__DIR__ . '/' . $inputdirectory . $inputfiles);

        // Test if no exceptions are raised for the given framework
        $e = null;

        try {
            $scss->compile($input, $inputfiles);
        } catch (\Exception $e) {
            // test fail
        }

        $this->assertNull($e);
    }

    public function frameworkProvider()
    {
        return self::$frameworks;
    }
}
