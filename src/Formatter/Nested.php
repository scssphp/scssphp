<?php
/**
 * SCSSPHP
 *
 * @copyright 2012-2019 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Formatter;

use ScssPhp\ScssPhp\Formatter;
use ScssPhp\ScssPhp\Formatter\OutputBlock;

/**
 * Nested formatter
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 */
class Nested extends Formatter
{
    /**
     * @var integer
     */
    private $depth;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->indentLevel = 0;
        $this->indentChar = '  ';
        $this->break = "\n";
        $this->open = ' {';
        $this->close = ' }';
        $this->tagSeparator = ', ';
        $this->assignSeparator = ': ';
        $this->keepSemicolons = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function indentStr()
    {
        $n = $this->depth - 1;
//var_dump($this->depth. ':'.$this->indentLevel);
        return str_repeat($this->indentChar, max($this->indentLevel + $n, 0));
    }

    /**
     * {@inheritdoc}
     */
    protected function blockLines(OutputBlock $block)
    {
        $inner = $this->indentStr();

        $glue = $this->break . $inner;

        foreach ($block->lines as $index => $line) {
            if (substr($line, 0, 2) === '/*') {
                $block->lines[$index] = preg_replace('/(\r|\n)+/', $glue, $line);
            }
        }

        $this->write($inner . implode($glue, $block->lines));
    }

    /**
     * {@inheritdoc}
     */
    protected function block(OutputBlock $block)
    {
        static $depths;
        static $downLevel;
        static $closeBlock;

        if ($block->type === 'root') {
            $depths = [ 0 ];
            $downLevel = '';
            $closeBlock = '';
            $this->depth = 0;
        }

        if (empty($block->lines) && empty($block->children)) {
            return;
        }

        $this->currentBlock = $block;


        // increase/decrease depth
        /*if ($block->depth == 1 && $block->depth == end($depths)) {
            $downLevel = $this->break;
        }*/
        while ($block->depth < end($depths) || ($block->depth == 1 && end($depths) == 1)) {
            array_pop($depths);
            $this->depth--;
            $downLevel = $this->break;
        }
        if (! empty($block->lines)) {
            if ($block->depth > end($depths)) {
                $this->depth++;
                $depths[] = $block->depth;
            }
        }


        if (! empty($block->selectors)) {
            if ($closeBlock) {
                $this->write($closeBlock);
                $closeBlock = '';
            }
            if ($downLevel) {
                $this->write($downLevel);
                $downLevel = '';
            }
            $this->blockSelectors($block);

            $this->indentLevel++;
        }

        if (! empty($block->lines)) {
            if ($closeBlock) {
                $this->write($closeBlock);
                $closeBlock = '';
            }
            if ($downLevel) {
                $this->write($downLevel);
                $downLevel = '';
            }
            $this->blockLines($block);
            $closeBlock = $this->break;
        }

        if (! empty($block->children)) {
            $this->blockChildren($block);
        }

        if (! empty($block->selectors)) {
            $this->indentLevel--;

            $this->write($this->close);
            $closeBlock = $this->break;
        }

        if ($block->type === 'root') {
            $this->write($this->break);
        }
    }

}
