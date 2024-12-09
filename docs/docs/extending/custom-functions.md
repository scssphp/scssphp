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

Functions must declare explicitly their argument, to allow the compiler to
validate arguments and support keyword arguments for them.

Custom host functions have to work with Sass values which are described in the
[documentation about values](./values.md).

Modern functions **must** use `\ScssPhp\ScssPhp\Value\Value` as native return
type. The compiler uses this return type to distinguish modern implementations
(using the modern representation) from legacy implementations (using the
deprecated legacy representation of values).

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
validate the values themselves.

## Reporting errors or warnings

Errors in the execution of functions must be reported by using the
`\ScssPhp\ScssPhp\Exception\SassScriptException` for proper error reporting.
When the error is related to the validation of an argument, the
`SassScriptException::forArgument` method should be used to instantiate the
exception.

Warnings can be reported using the `\ScssPhp\ScssPhp\Warn` API.

## Implementing the function

### Modern functions

The callable is expected to have the signature `callable(array $args): Value`,
where the type passed for arguments is `list<\ScssPhp\ScssPhp\Value\Value>`.

The argument is a list of Sass values, with one value per declared
arguments. The compiler guarantees that all arguments are always provided to the
callable. A rest argument receives a `\ScssPhp\ScssPhp\Value\SassArgumentList` as
the value.

```php
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Value\SassNumber;
use ScssPhp\ScssPhp\Value\Value;

$compiler = new Compiler();

$compiler->registerFunction(
  'add-two',
  /** @param list<Value> $args */
  function(array $args): Value {
    $number1 = $args[0]->assertNumber('number1');
    $number2 = $args[1]->assertNumber('number2');

    $number1->assertNoUnits('number1');
    $number2->assertNoUnits('number2');

    return SassNumber::create($number1->getValue() + $number2->getValue());
  },
  ['number1', 'number2']
);

$compiler->compileString('.ex1 { result: add-two(10, 10); }')->getCss();
$compiler->compileString('.ex1 { result: add-two($number1: 10, $number2: 10); }')->getCss();
```

### Legacy functions declaring their arguments

The callable receives 1 argument.

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

Functions must return either `null` or a Sass value. Returning `null` means that
the function call will be compiled as a CSS function call. This is generally not
needed for custom functions as they should probably not shadow CSS functions to
avoid confusion (some built-in Sass functions rely on that however, like `rgb()`).

### Legacy functions not declaring their arguments

When arguments are not declared (`null` is passed as the argument declaration
when registering the function), the compiler will accept any arguments when
calling the function. The callable will receive 2 arguments: a list of
positional arguments and a list of keyword arguments (with names which are not
normalized, and so confusing to use).

This usage was deprecated as of scssphp 1.5 and is not supported anymore in 2.0.

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
