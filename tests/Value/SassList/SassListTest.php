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

use ScssPhp\ScssPhp\Tests\Value\ValueTestCase;
use ScssPhp\ScssPhp\Value\ListSeparator;

class SassListTest extends ValueTestCase
{
    public function testASlashSeparatedListIsSlashSeparated()
    {
        $this->assertEquals(ListSeparator::SLASH, self::parseValue('list.slash(a, b, c)')->getSeparator());
    }

    public function testASpaceSeparatedListIsSpaceSeparated()
    {
        $this->assertEquals(ListSeparator::SPACE, self::parseValue('a b c')->getSeparator());
    }

    public function testABracketedListHasBrackets()
    {
        $this->assertTrue(self::parseValue('[a, b, c]')->hasBrackets());
    }

    public function testACommaSeparatedSingleElementListIsCommaSeparated()
    {
        $this->assertEquals(ListSeparator::COMMA, self::parseValue('(1,)')->getSeparator());
    }
}
