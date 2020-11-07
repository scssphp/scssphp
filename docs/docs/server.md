---
layout: default
title: Example
---

## Server Example

If you just want to start serving compiled `scss` files as quick as possible
then start here. The **scssphp/server** project provides an easy-to-use example
that demonstrates how to automatically compile `scss` files and serve them from
a directory that you specify.

Create a file, like `style.php`:

```php
use ScssPhp\Server\Server;

$directory = "stylesheets";

$server = new Server($directory);
$server->serve();
```

Create the directory set in the script alongside the script, then add your
`scss` files to it.

By default, **scssphp** expects a `scss_cache` directory to exist inside the
stylesheets directory where it will cache the compiled output. This way it can
quickly serve the files if no modifications have been made. Your PHP script
must have permission to write in `scss_cache`.

Going to the URL `example.com/style.php/style.scss` will attempt to compile
`style.scss` from the `stylesheets` directory, and serve it as CSS.

If it can not find the file it will return an HTTP 404 page:

```
/* INPUT NOT FOUND scss v0.0.1 */
```

If the file can't be compiled due to an error, then an HTTP 500 page is
returned. Similar to the following:

```
Parse error: failed at 'height: ;' stylesheets/test.scss on line 8
```

Also, because SCSS server writes headers, make sure no output is written before
it runs.

### Constructor

Use the `Server` constructor to specify the cache directory and even the
instance of the `Compiler` that is used to compile SCSS.

* `new Server($sourceDir, $cacheDir, $scss)` creates a new server that
  serves files from `$sourceDir`. The cache dir is where the cached compiled
  files are placed. When `null`, `$sourceDir . '/scss_cache'` is used. `$scss`
  is the instance of `scss` that is used to compile.

Just call the `serve` method to let it render its output.

Here's an example of creating a SCSS server that outputs compressed CSS:

```php
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;
use ScssPhp\Server\Server;

$scss = new Compiler();
$scss->setOutputStyle(OutputStyle::COMPRESSED);

$server = new Server('stylesheets', null, $scss);
$server->serve();
```
