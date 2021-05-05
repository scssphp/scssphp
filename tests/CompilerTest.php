<?php

namespace ScssPhp\ScssPhp\Tests;

use ScssPhp\ScssPhp\Compiler;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    /**
     * @dataProvider provideSassFunctionMethods
     */
    public function testArgumentDeclaration($method)
    {
        $r = new \ReflectionClass(Compiler::class);

        $this->assertTrue($r->hasProperty($method));

        $p = $r->getProperty($method);

        $this->assertTrue($p->isStatic());
    }

    public static function provideSassFunctionMethods()
    {
        $r = new \ReflectionClass(Compiler::class);

        foreach ($r->getMethods() as $method) {
            if (0 !== strpos($method->name, 'lib')) {
                continue;
            }

            yield $method->name => [$method->name];
        }
    }
}
