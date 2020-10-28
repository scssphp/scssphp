<?php

namespace ScssPhp\ScssPhp\Tests;

use PHPUnit\Framework\TestCase;

class IncFileTest extends TestCase
{
    /**
     * @dataProvider provideFiles
     */
    public function testFileLoaded($relativePath)
    {
        $source = file_get_contents(__DIR__.'/../scss.inc.php');

        $expectedString = sprintf('include_once __DIR__ . \'/src/%s\';', $relativePath);

        $this->assertTrue(false !== strpos($source, $expectedString), "The file '$relativePath' must be loaded in scss.inc.php");
    }

    public static function provideFiles()
    {
        $baseDir = dirname(__DIR__).'/src/';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseDir, \FilesystemIterator::SKIP_DOTS));

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $relativePath = substr($file->getPathname(), strlen($baseDir));

            if (\DIRECTORY_SEPARATOR === '\\') {
                $relativePath = str_replace('\\', '/', $relativePath);
            }

            yield [$relativePath];
        }
    }
}
