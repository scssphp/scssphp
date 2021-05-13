# Preset variables

You can preset variables before compilation by using the `replaceVariables($vars)`
or `addVariables($vars)` methods of the Compiler. `Compiler::getVariables`
allows to get the list of registered variables. `Compiler::unsetVariable($name)`
allows to unset a variable (the exact name used for the registration must be
passed).

Presetting variables is semantically equivalent to prepending variable
declarations at the beginning of the input. If the variable is also defined in
your scss source, use the `!default` flag in the source to prevent your preset
variables from being overridden.

Variable names can optionally include the leading `$` and can use dashes or
underscores interchangeably. If multiple preset variables have the same
normalized name, the last one wins (as they are declared in order). The
recommendation is to stick with normalized names (no leading `$` and dashes
rather than underscores) to avoid confusion.

Variable values must be converted to the [internal representation of Sass values](./values.md)
using the `\ScssPhp\ScssPhp\ValueConverter` API. This API exposes 2 helpers:

- `ValueConverter::parseValue` parses a string containing a SCSS representation
  of a value.
- `ValueConverter::fromPhp` converts a PHP scalar (or null) to the equivalent
  SCSS value. Note that not all Sass values can be represented as a PHP scalar
  (numbers with units or colors for instance).

```php
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\ValueConverter;

$compiler = new Compiler();
$compiler->replaceVariables(array(
    'var' => ValueConverter::parseValue('false'),
    'size' => ValueConverter::parseValue('25px'),
));

echo $compiler->compileString('$var: true !default; @debug $var;')->getCss();
```
