**scssphp** is a compiler for [Sass][0] written in PHP.

Sass is a CSS preprocessor language that adds many features like variables,
mixins, imports, nesting, color manipulation, functions, and control directives.

**scssphp** is ready for inclusion in any project.

<div class="github-buttons">
<iframe src="https://ghbtns.com/github-btn.html?user=scssphp&repo=scssphp&type=star&count=true" allowtransparency="true" frameborder="0" scrolling="0" width="150" height="20"></iframe>
<iframe src="https://ghbtns.com/github-btn.html?user=scssphp&repo=scssphp&type=fork&count=true" allowtransparency="true" frameborder="0" scrolling="0" width="150" height="20"></iframe>
</div>

## Installing

The officially supported way of installing **scssphp** is by using [Composer][2]:

```bash
composer require scssphp/scssphp "^{{ site.current_version |replace: 'v', '' }}"
```

**scssphp** requires PHP version 8.1 (or above).

## Language Reference

For a complete guide to the syntax of Sass, consult the [official documentation][1].

Note that **scssphp** does not support Sass modules yet.

## SCSSPHP Library Reference

To use the scssphp library use your `composer` generated autoloader, and then
invoke the `\ScssPhp\ScssPhp\Compiler` class:

```php
require "vendor/autoload.php";

use ScssPhp\ScssPhp\Compiler;

$compiler = new Compiler();

echo $compiler->compileString('
  $color: #abc;
  div { color: lighten($color, 20%); }
')->getCss();
```

The `compileString` method takes the `SCSS` source code as a string and an
optional path of the input file (to resolve relative imports), and returns
a `CompilationResult` value object containing the CSS and some additional
data. If there is an error when compiling, a `\ScssPhp\ScssPhp\Exception\SassException`
is thrown with an appropriate message.

For a more detailed guide, [consult the documentation](docs/).

## Issues

Please submit bug reports and feature requests to [the issue tracker][3].
Pull requests are also welcome.

Any feature request about implementing new language feature will be rejected.
They must be submitted to the upstream Sass project instead.

## Changelog

For a list of **scssphp** changes, refer to [the changelog](docs/changelog.md).

  [0]: https://sass-lang.com/
  [1]: https://sass-lang.com/documentation
  [2]: https://getcomposer.org/
  [3]: {{ site.repo_url }}/issues
