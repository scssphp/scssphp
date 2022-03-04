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

/**
 * @group frameworks
 */
class FrameworkTest extends TestCase
{
    public function testBootstrap()
    {
        $compiler = new Compiler();
        $compiler->setLogger(new QuietLogger());
        $compiler->setSourceMap(Compiler::SOURCE_MAP_INLINE);

        $entrypoint = dirname(__DIR__) . '/vendor/twbs/bootstrap/scss/bootstrap.scss';

        $result = $compiler->compileString(file_get_contents($entrypoint), $entrypoint);

        $this->assertNotEmpty($result->getCss());
    }

    public function testBootstrap4()
    {
        $compiler = new Compiler();
        $compiler->setLogger(new QuietLogger());
        $compiler->setSourceMap(Compiler::SOURCE_MAP_INLINE);

        $entrypoint = dirname(__DIR__) . '/vendor/twbs/bootstrap4/scss/bootstrap.scss';

        $result = $compiler->compileString(file_get_contents($entrypoint), $entrypoint);

        $this->assertNotEmpty($result->getCss());
    }

    public function testBootstrap4CustomSettings()
    {
        $compiler = new Compiler();
        $compiler->addImportPath(dirname(__DIR__) . '/vendor/twbs/bootstrap4/scss');
        $compiler->setLogger(new QuietLogger());
        $compiler->setSourceMap(Compiler::SOURCE_MAP_INLINE);

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
        $compiler->setSourceMap(Compiler::SOURCE_MAP_INLINE);

        $entrypoint = dirname(__DIR__) . '/vendor/zurb/foundation/assets/foundation.scss';

        $result = $compiler->compileString(file_get_contents($entrypoint), $entrypoint);

        $this->assertNotEmpty($result->getCss());
    }

    /**
     * @dataProvider provideBourbonEntrypoints
     */
    public function testBourbon($entrypoint)
    {
        $compiler = new Compiler();
        $compiler->setLogger(new QuietLogger());
        $compiler->setSourceMap(Compiler::SOURCE_MAP_INLINE);
        $compiler->addImportPath(dirname(__DIR__) . '/vendor/thoughtbot/bourbon');
        $compiler->addImportPath(dirname(__DIR__) . '/vendor/thoughtbot/bourbon/spec/fixtures');

        $result = $compiler->compileString(file_get_contents($entrypoint), $entrypoint);

        $this->assertNotEmpty($result->getCss());
    }

    public static function provideBourbonEntrypoints()
    {
        $baseDir = dirname(__DIR__) . '/vendor/thoughtbot/bourbon/spec/fixtures';

        $iterator = new \RecursiveDirectoryIterator($baseDir, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveCallbackFilterIterator($iterator, function (\SplFileInfo $current) {
            return $current->isDir() || $current->getFilename()[0] !== '_';
        });

        /** @var \SplFileInfo $file */
        foreach (new \RecursiveIteratorIterator($iterator) as $file) {
            yield substr($file->getRealPath(), strlen($baseDir) + 1) => [$file->getRealPath()];
        }
    }
}
