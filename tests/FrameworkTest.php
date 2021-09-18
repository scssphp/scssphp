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

class FrameworkTest extends TestCase
{
    public function testBootstrap()
    {
        $compiler = new Compiler();
        $compiler->setLogger(new QuietLogger());

        $entrypoint = dirname(__DIR__) . '/vendor/twbs/bootstrap/scss/bootstrap.scss';

        $result = $compiler->compileString(file_get_contents($entrypoint), $entrypoint);

        $this->assertNotEmpty($result->getCss());
    }

    public function testBootstrap4()
    {
        $compiler = new Compiler();
        $compiler->setLogger(new QuietLogger());

        $entrypoint = dirname(__DIR__) . '/vendor/twbs/bootstrap4/scss/bootstrap.scss';

        $result = $compiler->compileString(file_get_contents($entrypoint), $entrypoint);

        $this->assertNotEmpty($result->getCss());
    }

    public function testBootstrap4CustomSettings()
    {
        $compiler = new Compiler();
        $compiler->addImportPath(dirname(__DIR__) . '/vendor/twbs/bootstrap4/scss');
        $compiler->setLogger(new QuietLogger());

        $scss = <<<'SCSS'
$enable-shadows: true;
$enable-gradients: true;

@import "bootstrap";
SCSS;

        $result = $compiler->compileString($scss);

        $this->assertNotEmpty($result->getCss());
    }

    public function testFoundation()
    {
        $compiler = new Compiler();
        $compiler->setLogger(new QuietLogger());

        $entrypoint = dirname(__DIR__) . '/vendor/zurb/foundation/assets/foundation.scss';

        $result = $compiler->compileString(file_get_contents($entrypoint), $entrypoint);

        $this->assertNotEmpty($result->getCss());
    }
}
