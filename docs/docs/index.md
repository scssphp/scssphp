---
layout: default
title: Documentation
---

# scssphp {{ site.current_version }} Documentation

## SCSSPHP Library

### Including

The project can be loaded through a `composer` generated auto-loader.

Alternatively, the entire project can be loaded through a utility file.
Just include it somewhere to start using it:

```php
require_once 'scssphp/scss.inc.php';
```

### Compiling

In order to manually compile code from PHP you must create an instance of the
`Compiler` class. The typical flow is to create the instance, set any compile time
options, then run the compiler with the `compile` method.

```php
use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();

echo $scss->compile('
  $color: #abc;
  div { color: lighten($color, 20%); }
');
```

* `compile($scssCode)` will attempt to compile a string of SCSS code. If it
  succeeds, the CSS will be returned as a string. If there is any error, an
  exception is thrown with an appropriate error message.

### Import Paths

When you import a file using the `{% raw %}@{% endraw %}import` directive,
the import is resolved relatively to the current file. The input of `compile`
is considered to be in the current working directory (`getcwd()`) unless its
path is provided.
In case you want to load files from other folders, there are two methods for
 manipulating the import path: `addImportPath`, and `setImportPaths`.

* `addImportPath($path)` will append `$path` to the list of the import
  paths that are searched.

* `setImportPaths($pathArray)` will replace the entire import path with
  `$pathArray`. The value of `$pathArray` will be converted to an array if it
  isn't one already.

```php
use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();
$scss->setImportPaths('assets/stylesheets/');

// will search for 'assets/stylesheets/mixins.scss'
echo $scss->compile('{% raw %}@{% endraw %}import "mixins.scss";');
```

Besides adding static import paths, it's also possible to add custom import
functions. This allows you to load paths from a database, or HTTP, or using
files that SCSS would otherwise not process (such as vanilla CSS imports).

```php
use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();
$scss->addImportPath(function($path) {
    if (!file_exists('stylesheets/'.$path)) return null;
    return 'stylesheets/'.$path;
});

// will import 'stylesheets/vanilla.css'
echo $scss->compile('{% raw %}@{% endraw %}import "vanilla.css";');
```

A list of the compiled files (both the primary file and its imports) can be
retrieved using the `getParsedFiles` method.

* `getParsedFiles()` returns an associative array where the keys are
  the file names and the values are the corresponding file's last-modified
  timestamp.

### Preset Variables

You can preset variables before compilation by using the `setVariables($vars)`
method. If the variable is also defined in your scss source, use the `!default`
flag to prevent your preset variables from being overridden.

```php
use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();
$scss->setVariables(array(
    'var' => 'false',
));

echo $scss->compile('$var: true !default;');
```

Note: the value is the scss source to be parsed. If you want to parse a string,
you have to represent it as a string, e.g. `'var' => '"string"'`.

Likewise, you can retrieve the preset variables using the `getVariables()`
method, and unset a variable using the `unsetVariable($name)` method.

### Output Formatting

The output formatting can be configured using the `setOutputStyle` method.
2 styles are provided: `\ScssPhp\ScssPhp\OutputStyle::EXPANDED` and
`\ScssPhp\ScssPhp\OutputStyle::COMPRESSED`.

Given the following SCSS:

```scss
/*! Comment */
.navigation {
    ul {
        line-height: 20px;
        color: blue;
        a {
            color: red;
        }
    }
}

.footer {
    .copyright {
        color: silver;
    }
}
```

The output will look like that:

`OutputStyle::EXPANDED`:

```css
/*! Comment */
.navigation ul {
  line-height: 20px;
  color: blue;
}
.navigation ul a {
  color: red;
}
.footer .copyright {
  color: silver;
}
```

`OutputStyle::COMPRESSED`:

```css
/* Comment*/.navigation ul{line-height:20px;color:blue;}.navigation ul a{color:red;}.footer .copyright{color:silver;}
```

### Source Maps

Source Maps are useful in debugging compiled css files using browser developer tools.

To enable source maps, use the `setSourceMap()` and `setSourceMapOptions()` methods.

```php
use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();
$scss->setSourceMap(Compiler::SOURCE_MAP_FILE);
$scss->setSourceMapOptions([
    // absolute path to write .map file
    'sourceMapWriteTo'  => '/var/www/vhost/my-style.map',

    // relative or full url to the above .map file
    'sourceMapURL'      => 'content/themes/THEME/assets/css/my-style.map',

    // (optional) relative or full url to the .css file
    'sourceMapFilename' => 'my-style.css',

    // partial path (server root) removed (normalized) to create a relative url
    'sourceMapBasepath' => '/var/www/vhost',

    // (optional) prepended to 'source' field entries for relocating source files
    'sourceRoot'        => '/',
]);

// use Compiler::SOURCE_MAP_INLINE for inline (comment-based) source maps
```

### Custom Functions

It's possible to register custom functions written in PHP that can be called
from SCSS. Some possible applications include appending your assets directory
to a URL with an `asset-url` function, or converting image URLs to an embedded
data URI to reduce the number of requests on a page with a `data-uri` function.

We can add and remove functions using the methods `registerFunction` and
`unregisterFunction`.

* `registerFunction($functionName, $callable, $prototype)` assigns the callable value to
  the name `$functionName`. The name is normalized using the rules of SCSS,
  meaning underscores and dashes are interchangeable. If a function with the
  same name already exists then it is replaced. The optional `$prototype` is an
  array of parameter names.

* `unregisterFunction($functionName)` removes `$functionName` from the list of
  available functions.

The `$callable` can be anything that PHP knows how to call using
`call_user_func`. The function receives two arguments when invoked. The first
is an array of SCSS typed arguments that the function was sent. The second is an
array of SCSS values corresponding to keyword arguments (aka kwargs).

The SCSS *typed arguments* and *kwargs* are actually just arrays or Number objects
that represent SCSS values. SCSS has different types than PHP, and this is how
**scssphp** represents them internally.

There is a large variety of types. Experiment with a debugging function like `print_r`
to examine the possible inputs.

The return value of the custom function can either be a SCSS type or a basic
PHP type (such as a string or a number). If it's a PHP type, it will be converted
automatically to the corresponding SCSS type.

As an example, a function called `add-two` is registered, which adds two numbers
together. PHP's anonymous function syntax is used to define the function.

```php
use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();

$scss->registerFunction(
  'add-two',
  function($args) {
    list($a, $b) = $args;

    return $a[1] + $b[1];
  }
);

$scss->compile('.ex1 { result: add-two(10, 10); }');
```

Using a prototype and kwargs, functions can take named parameters. In this next example,
we register a function called `divide` which divides a named dividend by a named divisor.

```php
use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();

$scss->registerFunction(
  'divide',
  function($args, $kwargs) {
    return $kwargs['dividend'][1] / $kwargs['divisor'][1];
  },
  array('dividend', 'divisor')
);

$scss->compile('.ex2 { result: divide($divisor: 2, $dividend: 30); }');
```

Note: in the above examples, we lose the units of the number, and we
also don't do any type checking. This will have undefined results if we give it
anything other than two numbers.

### Security Considerations

If your web application compiles SCSS on-the-fly, you need to handle any potential
exceptions thrown by the Compiler. This is especially important in a production
environment where the content may be untrusted (e.g., user uploaded) because
the exception stack trace may contain sensitive data.

```php
use ScssPhp\ScssPhp\Compiler;

try {
    $scss = new Compiler();

    echo $scss->compile($content);
} catch (\Exception $e) {
    echo '';
    syslog(LOG_ERR, 'scssphp: Unable to compile content');
}
```

If your web application allows for arbitrary `@import` paths, you should
tighten the `open_basedir` setting at run-time to mitigate vulnerability to
local file inclusion (LFI) attack.

### Server Example

An example `Server` class is described here: <a href="{{ site.baseurl }}/docs/server.html">{{ site.baseurl }}/docs/server.html</a>.
