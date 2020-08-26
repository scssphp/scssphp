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
        try {
            $this->compile($scss);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), $expectedExceptionMessage) === false) {
                $this->fail('Unexpected exception raised: ' . $e->getMessage() . ' vs ' . $expectedExceptionMessage);
            }

            $this->assertTrue(true);

            return;
        }

        $this->fail('Expected exception to be raised: ' . $expectedExceptionMessage);
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
                'Mixin or function doesn\'t have an argument named $a.'
            ],
            array(<<<'END_OF_SCSS'
div {
  color: darken(cobaltgreen, 10%);
}
END_OF_SCSS
                ,
                'expecting color'
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
        ];
    }

    private function compile($str)
    {
        $scss = new Compiler();

        return trim($scss->compile($str));
    }
}
