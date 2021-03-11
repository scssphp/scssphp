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

class CompilationResult
{
    /**
     * @var string
     */
    private $css;

    /**
     * @var string|null
     */
    private $sourceMap;

    /**
     * @var string|null
     */
    private $sourceMapFile;

    /**
     * @var string|null
     */
    private $sourceMapUrl;

    /**
     * @var string[]
     */
    private $includedFiles;

    /**
     * @param string $css
     * @param string|null $sourceMap
     * @param string|null $sourceMapFile
     * @param string|null $sourceMapUrl
     * @param string[] $includedFiles
     */
    public function __construct($css, $sourceMap, $sourceMapFile, $sourceMapUrl, array $includedFiles)
    {
        $this->css = $css;
        $this->sourceMap = $sourceMap;
        $this->sourceMapFile = $sourceMapFile;
        $this->sourceMapUrl = $sourceMapUrl;
        $this->includedFiles = $includedFiles;
    }

    /**
     * @return string
     */
    public function getCss()
    {
        return $this->css . $this->getSourceMapCss();
    }

    /**
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
