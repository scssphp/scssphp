<?php

/**
 * SCSSPHP
 *
 * @copyright 2018-2020 Anthon Pang
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Tests\Value\SassList;

use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\Value\ListSeparator;
use ScssPhp\ScssPhp\Value\SassList;
use ScssPhp\ScssPhp\Value\SassString;

/**
 * @testdox new SassList()
 */
class ConstructorTest extends TestCase
{
    public function testCreatesAListWithTheGivenContentsAndMetadata()
    {
        $list = new SassList([new SassString('a', false)], ListSeparator::SPACE);

        $this->assertEquals([new SassString('a', false)], $list->asList());
        $this->assertEquals(ListSeparator::SPACE, $list->getSeparator());
        $this->assertFalse($list->hasBrackets());
    }

    public function testCanCreateABracketedList()
    {
        $list = new SassList([new SassString('a', false)], ListSeparator::SPACE, true);

        $this->assertTrue($list->hasBrackets());
    }

    public function testCanCreateAShortListWithAnUndecidedSeparator()
    {
        $list = new SassList([new SassString('a', false)], ListSeparator::UNDECIDED);
        $this->assertEquals(ListSeparator::UNDECIDED, $list->getSeparator());

        $this->assertEquals(ListSeparator::UNDECIDED, (new SassList([], ListSeparator::UNDECIDED))->getSeparator());
    }

    public function testCantCreateALongListWithAnUndecidedSeparator()
    {
        $contents = [new SassString('a', false), new SassString('b', false)];

        $this->expectException(\InvalidArgumentException::class);
        new SassList($contents, ListSeparator::UNDECIDED);
    }
}
