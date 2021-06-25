**scssphp** is a compiler for [SCSS][0] written in PHP.

SCSS is a CSS preprocessor language that adds many features like variables,
mixins, imports, nesting, color manipulation, functions, and control directives.

**scssphp** is ready for inclusion in any project. It includes a command
line tool for running the compiler from a terminal/shell or script.

<div class="github-buttons">
<iframe src="https://ghbtns.com/github-btn.html?user=scssphp&repo=scssphp&type=star&count=true" allowtransparency="true" frameborder="0" scrolling="0" width="150" height="20"></iframe>
<iframe src="https://ghbtns.com/github-btn.html?user=scssphp&repo=scssphp&type=fork&count=true" allowtransparency="true" frameborder="0" scrolling="0" width="150" height="20"></iframe>
</div>

<a name="installing"></a>

## Installing

You can always download the latest version here:
<a href="{{ site.repo_url }}/archive/{{ site.current_version }}.tar.gz" id="download-link">scssphp-{{ site.current_version }}.tar.gz</a>

You can also find the latest source online:
<{{ site.repo_url }}/>

If you use [Packagist][2] for installing packages, then you can update your
`composer.json` like so:

```json
{
    "require": {
        "scssphp/scssphp": "^{{ site.current_version |replace: 'v', '' }}"
    }
}
```

Note: git archives of stable versions no longer include the `tests/` folder.
To install the unit tests, download the complete package source using
`composer`'s `--prefer-source` option.

**scssphp** requires PHP version 5.6 (or above).

## Language Reference

For a complete guide to the syntax of SCSS, consult the [official documentation][1].

Note that **scssphp** is not fully compliant with the Sass specification yet.
Sass modules are not implemented yet either.

## Command Line Tool

A really basic command line tool is included for integration with scripts. It
is called `pscss`. It reads SCSS from either a named input file or standard in,
and returns the CSS to standard out.

Usage: `bin/pscss [options] [input-file] [output-file]`

### Options

If passed the flag `-h` (or `--help`), input is ignored and a summary of the
command's usage is returned.

If passed the flag `-v` (or `--version`), input is ignored and the current
version is returned.

The flag `-s` (or `--style`) can be used to set the
[output style](docs/#output-formatting):

```bash
$ bin/pscss -s compressed styles.scss
```

The flag `-I` (or `--load_path`) can be used to set import paths for the loader.
On Unix/Linux systems, the paths are colon separated. On Windows, they are
separated by a semi-colon.

## SCSSPHP Library Reference

To use the scssphp library either require `scss.inc.php` or use your `composer`
generated auto-loader, and then invoke the `\ScssPhp\ScssPhp\Compiler` class:

```php
require_once "scssphp/scss.inc.php";

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

<a name="issues"></a>

## Issues

Please submit bug reports and feature requests to the [the issue tracker][3].
Pull requests are also welcome.

Any feature request about implementing new language feature will be rejected.
They must be submitted to the upstream Sass project instead.

## Changelog

For a list of **scssphp** changes, refer to [the changelog](docs/changelog.md).

  [0]: https://sass-lang.com/
  [1]: https://sass-lang.com/documentation
  [2]: https://packagist.org/
  [3]: {{ site.repo_url }}/issues
