# Customizing the resolution of importers

Custom resolution of imports can be implemented by passing a callable in
import paths.

Note: callable strings are not supported, as strings are treated as directory
names. If your resolution logic is implemented as a named PHP function, use
`\Closure::fromCallable` to wrap it (or a custom solution if using older PHP
versions that don't have that helper).

The callable will be called with a single string argument, which is the URI
being imported and should return the absolute path of a file or `null` if it
does not support that import.

For legacy reasons, custom importers are also called for CSS imports, allowing
to treat them like Sass imports. However, that behavior is deprecated. Custom
importers should check their input with `\ScssPhp\ScssPhp\Compiler::isCssImport`
and always return `null` for them.

```php
use ScssPhp\ScssPhp\Compiler;

$compiler = new Compiler();
$compiler->addImportPath(function($path) {
    if (Compiler::isCssImport($path)) {
        return null;
    }

    if (!file_exists('stylesheets/'.$path)) {
        return null;
    }

    return __DIR__.'/stylesheets/'.$path;
});

// will import 'stylesheets/sub.scss'
echo $compiler->compileString('@import "sub.scss";')->getCss();
```
