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
    /**
     * @var string
     */
    protected $css = '';

    /**
     * @var string
     */
    protected $sourceMap = '';

    protected $sourceMapFile;
    protected $sourceMapUrl;


    /**
     * All the effective parsedfiles
     * @var array
     */
    protected $parsedFiles = [];

    /**
     * All the @import files and urls seen in the compilation process
     * @var array
     */
    protected $includedFiles = [];

    /**
     * All the @import files resolved and imported (use to check the once condition)
     * @var array
     */
    protected $importedFiles = [];


    /**
     * @param string $css
     */
    public function setCss($css)
    {
        $this->css = $css;
    }

    /**
     * @param string $css
     */
    public function appendCss($css)
    {
        $this->css .= $css;
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
     * @param string $path
     *
     * @return void
     */
    public function addParsedFile($path)
    {
        if (isset($path) && is_file($path)) {
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


    // name matching the JS API
    // The set that will eventually populate the JS API's
    // `result.stats.includedFiles` field.
    //
    // For filesystem imports, this contains the import path. For all other
    // imports, it contains the URL passed to the `@import`.
    public function getIncludedFiles()
    {
        // ...
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
     * @param $sourceMap
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
     * @return string
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
     * @return bool
     */
    public function checkValid()
    {
        // check if any dependency file changed before accepting the cache
        foreach ($this->parsedFiles as $file => $mtime) {
            if (! is_file($file) || filemtime($file) !== $mtime) {
                return false;
            }
        }

        return true;
    }

}
