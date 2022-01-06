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
use ScssPhp\ScssPhp\OutputStyle;

class CompressedTest extends TestCase
{
    public function testRemovesUnnecessaryWhitespaceAndSemicolons()
    {
        $this->assertEquals('a{x:y}', $this->compile('a {x: y}'));
    }

    public function testForDeclarationsPreservesSemicolonsWhenNecessary()
    {
        $this->assertEquals('a{q:r;s:t}', $this->compile('a {q: r; s: t}'));
    }

    public function testTheTopLevelRemovesWhitespaceAndSemicolonsBetweenAtRules()
    {
        $this->assertEquals('@foo;@bar;@baz', $this->compile('@foo; @bar; @baz;'));
    }

    public function testTheTopLevelRemovesWhitespaceAndSemicolonsBetweenStyleRules()
    {
        $this->assertEquals('a{b:c}x{y:z}', $this->compile('a {b: c} x {y: z}'));
    }

    public function testKeyframesRemovesWhitespaceAfterTheSelector()
    {
        $this->assertEquals('@keyframes a{from{a:b}}', $this->compile('@keyframes a {from {a: b}}'));
    }

    public function testKeyframesRemovesWhitespaceAfterCommas()
    {
        $this->assertEquals('@keyframes a{from,to{a:b}}', $this->compile('@keyframes a {from, to {a: b}}'));
    }

    public function testCommentsAreRemoved()
    {
        $this->assertEquals('', $this->compile('/* foo bar */'));
        $this->assertEquals('a{b:c;d:e}', $this->compile('a {
          b: c;
          /* foo bar */
          d: e;
        }'));
    }

    /**
     * @testdox Comments are preserved with /*!
     */
    public function testCommentsArePreserved()
    {
        $this->assertEquals('/*! foo bar */', $this->compile('/*! foo bar */'));
        $this->assertEquals('/*! foo *//*! bar */', $this->compile("/*! foo */\n/*! bar */"));
        $this->assertEquals('a{/*! foo bar */}', $this->compile('a {
          /*! foo bar */
        }'));
    }

    /**
     * @param string $input
     *
     * @return string
     */
    private function compile($input)
    {
        $compiler = new Compiler();
        $compiler->setOutputStyle(OutputStyle::COMPRESSED);

        $result = $compiler->compileString($input);

        return $result->getCss();
    }
}
