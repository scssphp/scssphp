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
use ScssPhp\ScssPhp\CompilationResult;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\Logger\QuietLogger;

/**
 * Exception test
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 */
class ExceptionTest extends TestCase
{
    /**
     * @param string $scss
     * @param string $expectedExceptionMessage
     *
     * @dataProvider provideScss
     */
    public function testThrowError($scss, $expectedExceptionMessage)
    {
        $this->expectException(SassException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->compile($scss);
    }

    /**
     * @return array
     */
    public function provideScss()
    {
        return [
            [<<<'END_OF_SCSS'
.test {
  foo : bar;
END_OF_SCSS
                ,
                'unclosed block'
            ],
            [<<<'END_OF_SCSS'
.test {
}}
END_OF_SCSS
                ,
                'unexpected }'
            ],
            [<<<'END_OF_SCSS'
.test { color: #fff / 0; }
END_OF_SCSS
                ,
                'color: Can\'t divide by zero'
            ],
            [<<<'END_OF_SCSS'
.test {
  @include foo();
}
END_OF_SCSS
                ,
                'Undefined mixin foo'
            ],
            [<<<'END_OF_SCSS'
@mixin do-nothing() {
}

.test {
  @include do-nothing($a: "hello");
}
END_OF_SCSS
                ,
                'No argument named $a.'
            ],
            array(<<<'END_OF_SCSS'
div {
  color: darken(cobaltgreen, 10%);
}
END_OF_SCSS
                ,
                '$color: cobaltgreen is not a color.'
            ),
            array(<<<'END_OF_SCSS'
div {
  color: fade-out(#FFF, 100%);
}
END_OF_SCSS
                ,
                '$amount: Expected 100% to be within 0 and 1.'
            ),
            [<<<'END_OF_SCSS'
BODY {
    DIV {
        $bg: red;
    }

    background: $bg;
}
END_OF_SCSS
                ,
                'Undefined variable $bg'
            ],
            [<<<'END_OF_SCSS'
@mixin example {
    background: $bg;
}

P {
    $bg: red;

    @include example;
}
END_OF_SCSS
                ,
                'Undefined variable $bg'
            ],
            [<<<'END_OF_SCSS'
a.important {
  @extend .notice;
}
END_OF_SCSS
                ,
                'was not found'
            ],
            [<<<'END_OF_SCSS'
@import "missing";
END_OF_SCSS
                ,
                'file not found for @import'
            ],
            [<<<'END_OF_SCSS'
.test {
    $list: 1, 2, 3;
    value: nth($list, 1.5);
}
END_OF_SCSS
                ,
                '1.5 is not an integer.'
            ],
            [<<<'END_OF_SCSS'
.test {
    $list: 1, 2, 3;
    $new-list: set-nth($list, 1.5, 5);
}
END_OF_SCSS
                ,
                '1.5 is not an integer.'
            ],
        ];
    }

    /**
     * @param string $str
     *
     * @return CompilationResult
     */
    private function compile($str)
    {
        $scss = new Compiler();
        $scss->setLogger(new QuietLogger());

        return $scss->compileString($str);
    }
}
