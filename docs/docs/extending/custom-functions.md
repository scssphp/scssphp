# Implementing custom host functions

It is possible to register custom functions written in PHP that can be called
from SCSS. Some possible applications include appending your assets directory
to a URL with an `asset-url` function, or converting image URLs to an embedded
data URI to reduce the number of requests on a page with a `data-uri` function.
Note that functions declared in SCSS with `@function` are allowed to shadow the
host functions if they reuse the same name.

We can add and remove functions using the methods `registerFunction` and
`unregisterFunction` of the `Compiler`.

* `registerFunction($functionName, $callable, $argumentDeclaration)` assigns the
  callable value to the name `$functionName`. The name is normalized using the
  rules of SCSS, meaning underscores and dashes are interchangeable. If a
  function with the same name already exists then it is replaced. The
  `$argumentDeclaration` argument is an array of parameter names (without the
  `$`).

* `unregisterFunction($functionName)` removes `$functionName` from the list of
  available functions.

Functions should declare explicitly their argument, to allow the compiler to
validate arguments and support keyword arguments for them. However, a deprecated
alternative API also exists accepting any arguments with a different signature
for the callable.

Custom host functions have to work with Sass values which are described in the
[documentation about values](./values.md).

Functions must return either `null` or a Sass value. Returning `null` means that
the function call will be compiled as a CSS function call. This is generally not
needed for custom functions as they should probably not shadow CSS functions to
avoid confusion (some built-in Sass functions rely on that however, like `rgb()`).

## Argument declaration

The argument declaration is an array of strings. Each string defines an argument.
Argument names must be valid Sass variable names, without the leading `$`. It is
recommended using dashes rather than underscores (i.e. using normalized names).
There are 3 kinds of arguments that can be declared:

- `name` declares a mandatory argument named `name`
- `name:default` declares an optional argument named `name` with `default` as
  the default value. The default value is parsed as a Sass value.
- `name...` declares a rest argument named `name`. The rest argument must always
  be the last one.

The compiler will take care of validating arguments against the function
signature, supporting the same features as for calls to functions defined in
Sass directly. However, it is still the responsibility of the callable to
validate the values themselves. The `Compiler::assert*` helpers should be used
to validate the type, providing the argument name for better error reporting.
To report custom errors, the callable must use the
`\ScssPhp\ScssPhp\Exception\SassScriptException` to ensure proper error
reporting.

## Implementing the function

### Functions declaring their arguments

The callable receives 2 arguments. However, the second one is passed only for
historical reasons (and for some special internal usages) and should not be used
anymore.

The first argument is an array of Sass values, with one value per declared
arguments. The compiler guarantees that all arguments are always provided to the
callable. A rest argument receives a Sass argument list as the value.

As an example, a function called `add-two` is registered, which adds two numbers
together (this example is useless as the built-in Sass addition is more powerful
regarding units).

```php
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Node\Number;

$compiler = new Compiler();

$compiler->registerFunction(
  'add-two',
  function($args) use ($compiler) {
    $number1 = $compiler->assertNumber($args[0], 'number1');
    $number2 = $compiler->assertNumber($args[1], 'number2');

    $number1->assertNoUnits('number1');
    $number2->assertNoUnits('number2');

    return new Number($number1->getDimension() + $number2->getDimension(), '');
  },
  ['number1', 'number2']
);

$compiler->compileString('.ex1 { result: add-two(10, 10); }')->getCss();
$compiler->compileString('.ex1 { result: add-two($number1: 10, $number2: 10); }')->getCss();
```

### Functions not declaring their arguments

When arguments are not declared (`null` is passed as the argument declaration
when registering the function), the compiler will accept any arguments when
calling the function. The callable will receive 2 arguments: a list of
positional arguments and a list of keyword arguments (with names which are not
normalized, and so confusing to use).

This usage is deprecated as of scssphp 1.5 and will not be supported in 2.0.

The direct migration is to declare the function with a rest argument:

```diff
 $compiler->registerFunction(
   'foo',
-  function ($positionalArgs, $keywordArgs) use ($compiler) {
+  function (array $args) use ($compiler) {
+    $restArgument = $args[0];
+    $positionalArgs = $restArgument[2];
+    $keywordArgs = $compiler->getArgumentListKeywords($restArgument);
     // Do something to return a value
-  }
+  },
+  ['args...']
 );
```

However, in most cases, functions actually expect a given signature implicitly
rather than accepting anything. In such case, it is better to migrate to an
explicit signature and to rework the callable to account for that.

## Reporting errors or warnings

Errors in the execution of functions must be reported by using the
`\ScssPhp\ScssPhp\Exception\SassScriptException` for proper error reporting.
When the error is related to the validation of an argument, the
`SassScriptException::forArgument` method should be used to instantiate the
exception.

Warnings can be reported using the `\ScssPhp\ScssPhp\Warn` API.
