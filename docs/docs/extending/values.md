# Working with Sass values

Note: this documentation applies to working with values inside Sass functions.
When working on the internals of the compiler, things are more complex.

scssphp represents Sass values using `array|\ScssPhp\ScssPhp\Node\Number`. The
index `0` of the array will be the type of value (one of the
`\ScssPhp\ScssPhp\Type` constants, but not all these constants are about value
types).

The `Compiler` class exposes various `assert*` methods to check the type of
values. These methods return the asserted value (which may not be exactly the
same as the input due to complex implementation details, so the return value
should be used).

If a string representation of a value is needed to include it in an error
message, the `Compiler::compileValue` method can be used.

## Value representation

### Null

Sass' `null` value is representation using `[Type::T_NULL]`. It can be
referenced as `Compiler::$null`.

### Booleans

Sass' boolean values are represented as `Compiler::$true` and `Compiler::$false`.

When accepting a parameter in a function, it is generally better to use
`Compiler::isTruthy` instead of explicitly checking for boolean value.

### Numbers

Sass' numbers are represented using the `\ScssPhp\ScssPhp\Node\Number` class.

### Strings

Sass' strings are represented as an array with 3 elements:

- `Type::T_STRING`
- the quote character. this is either the empty string for unquoted strings or
  a quote character (`"` or `'`). When creating new quoted strings, use the `"`
  character (the rendering will not always respect this quoting character anyway).
- the string content, as an array of string parts. The exact structure for parts
  is considered an internal implementation detail. Use `Compiler::getStringText`
  to get the text of the string. When creating new strings, put the string
  content as the single item in the array (or use `ValueConverter::fromPhp`).

Note: arguments received by functions may not always use the `Type::T_STRING`
due to internal details. However, `Compiler::assertString` guarantees that its
return value is actually a string using `Type::T_STRING`.

### Colors

Sass' colors are represented as an array with 4 or 5 elements (the alpha
channel is optional):

- `Type::T_COLOR`
- an integer between 0 and 255 for the red channel
- an integer between 0 and 255 for the green channel
- an integer between 0 and 255 for the blue channel
- an optional float between 0 and 1 for the alpha channel (omitting it is the
  same as having 1 as value)

Note: arguments might receive a `Type::T_KEYWORD` corresponding to a color
keyword. Always use `Compiler::assertColor` to get the actual color.

### Maps

Sass' maps are represented as an array with 3 elements:

- `Type::T_MAP`
- an array of keys
- an array of values

The actual handling of keys is weird, as it does not rely on the equality rules
of values (which is not spec compliant). When writing custom functions, it is
advised to stay away from maps as arguments or return value for now.

As Sass empty lists can be used as maps, always use `Compiler::assertMap` to get
the actual map value (this helper takes care of converting empty lists).

### Lists

Sass' lists are represented as an array with 3 elements:

- `Type::T_LIST`
- the list separator as a string (the empty string represents undecided delimiters)
- an array of values

Bracketed lists are represented by adding an `enclosing` key with a value of
`bracket`.

Note that the handling of the list separator is not fully compliant. List values
might have an undecided separators in cases where they should not in Sass.

### Argument lists

Sass's argument lists are representing the list of rest arguments for variadic
functions.

They are represented as a Sass list (see above) containing positional arguments
with an additional element in the array to store the keyword arguments. To
access keyword arguments of the Sass argument list, use
`Compiler::getArgumentListKeywords`.
