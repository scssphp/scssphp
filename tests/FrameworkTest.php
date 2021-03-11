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

        $result = $compiler->compile(file_get_contents($entrypoint), $entrypoint);

        $this->assertNotEmpty($result->getCss());
    }

    public function testFoundation()
    {
        $compiler = new Compiler();
        $compiler->addImportPath(dirname(__DIR__) . '/vendor/zurb/foundation/scss');
        $compiler->setLogger(new QuietLogger());

        // The Foundation entrypoint only define mixins. To get a useful compilation
        // executing their code, we need to actually use the mixin.
        $scss = <<<'SCSS'
@import "settings/settings";
@import "foundation";

@include foundation-everything;
SCSS;

        $result = $compiler->compile($scss);

        $this->assertNotEmpty($result->getCss());
    }
}
