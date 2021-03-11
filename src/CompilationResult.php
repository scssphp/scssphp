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

namespace ScssPhp\ScssPhp;

use ScssPhp\ScssPhp\Exception\CompilerException;

/**
 * Compiler environment
 *
 */
class CompilationResult
{
    /**
     * @var string
     */
    private $css = '';

    /**
     * @var string
     */
    private $sourceMap = '';

    /**
     * @var string|null
     */
    private $sourceMapFile;

    /**
     * @var string|null
     */
    private $sourceMapUrl;

    /**
     * All the effective parsed files
     * @var array<string, int>
     */
    private $parsedFiles = [];

    /**
     * All the @import files and urls seen in the compilation process
     * @var string[]
     */
    private $includedFiles = [];

    /**
     * All the @import files resolved and imported (use to check the once condition)
     * @var array
     * @phpstan-var list<array{currentDir: string, path: string, filePath: string}>
     */
    private $importedFiles = [];

    /**
     * @param string $css
     *
     * @return void
     */
    public function setCss($css)
    {
        $this->css = $css;
    }

    /**
     * @return string
     */
    public function getCss()
    {
        return $this->css . $this->getSourceMapCss();
    }

    /**
     * Adds to list of parsed files
     *
     * @api
     *
     * @param string|null $path
     *
     * @return void
     */
    public function addParsedFile($path)
    {
        if (! \is_null($path) && is_file($path)) {
            $this->parsedFiles[realpath($path)] = filemtime($path);
        }
    }

    /**
     * Returns list of parsed files
     *
     * @api
     *
     * @return array<string, int>
     */
    public function getParsedFiles()
    {
        return $this->parsedFiles;
    }

    /**
     * Save the imported files with their resolving path context
     * @param string $currentDirectory
     * @param string $path
     * @param string $filePath
     *
     * @return void
     */
    public function addImportedFile($currentDirectory, $path, $filePath)
    {
        $this->importedFiles[] = ['currentDir' => $currentDirectory, 'path' => $path, 'filePath' => $filePath];
        $this->addIncludedFile($filePath);
    }


    /**
     * Get the list of the already imported Files
     * used by the compiler to check the once condition on @import
     * @return string[]
     */
    public function getImportedFiles()
    {
        return array_column($this->importedFiles, 'filePath');
    }

    /**
     * @return array
     * @phpstan-return list<array{currentDir: string, path: string, filePath: string}>
     *
     * @internal
     */
    public function getImports()
    {
        return $this->importedFiles;
    }

    /**
     * Save the included files
     * @param string $path
     *
     * @return void
     */
    public function addIncludedFile($path)
    {
        // unquote the included path if needed
        foreach (['"', '"'] as $quote) {
            if (strpos($path, $quote) === 0 && substr($path, -1) === $quote) {
                $path = substr($path, 1, -1);
                break;
            }
        }

        $this->includedFiles[] = $path;
    }

    /**
     * For filesystem imports, this contains the import path. For all other
     * imports, it contains the URL passed to the `@import`.
     *
     * @return string[]
     */
    public function getIncludedFiles()
    {
        return $this->includedFiles;
    }

    public function __toString()
    {
        return $this->getCss(); // To reduce the impact of the BC break
    }

    /**
     * Store the sourceMap and its storage data
     * @param string $sourceMap
     * @param null|string $sourceMapFile
     * @param null|string $sourceMapUrl
     *
     * @return void
     */
    public function setSourceMap($sourceMap, $sourceMapFile = null, $sourceMapUrl = null)
    {
        $this->sourceMap = $sourceMap;
        if (empty($sourceMapFile) || empty($sourceMapUrl)) {
            $this->sourceMapUrl = $this->sourceMapFile = null;
        } else {
            $this->sourceMapFile = $sourceMapFile;
            $this->sourceMapUrl = $sourceMapUrl;
        }
    }

    /**
     * The sourceMap content, if it was generated
     *
     * @return null|string
     */
    public function getSourceMap()
    {
        return $this->sourceMap;
    }

    /**
     * The sourceMap Css content, if there is a sourceMap
     * @return string
     */
    private function getSourceMapCss()
    {
        if ($this->sourceMap) {
            if ($this->sourceMapFile) {
                $sourceMapurl = $this->sourceMapUrl;
                $dir  = \dirname($this->sourceMapFile);

                // directory does not exist
                if (! is_dir($dir)) {
                    // FIXME: create the dir automatically?
                    throw new CompilerException(
                        sprintf('The directory "%s" does not exist. Cannot save the source map.', $dir)
                    );
                }

                // FIXME: proper saving, with dir write check!
                if (file_put_contents($this->sourceMapFile, $this->sourceMap) === false) {
                    throw new CompilerException(sprintf('Cannot save the source map to "%s"', $this->sourceMapFile));
                }
            }
            else {
                $sourceMapurl = Util::encodeURIComponent($this->sourceMap);
            }

            if ($sourceMapurl) {
                return sprintf('/*# sourceMappingURL=%s */', $sourceMapurl);
            }
        }

        return '';
    }
}
