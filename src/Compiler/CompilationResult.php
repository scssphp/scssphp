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

namespace ScssPhp\ScssPhp\Compiler;

use ScssPhp\ScssPhp\Util;
use ScssPhp\ScssPhp\Exception\CompilerException;

/**
 * Compiler environment
 *
 */
class CompilationResult
{
    private $isCached = false;

    /**
     * @var string
     */
    private $css = '';

    /**
     * @var string
     */
    private $sourceMap = '';

    private $sourceMapFile;
    private $sourceMapUrl;


    /**
     * All the effective parsedfiles
     * @var array
     */
    private $parsedFiles = [];

    /**
     * All the @import files and urls seen in the compilation process
     * @var array
     */
    private $includedFiles = [];

    /**
     * All the @import files resolved and imported (use to check the once condition)
     * @var array
     */
    private $importedFiles = [];


    /**
     * @param bool $isCached
     */
    public function setIsCached($isCached) {
        $this->isCached = $isCached;
    }

    /**
     * @return bool
     */
    public function getIsCached() {
        return $this->isCached;
    }

    /**
     * @param string $css
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
     * @return array
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
     */
    public function addImportedFile($currentDirectory, $path, $filePath)
    {
        $this->importedFiles[] = ['currentDir' => $currentDirectory, 'path' => $path, 'filePath' => $filePath];
        $this->addIncludedFile($filePath);
    }


    /**
     * Get the list of the already imported Files
     * used by the compiler to check the once condition on @import
     * @return array
     */
    public function getImportedFiles()
    {
        return array_column($this->importedFiles, 'filePath');
    }

    /**
     * Save the included files
     * @param string $path
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
     * @return array
     */
    public function getIncludedFiles()
    {
        return $this->includedFiles;
    }

    // A map from source file URLs to the corresponding [SourceFile]s.
    //
    // This can be passed to [sourceMap]'s [Mapping.spanFor] method. It's `null`
    // if source mapping was disabled for this compilation.
    public function getSourceFiles()
    {

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
    public function getSourceMapCss()
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

    /**
     * Check if this result is still valid
     * ie no file has been modified
     *
     * @param bool $deepCheck
     * @param \ScssPhp\ScssPhp\Compiler $compiler
     * @return bool
     */
    public function checkValid($deepCheck = false, $compiler = null)
    {
        // only check for cached results
        if (! $this->isCached) {
            return true;
        }

        // check that all the findImport would resolve the same way
        if ($deepCheck && $compiler) {
            $resolvedImport = [];
            foreach ($this->importedFiles as $imported) {

                $currentDir = $imported['currentDir'];
                $path = $imported['path'];
                // store the check accros all the results in memory to avoid multiple findImport() on the same path
                // with same context
                // this is happening in a same hit with multiples compilation (especially with big frameworks)
                if (empty($resolvedImport[$currentDir][$path])) {
                    $resolvedImport[$currentDir][$path] = $compiler->findImport($path, $currentDir);
                }

                if ($resolvedImport[$currentDir][$path] !== $imported['filePath']) {
                    return false;
                }
            }
        }

        // check if any dependency file changed since the result was compiled
        foreach ($this->parsedFiles as $file => $mtime) {
            if (! is_file($file) || filemtime($file) !== $mtime) {
                return false;
            }
        }

        return true;
    }

}
