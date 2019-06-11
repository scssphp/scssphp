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
        static $previousEmpty;

        if ($block->type === 'root') {
            $depths = [ 0 ];
            $downLevel = '';
            $closeBlock = '';
            $this->depth = 0;
            $previousEmpty = false;
        }

        while ($block->depth < end($depths) || ($block->depth == 1 && end($depths) == 1)) {
            array_pop($depths);
            $this->depth--;
            if (!$this->depth && $block->depth <= 1) {
                $downLevel = $this->break;
            }
            if (empty($block->lines) && empty($block->children)) {
                $previousEmpty = true;
            }
        }

        if (empty($block->lines) && empty($block->children)) {
            return;
        }

        $this->currentBlock = $block;

        if (! $previousEmpty || $this->depth < 1) {
            if (! empty($block->lines) || (! empty($block->children) && $this->depth < 1)) {
                if ($block->depth > end($depths)) {
                    $this->depth++;
                    $depths[] = $block->depth;
                }
            }
        }

        $previousEmpty = false;

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
            if ($this->depth>0) {
                array_pop($depths);
                $this->depth--;
                $this->blockChildren($block);
                $this->depth++;
                $depths[] = $block->depth;
            } else {
                $this->blockChildren($block);
            }
        }

        if (! empty($block->selectors)) {
            $this->indentLevel--;

            $this->write($this->close);
            $closeBlock = $this->break;

            if ($this->depth > 1 && ! empty($block->children)) {
                array_pop($depths);
                $this->depth--;
                if (!$this->depth) {
                    $downLevel = $this->break;
                }
            }
        }

        if ($block->type === 'root') {
            $this->write($this->break);
        }
    }
}
