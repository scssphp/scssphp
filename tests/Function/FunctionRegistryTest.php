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

    public function testMathIsBuiltinModule(): void
    {
        $this->assertTrue(FunctionRegistry::isBuiltinModule('sass:math'));
    }

    /**
     * Only `sass:math` is implemented so far; the other `sass:*` modules can't
     * yet be loaded with `@use`.
     *
     * @dataProvider provideUnsupportedModuleUrls
     */
    public function testUnsupportedModuleIsNotBuiltin(string $url): void
    {
        $this->assertFalse(FunctionRegistry::isBuiltinModule($url));
    }

    public static function provideUnsupportedModuleUrls(): iterable
    {
        yield ['sass:color'];
        yield ['sass:list'];
        yield ['sass:map'];
        yield ['sass:meta'];
        yield ['sass:selector'];
        yield ['sass:string'];
        yield ['sass:bogus'];
        yield ['library'];
    }

    public function testMathModuleExposesFunctionsByCanonicalName(): void
    {
        $module = FunctionRegistry::getModule('sass:math');

        // Functions exposed under their canonical name within the module.
        $this->assertTrue($module->functionExists('compatible'));
        $this->assertTrue($module->functionExists('is-unitless'));
        $this->assertTrue($module->functionExists('unit'));
        // The legacy global names are not used inside the module.
        $this->assertFalse($module->functionExists('comparable'));
        $this->assertFalse($module->functionExists('unitless'));
    }

    /**
     * @dataProvider provideModuleOnlyMathFunctions
     */
    public function testMathModuleExposesModuleOnlyFunctions(string $name): void
    {
        $this->assertTrue(FunctionRegistry::getModule('sass:math')->functionExists($name));
        // Module-exclusive functions are not available in the global namespace.
        $this->assertFalse(FunctionRegistry::has($name), "$name should not be a global function");
    }

    public static function provideModuleOnlyMathFunctions(): iterable
    {
        foreach (['div', 'clamp', 'hypot', 'log', 'pow', 'sqrt', 'cos', 'sin', 'tan', 'acos', 'asin', 'atan', 'atan2'] as $name) {
            yield $name => [$name];
        }
    }

    public function testMathModuleExposesConstants(): void
    {
        $module = FunctionRegistry::getModule('sass:math');

        foreach (['pi', 'e', 'epsilon', 'max-safe-integer', 'min-safe-integer', 'max-number', 'min-number'] as $name) {
            $this->assertNotNull($module->getVariable($name), "math.\$$name should exist");
        }
    }

    public function testGetModuleThrowsForUnsupportedModule(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        FunctionRegistry::getModule('sass:color');
    }
}
