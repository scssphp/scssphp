---
title: Documentation
---

# scssphp {{ site.current_version }} Documentation

## SCSSPHP Library

### Including

The project can be loaded through the `composer` generated auto-loader.

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

$compiler = new Compiler();

echo $compiler->compileString('
  $color: #abc;
  div { color: lighten($color, 20%); }
')->getCss();
```

`compileString($scssCode, $path = null)` will attempt to compile a string of
SCSS code. If it succeeds, a `\ScssPhp\ScssPhp\CompilationResult` containing the
CSS will be returned. If there is any error, a
`\ScssPhp\ScssPhp\Exception\SassException` is thrown with an appropriate error
message.

### Import Paths

When you import a file using the `@import` directive,
the import is resolved relatively to the current file. The input of `compileString`
is considered to be in the provided path. If no path is provided, relative imports
won't be resolved. Imports paths will need to be used.
In case you want to load files from other folders, there are two methods for
 manipulating the import path: `addImportPath`, and `setImportPaths`.

* `addImportPath($path)` will append `$path` to the list of the import
  paths that are searched.

* `setImportPaths($pathArray)` will replace the entire list of import paths with
  `$pathArray`. The value of `$pathArray` will be converted to an array if it
  isn't one already.

```php
use ScssPhp\ScssPhp\Compiler;

$compiler = new Compiler();
$compiler->setImportPaths('assets/stylesheets/');

// will search for 'assets/stylesheets/mixins.scss'
echo $compiler->compileString('@import "mixins.scss";')->getCss();
```

Besides adding static import paths, it's also possible to add
[custom import functions](./extending/importers.md).

A list of the included files can be retrieved using the `getIncludedFiles`
method of the `CompilationResult`. The input file is not included in this list.

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

$compiler = new Compiler();
$compiler->setSourceMap(Compiler::SOURCE_MAP_FILE);
$compiler->setSourceMapOptions([
    // relative or full url to the above .map file
    'sourceMapURL' => './my-style.map',

    // (optional) relative or full url to the .css file
    'sourceMapFilename' => 'my-style.css',

    // partial path (server root) removed (normalized) to create a relative url
    'sourceMapBasepath' => '/var/www/vhost',

    // (optional) prepended to 'source' field entries for relocating source files
    'sourceRoot' => '/',
]);

$result = $compiler->compileString('@import "sub.scss";');

file_put_contents('/var/www/vhost/my-style.map', $result->getSourceMap());
file_put_contents('/var/www/vhost/my-style.css', $result->getCss());

// use Compiler::SOURCE_MAP_INLINE for inline (comment-based) source maps
```

### Extending scssphp

The Compiler supports several extension points for advanced usages. They are
documented in [the documentation about extension points](./extending/).

### Security Considerations

If your web application compiles SCSS on-the-fly, you need to handle any potential
exceptions thrown by the Compiler. This is especially important in a production
environment where the content may be untrusted (e.g., user uploaded) because
the exception stack trace may contain sensitive data.

```php
use ScssPhp\ScssPhp\Compiler;

try {
    $compiler = new Compiler();

    echo $compiler->compileString($content)->getCss();
} catch (\Exception $e) {
    echo '';
    syslog(LOG_ERR, 'scssphp: Unable to compile content');
}
```

If your web application allows for arbitrary `@import` paths, you should
tighten the `open_basedir` setting at run-time to mitigate vulnerability to
local file inclusion (LFI) attack.
