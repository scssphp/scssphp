<?php

namespace ScssPhp\ScssPhp\Tests\Function;

use ReflectionClass;
use ScssPhp\ScssPhp\Function\FunctionRegistry;
use PHPUnit\Framework\TestCase;

class FunctionRegistryTest extends TestCase
{
    /**
     * @dataProvider provideRegisteredFunctions
     */
    public function testFunctionDeclaration(string $functionName): void
    {
        $this->assertTrue(FunctionRegistry::has($functionName));

        $sassCallable = FunctionRegistry::get($functionName);
        $this->assertEquals($functionName, $sassCallable->getName());
    }

    public static function provideRegisteredFunctions(): iterable
    {
        $ref = new ReflectionClass(FunctionRegistry::class);
        $constant = $ref->getConstant('BUILTIN_FUNCTIONS');

        foreach ($constant as $name => $value) {
            yield [$name];
        }
    }
}
