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

{% highlight php startinline=true %}
require_once 'scssphp/scss.inc.php';
{% endhighlight %}

### Compiling

In order to manually compile code from PHP you must create an instance of the
`Compiler` class. The typical flow is to create the instance, set any compile time
options, then run the compiler with the `compile` method.

{% highlight php startinline=true %}
use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();

echo $scss->compile('
  $color: #abc;
  div { color: lighten($color, 20%); }
');
{% endhighlight %}

* `compile($scssCode)` will attempt to compile a string of SCSS code. If it
  succeeds, the CSS will be returned as a string. If there is any error, an
  exception is thrown with an appropriate error message.

### Import Paths

When you import a file using the `{% raw %}@{% endraw %}import` directive, the current path of your
PHP script is used as the search path by default. This is often not what
you want, so there are two methods for manipulating the import path:
`addImportPath`, and `setImportPaths`.

* `addImportPath($path)` will append `$path` to the list of the import
  paths that are searched.

* `setImportPaths($pathArray)` will replace the entire import path with
  `$pathArray`. The value of `$pathArray` will be converted to an array if it
  isn't one already.

If the import path is set to `array()` then importing is effectively disabled.
The default import path is `array('')`, which means the current directory.

{% highlight php startinline=true %}
use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();
$scss->setImportPaths('assets/stylesheets/');

// will search for 'assets/stylesheets/mixins.scss'
echo $scss->compile('{% raw %}@{% endraw %}import "mixins.scss";');
{% endhighlight %}

Besides adding static import paths, it's also possible to add custom import
functions. This allows you to load paths from a database, or HTTP, or using
files that SCSS would otherwise not process (such as vanilla CSS imports).

{% highlight php startinline=true %}
use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();
$scss->addImportPath(function($path) {
    if (!file_exists('stylesheets/'.$path)) return null;
    return 'stylesheets/'.$path;
});

// will import 'stylesheets/vanilla.css'
echo $scss->compile('{% raw %}@{% endraw %}import "vanilla.css";');
{% endhighlight %}

A list of the compiled files (both the primary file and its imports) can be
retrieved using the `getParsedFiles` method.

* `getParsedFiles()` returns an associative array where the keys are
  the file names and the values are the corresponding file's last-modified
  timestamp.

### Preset Variables

You can preset variables before compilation by using the `setVariables($vars)`
method. If the variable is also defined in your scss source, use the `!default`
flag to prevent your preset variables from being overridden.

{% highlight php startinline=true %}
use ScssPhp\ScssPhp\Compiler;
-
$scss = new Compiler();
$scss->setVariables(array(
    'var' => 'false',
));

echo $scss->compile('$var: true !default;');
{% endhighlight %}

Note: the value is the scss source to be parsed. If you want to parse a string,
you have to represent it as a string, e.g. `'var' => '"string"'`.

Likewise, you can retrieve the preset variables using the `getVariables()`
method, and unset a variable using the `unsetVariable($name)` method.

### Output Formatting

It's possible to customize the formatting of the output CSS by changing the
default formatter.

Five formatters are included:

* `ScssPhp\ScssPhp\Formatter\Expanded` *(default)*
* `ScssPhp\ScssPhp\Formatter\Nested`
* `ScssPhp\ScssPhp\Formatter\Compressed`
* `ScssPhp\ScssPhp\Formatter\Compact`
* `ScssPhp\ScssPhp\Formatter\Crunched`

We can change the formatting using the `setFormatter` method.

* `setFormatter($formatterName)` sets the current formatter to `$formatterName`,
  the name of a class as a string that implements the formatting interface. See
  the source for `ScssPhp\ScssPhp\Formatter\Expanded` for an example.

Given the following SCSS:

{% highlight scss %}
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
{% endhighlight %}

The formatters output the following:

`ScssPhp\ScssPhp\Formatter\Expanded`:

{% highlight css %}
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
{% endhighlight %}

`ScssPhp\ScssPhp\Formatter\Nested`:

{% highlight css %}
/*! Comment */
.navigation ul {
  line-height: 20px;
  color: blue; }
    .navigation ul a {
      color: red; }

.footer .copyright {
  color: silver; }
{% endhighlight %}

`ScssPhp\ScssPhp\Formatter\Compact`:

{% highlight css %}
/*! Comment */
.navigation ul { line-height:20px; color:blue; }

.navigation ul a { color:red; }

.footer .copyright { color:silver; }
{% endhighlight %}

`ScssPhp\ScssPhp\Formatter\Compressed`:

{% highlight css %}
/* Comment*/.navigation ul{line-height:20px;color:blue;}.navigation ul a{color:red;}.footer .copyright{color:silver;}
{% endhighlight %}

`ScssPhp\ScssPhp\Formatter\Crunched`:

{% highlight css %}
.navigation ul{line-height:20px;color:blue;}.navigation ul a{color:red;}.footer .copyright{color:silver;}
{% endhighlight %}

### Source Maps

Source Maps are useful in debugging compiled css files using browser developer tools.

To enable source maps, use the `setSourceMap()` and `setSourceMapOptions()` methods.

{% highlight php startinline=true %}
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
{% endhighlight %}

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

{% highlight php startinline=true %}
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
{% endhighlight %}

Using a prototype and kwargs, functions can take named parameters. In this next example,
we register a function called `divide` which divides a named dividend by a named divisor.

{% highlight php startinline=true %}
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
{% endhighlight %}

Note: in the above examples, we lose the units of the number, and we
also don't do any type checking. This will have undefined results if we give it
anything other than two numbers.

### Security Considerations

If your web application compiles SCSS on-the-fly, you need to handle any potential
exceptions thrown by the Compiler. This is especially important in a production
environment where the content may be untrusted (e.g., user uploaded) because
the exception stack trace may contain sensitive data.

{% highlight php startinline=true %}
use ScssPhp\ScssPhp\Compiler;

try {
    $scss = new Compiler();

    echo $scss->compile($content);
} catch (\Exception $e) {
    echo '';
    syslog(LOG_ERR, 'scssphp: Unable to compile content');
}
{% endhighlight %}

If your web application allows for arbitrary `@import` paths, you should
tighten the `open_basedir` setting at run-time to mitigate vulnerability to
local file inclusion (LFI) attack.

### Server Example

An example `Server` class is described here: <a href="{{ site.baseurl }}/docs/server.html">{{ site.baseurl }}/docs/server.html</a>.
