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

namespace ScssPhp\ScssPhp\Tests\SourceSpan\Util;

use PHPUnit\Framework\TestCase;
use ScssPhp\ScssPhp\SourceSpan\Util;

class FindLineStartTest extends TestCase
{
    public function testSkipEntriesInWrongColumn(): void
    {
        $context = "0_bb\n1_bbb\n2b____\n3bbb\n";
        $index = Util::findLineStart($context, 'b', 1);

        self::assertNotNull($index);
        self::assertEquals(11, $index);
        self::assertEquals("\n2b_", Util::substring($context, $index - 1, $index + 3));
    }

    public function testEndOfLineColumnForEmptyText(): void
    {
        $context = "0123\n56789\nabcdefgh\n";
        $index = Util::findLineStart($context, '', 5);

        self::assertNotNull($index);
        self::assertEquals(5, $index);
        self::assertEquals('5', $context[$index]);
    }

    public function testColumnAtEndOfFileForEmptyText(): void
    {
        $context = "0\n2\n45\n";

        $index = Util::findLineStart($context, '', 2);

        self::assertNotNull($index);
        self::assertEquals(4, $index);
        self::assertEquals('4', $context[$index]);

        $context = "0\n2\n45";
        $index = Util::findLineStart($context, '', 2);

        self::assertNotNull($index);
        self::assertEquals(4, $index);
    }

    public function testEmptyTextInEmptyContext(): void
    {
        $index = Util::findLineStart('', '', 0);
        self::assertNotNull($index);
        self::assertEquals(0, $index);
    }

    public function testFoundOnTheFirstLine(): void
    {
        $context = "0\n2\n45\n";

        $index = Util::findLineStart($context, '0', 0);

        self::assertNotNull($index);
        self::assertEquals(0, $index);
    }

    public function testFindsTextThatStartWithANewline(): void
    {
        $context = "0\n2\n45\n";

        $index = Util::findLineStart($context, "\n2", 1);

        self::assertNotNull($index);
        self::assertEquals(0, $index);
    }

    public function testNotFound(): void
    {
        $context = "0\n2\n45\n";

        $index = Util::findLineStart($context, "0", 1);

        self::assertNull($index);
    }
}
