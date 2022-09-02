# Changelog

## **1.11.0** -- September 2, 2022

**Deprecated**

* Deprecate passing a limit with unit to `random()` (@stof)

**Added**

* Add support for empty fallback in `var()` (@stof)

**Fixed**

* Fix the handling of mixed case operators in media queries (@stof)

**Changed**

* Improve error messages for incorrect units in color functions (@stof)

## **1.10.5** -- July 27, 2022

**Fixed**

* Fix the handling of non-integer numbers in `nth` and `set-nth` (@max-m)

## **1.10.4** -- July 26,2022

**Fixed**

* Remove false positive deprecation warnings when compiling Bootstrap 5.2.0 (@stof)

**Changed**

* Remove usage of interpolation syntax deprecated in PHP 8.2 (@shyim)

## **1.10.3** -- May 16, 2022

**Fixed**

* Fix the handling of nested at-root in mixins (@stof)

**Changed**

* Mark the logger implementations as `@final` to prepare for 2.0 (@stof)

## **1.10.2** -- March 2, 2022

**Fixed**

* Fix the tracking of the location of comments when using sourcemaps (@stof)
* Fix the leaking of an output buffer in case of error during the formatting of the output (@stof)

## **1.10.1** -- February 28, 2022

**Fixed**

* Fix the handling of `rgb`, `rgba`, `hsl` and `hsla` called with a trailing comma in arguments (@stof)
* Fix the handling of negative index in `str-insert` (@stof)

## **1.10.0** -- January 6, 2022

**Fixed**

* Fix the generation of source maps without an input path (@stof)
* Fix the handling of list separators in list functions (@stof)
* Add explicit return types to avoid warnings from the Symfony DebugClassLoader (@shyim)

**Changed**

* Use double quotes to render strings in the generated CSS by default, to match dart-sass (@stof)
* Remove usage of dynamic properties to avoid deprecation warnings in PHP 8.2-dev (@stof)
* Change the order of selectors in the output of `selector-append` to match dart-sass (@stof)
* Mark AST-only types as internal in `\ScssPhp\ScssPhp\Type` (@stof)
* Preserve the `!` for preserved comments in the compressed output, to match dart-sass (@stof)

## **1.9.0** -- December 13, 2021

**Added**

* Add support for deep map manipulation in `map-get`, `map-has-key` and `map-merge` (@stof)

**Fixed**

* Preserve the type of keys when iterating over maps (@stof)

## **1.8.1** -- September 18, 2021

**Added**

* Fix compatibility with PHP 8.1 (@TimWolla, @stof)

## **1.8.0** -- September 18, 2021

**Deprecated**

* Deprecate calling core functions with non-standard names. Due to the internal implementation of core functions, they were calling with different cases or different positions of `-` or `_` in the names (@stof)

**Fixed**

* Fix the computation of the hue of colors for some cases (@stof)

## **1.7.0** -- September 15, 2021

**Added**

* Add support for a `charset` option to omit the charset declaration from the generated CSS (@stof)

**Changed**

* Add spec-compliant validation of arguments in several color functions (@stof)

**Fixed**

* Fix the handling of CSS variables in `rgba()` (@stof)
* Fix the handling of unitless weights in `mix()` and `invert()` (@stof)
* Fix the handling of amounts in `saturate()`, `desaturate()`, `fade-in()` and `fade-out()` (@stof)
* Fix the handling of `@charset` in the Sass source (@stof)
* Fix wrong deprecation warning about unterminated interpolation in discarded comments (@stof)
* Fix file paths in error messages on Windows (@stof)

## **1.6.0** -- June 30, 2021

**Deprecated:**

* Deprecate extending compound selectors, as that's unsupported by dart-sass (@stof)

**Fixed:**

* Fixed the parsing of expressions with alterning operator precedence, which fixes support for compiling Bootstrap 5.0.2 (@stof)

## **1.5.2** -- May 18, 2021

**Fixed:**

* Fix the implementation of the `Compiler::getStringText` helper (@stof)
* Fix the handling of compiling without an input path, to avoid deprecations on PHP 8.1 (@stof)
* Revert the 1.5.0 fix for list indexes in `nth` and `setNth` as other spec compliance issues for list are making it break the compilation of Bootstrap 4.6 (@stof)

## **1.5.1** -- May 17, 2021

**Fixed:**

* Fix computation on colors to always return integer values for RGB channels (@stof)

## **1.5.0** -- May 14, 2021

**Deprecated:**

* Deprecate the `compile` method relying on stateful getters for included files (@stof)
* Deprecate extending the Compiler class to register custom functions. Use `registerFunction` instead (@stof)
* Deprecate overriding core functions through `registerFunction` (@stof)
* Deprecate returning a file path in custom importers for CSS imports (@stof)
* Deprecate `Compiler::setEncoding` as it was not implemented in a compliant way. Only UTF-8 is supported now (@stof)
* Deprecate the `--dump-tree` option of the pscss CLI (@stof)
* Deprecate the `scssphp-glob` function (@stof)
* Deprecate the `Compiler::setVariables` method (@stof)
* Deprecate passing non-converted values when registering variables (@stof)
* Deprecate returning a PHP value rather than a Sass value from custom functions (@stof)
* Deprecate registering custom functions without an argument declaration (@stof)
* Deprecate the `@scssphp-import-once` directive (@stof)
* Deprecate non-standard support for broken interpolation in loud comments (@stof)
* Deprecate `\ScssPhp\ScssPhp\Exception\ServerException` (@stof)

**Added:**

* Add support for writing the output to a file in pscss (@stof)
* Add support for writing sourcemaps to a file in pscss (@stof)
* Add support for embedding sources in the sourcemap in pscss (@stof)
* Add support for `$blackness` and `$whiteness` in `adjust-color`, `change-color` and `scale-color` (@Cerdic)
* Add a new `compileString` method returning a `CompilationResult` (@Cerdic, @stof)
* Add a new `checkImportResolutions` cache option to invalidate the compilation cache if imports would resolve differently (@Cerdic)
* Add a `LoggerInterface` to customize the handling of warning and debug messages (@stof)
* Add the `\ScssPhp\ScssPhp\Warn` API to report warnings in custom functions (@stof)
* Add `Compiler::replaceVariables` and `Compiler::addVariables` to manage custom variables (@stof)
* Add the `\ScssPhp\ScssPhp\ValueConverter` to produce values in the Sass value representation (@stof)
* Add `Compiler::getStringText` to get the text of a Sass string (@stof)
* Add `Compiler::getArgumentListKeywords` to get the keyword arguments of a Sass argument list (@stof)
* Add `Compiler::isCssImport` to allow custom importers to skip CSS imports (@stof)
* Add documentation about extending the library (@stof)

**Changed:**

* Add type checks for arguments of core functions (@Cerdic, @stof)
* Refactor the processing of function arguments to be more spec compliant (@stof)
* Forbid unsupported selectors in the `@extend` directive instead of producing a non-standard behavior (@stof)
* Take into account `.sass` files during import resolution to avoid selecting a different file than dart-sass (@stof)
* Change the internal representation of arguments lists to make them compliant lists (@stof)
* Tagged all internal APIs with `@internal` to exclude them from the backward compatibility surface (@stof)
* Change the error reporting for `Compiler::assert*` helpers to be consistent (@stof)
* Change `scss.inc.php` to register an autoloader rather than loading all classes eagerly (@stof)
* Improve the phpdoc of the library, with advanced type declarations for phpstan (@stof)

**Removed:**

* Remove support for running (in a non-compliant way) without mbstring and iconv. Either mbstring or iconv is now required (@stof)
* Remove non-standard support for ignoring HTML comment delimiters (but not the content of the comment) during parsing (@stof)

**Fixed:**

* Fix the handling of units and bounds in for loop (@stof)
* Fix the handling of interpolation in expressions (@stof)
* Fix the implementation of `str-slice` for non-ASCII chars (@stof)
* Fix the handling of `min` and `max` without arguments (@stof)
* Fix the implementation of `keywords()` for functions called with positional arguments (@stof)
* Fix the handling of list indexes in `nth` and `set-nth` (@stof)
* Fix usage of `preg_match` flags avoid passing `null` (@stof)

## **1.4.1** -- Jan 4, 2021

* Fix support for absolute paths in imports (stof)
* Fix support for custom properties in plain CSS imports (willpower232)
* Fix the BC layer for cwd-based import resolution to support code disabling it in the old API (stof)
* Fix sourcemaps for the compressed output (stof)
* Fix the escaping in selectors (Cerdic)
* Add the library version as a cache busting criteria (Cerdic)
* Fix the parser to apply `realpath` to the path used for error reporting as well (Cerdic)
* Fix the phpdoc in the Compiler (stof)

## **1.4** -- Nov 7, 2020

* fix the injection of the `@charset` rule without mbstring (stof)
* Add a CI job running tests without mbstring (stof)
* Stop changing current directory in pscss (stof)
* Refactor the resolution of imports to be spec compliant (stof)
* Add a factory method for SassScriptException with an argument name (stof)
* Expose `SassScriptException` as a non internal class (stof)
* Fix regression with whitespaces or comments at the beginning of interpolated selectors (Cerdic)
* Fix the implementation of `to-uppercase` and `to-lowercase` to avoid being locale dependant (Cerdic)
* Add a better error rendering of sass errors in `pscss` (stof)
* Deprecate `setFormatter` in favor of `setOutputStyle` (stof)
* Deprecate all formatters except `Expanded` and `Compressed`(stof)
* Change the default formatter to be `Expanded` (stof)
* Migrate CI to github actions (stof, robocoder)
* Fix the generation of sourcemaps (stof)
* Adjust the source map to account for the charset prefix (stof)
* Improve the phpdoc (stof)
* Fix the behavior of `str-index` (stof)
* Deprecate color arithmetic (stof)
* Fix spec compliance for the `call` function (stof)
* Fix the matching of the space ending an escape sequence (stof)
* Fix the behavior of `to-uppercase` and `to-lowercase` (stof)
* Fix the implementation of `==` and `!=` between number and colors (stof)
* Fix the implementation of modulo (stof)
* Mark the `units-level-3` feature as implemented (stof)

## **1.3** -- Oct 29, 2020
  * Better `quote()` compliance (Cerdic)
  * Improve string compliance with sass-spec (Cerdic)
  * Fix issue with argument values being swapped (jljr222, Cerdic)
  * Fix parsing of comment in selector list (Daijobou, Cerdic)
  * Fix for double dash in class names (janstieler, Cerdic)
  * Drop support for  `/foo/` selector (Cerdic)
  * Fix compatibility issues with PHP 5.6 and 7.2 (stof)
  * Migrate from throwParseError to parseError factory (stof)
  * Refactor Number (to be continued) (stof)
  * Remove dead code, support for numbered output, and ruby-sass tests (stof)
  * Remove experimental spaceship operator and `@break` and `@continue` (stof)
  * Deprecate `Compiler::addFeature()` (stof)
  * Move `gh-pages` to `/docs` folder on main branch (stof, robocoder)
  * Add php 8 support for phpunit (adlenton)

## **1.2.1** -- Sep 7, 2020
  * Fix `@import url()` parsing (leonardfischer, Cerdic)
  * Fix various directive parsing issues (zoglo, CatoTH, Cerdic)
  * Fix `min()`, `max()` (Cerdic)
  * Fix `str-length()`, `str-index()`, and `str-insert()` (Cerdic)
  * Fix `is-superselector()` and other select issues (Cerdic)
  * Fix `call()` argument name (Cerdic)
  * Fix `random()` (Cerdic)
  * Fix `list-separator()` on empty or one element list (Cerdic)

## **1.2** -- Aug 26, 2020
  * Many, many sass-spec test improvements (stof, Cerdic)
  * Partial fix of special cases in hsl/hsla functions (Cerdic)
  * In certain interpolations, the spec seems to prefer to force a double quote for output strings (Cerdic)
  * Fix list separated values with no delimiter with a keyword between two strings (Cerdic)
  * Fix spaces escaping in `@import` path strings (Cerdic)
  * Fix single/double quote escaping in single/double scope strings (stof)
  * Add polyfill for `mb_chr` (stof)
  * Refactor handling of stirngs and escape sequences (stof)
  * Fix the enclosedList parsing in a more generic way (Cerdic)
  * Parser: explicitly flatten where expected (Cerdic)
  * Color function can be called with a var(..) argument (Cerdic)
  * Throw an error when passing too many arguments (stof, Cerdic)
  * Don't coerce anything into a map but throw an error if it's not at all matching a map (Cerdic)
  * In the `@atroot (#{with: media})` the interpolation has to be reparsed in the compiler before trying to manipulate as a map (Cerdic)
  * Throw an error if positional and named passed, even on a splat... argument, except if this is the only one (Cerdic)
  * Fix map-remove() : second argument key can be a list of arguments (Cerdic)
  * Fix parsing value list in function call made of value list of 1 element (Cerdic)
  * Fix name of arguments on functions `mix(color1,color2)`, `map-merge()`, `comparable()`, `selector-extend()`, `selector-replace()`, `selector-parse()` (Cerdic)
  * Fix `saturate(50%)` (Cerdic)
  * Throw an error if a value that should be in a range is not a numeric value (Cerdic)
  * Add error handling for invalid type in some color functions (Cerdic)
  * Compiler: deprecated `throwError()` (stof)
  * Remove ignoreErrors mode (stof)
  * `--&` is a valid custom property (where `&` should be interpreted as self selector) (Cerdic)
  * Replace self selector in target part before the pushExtends() (Cerdic)
  * Add SassException interface (stof)
  * Add a proper error when trying to take a modulo by 0 (stof)
  * Properly detect the wrong operands for `for` loops (stof)
  * Fix the parsing of while(false) loop (stof)
  * Drop support for configuring precision (stof)
  * `bin/pscss` deprecated `--continue-on-error` and --`precision` (stof)
  * Deprecation warning when call() is used with a string (Cerdic)
  * Introducing scss get-function() and T_FUNCTION_REFERENCE type (Cerdic)
  * Reorder color names (stof)
  * Change output order for nested selectors (stof)
  * Simplify the handling of comments to be more spec-compliant (stof)
  * Refactor the format of the output for debug and warn directives (stof)
  * Discard comments in include arguments (stof)
  * Fix parsing of id tokens in values (stof)
  * Respect precision when computing alpha channel (stof)
  * Compiler: fix undefined offset 2 (chrisblakley)
  * Fix keyframe parsing in css files (dwachss, Cerdic)
  * In plain CSS, a property can only occur in a selector (ryssbowh, Cerdic)
  * Compiler: add `getSourcePosition()` (ryssbowh, robocoder)
  * ParserException: add sourcePosition getter/setter (cbl, robocoder)
  * Cache: cache directory must exist and be writeable (robocoder)
  * Update sass-spec tests (2020.08.20)
  * Update to PSR-12 (robocoder)
  * Add php 8 nightly to Travis CI (robocoder)

## **1.1.1** -- Jun 4, 2020
  * Fix extend and class concatenation (develth, Cerdic)
  * Fix arguments selector issue (stempora, Cerdic)
  * Fix regression when members units are not normalizable (jszczypk, Cerdic)
  * Remove box.json.dist from .gitattributes (reedy)
  * 32-bit fixes for Base64VLQ `encode()` and `unique-id()` (remicollet, robocoder)
  * Fix index of map within list of maps (stempora, robocoder)

## **1.1.0** -- Apr 21, 2020
  * Fix the handling of call traces for exceptions of native functions (stof)
  * Add named call stack entries for imports (stof)
  * Fix leaks in the call stack (stof)
  * Qualify function calls when the compiler can optimize them (stof)
  * Remove deprecated Parser::to() and Parser::show() methods (robocoder)

## **1.0.9** -- Apr 1, 2020
  * Fix parsing issues around `#, +, -, --` (Cerdic)
  * Fix `@import` compatibility (Cerdic)
  * Add vendor-prefixed `scssphp-glob()` function (havutcuoglu, robocoder)
  * Remove PHP version and mbstring.func_overload checks (KryukovDS, robocoder)
  * Fix multiple issues with Bootstrap 4.4.1 and master (fuzegit, Cerdic)
  * Fix variables interpolation bug (Seonic, Cerdic)

## **1.0.8** -- Feb 20, 2020
  * Import of valid scss files fails silently (oyejorge, Cerdic)
  * Undefined $libName (enricobono, robocoder)
  * Fix division and modulo per sass-spec (Cerdic)
  * Fix expressions in at directives (Cerdic)
  * Introduce support for custom properties (Cerdic)
  * Function compatibility issues with functions (abs, ceil, floor, max, min, percentage, random, round), units, and conversions. (Cerdic)

## **1.0.7** -- Jan 31, 2020
  * Fix problem with Bootstrap 4.4 / Responsive containers (nvindice, Cerdic)
  * Fix issue with pseudoelement selectors order in `@extend`'ed elements (CrazyManLabs, Cerdic)
  * `example/Server.php` moved to https://github.com/scssphp/server

## **1.0.6** -- Dec 12, 2019
  * Many sass-spec compatibility fixes (Cerdic)
  * Discriminate shorthands vs real divisions in border-radius property (joakipe, Cerdic)
  * Base64VLQ - 32-bit overflow fixes from Closure implementation (remicollet, robocoder)
  * Formatter for nested properties removes semicolon (Mythos07, Cerdic)
  * Variables scope issues (jducro, Cerdic)
  * Using `@extend` creates invalid output with nested classnames (bmbrands, Cerdic)
  * Fixed sourceMapGenerator bug if semicolons are stripped. (ugogon)

## **1.0.5** -- Oct 3, 2019
  * interpolation fixes (Cerdic)
  * phpunit test updates (stof)
  * undefined sourceIndex (connerbw, robocoder)
  * using is_null(), is_dir(), is_file() for consistency (robocoder)

## **1.0.4** -- Sep 6, 2019
  * `border-radius` shorthand support (alex-shul, Cerdic)
  * allow `zip()` function to use all types as arguments (devdot, Cerdic)
  * `@each` forcing unwanted type conversion (devdot)
  * `rgb()` and colour compatibility improvements (Cerdic)
  * `str-splice` broken in php 7.4
  * composer and travis configuration updates
  * remove obsolete `Base64VLQEncoder` class

## **1.0.3** -- Aug 7, 2019
  * `@at-root`, `@import`, and `url(//host/image.png)` fixes (Cerdic)
  * join operator with interpolated values vs vars or static values (julienmru, Cerdic)
  * Implemented passing Arguments to Content Blocks (jensniedling, Cerdic)
  * Support whitespaces inside :not() (schliesser)
  * Compile non-roots comments also (fabsn182, Cerdic)

## **1.0.2** -- July 6, 2019
  * Version: actually bump the version number

## **1.0.1** -- July 6, 2019
  * Fix iteration on map (alexsaalberg049 , Cerdic)
  * More compatibility with reference implementation (Cerdic)
  * Cache: bump `CACHE_VERSION` (Cerdic)
  * `bin/pscss` requires php 5.6+ (robocder)
  * travis updates and improved tests (Cerdic)
  * Nested formatted improvements (Cerdic)

## **1.0.0** -- June 4, 2019
  * Moving development to ScssPhp organization, https://github.com/scssphp/
  * Online documentation can be found at http://scssphp.github.com/scssphp/
  * Renamed namespace from Leafo to ScssPhp

## **0.8.4** -- June 18, 2019
  * This is the final tag on the leafo/scssphp repo; PHP requirements downgraded to 5.4+ for this repo/package only.
  * Support parent selector and selector functions (Cerdic)
  * Improve `and`/`or` compatibility (robocoder)
  * Backslash newline fix (Netmosfera, Cerdic)
  * Variable nesting/scoping issue (dleffler, Cerdic)
  * Interpolation in block comments (vicary, Cerdic)
  * Parser should match some utf8 symbols (ostrolucky, Cerdic)
  * Incorrectly evaluating expressions within Unicode range (timknight, Cerdic)
  * Problem with first comment on ampersand-nested class (blackgearit, Cerdic)
  * Parsing missing http(s) protocol from `url()` (sebastianwebb, robocoder)
  * Add source column to thrown error message (slprime, robocoder)
  * Detect invalid CSS outside of selector (JMitnik, robocoder)

## **0.8.3** -- May 31, 2019
  * grid-template-columns (gKreator, Cerdic)
  * `self` in selector and parse improvements (designerno1, Cerdic)
  * invalid css output when using interpolation with mixins (Jasonkoolman, Cerdic)
  * parser error for `@each $selector in & {...}` (wesleyvicthor, Cerdic)
  * `@extend` in extended class or placeholder, doesn't produce extended selector (dimitrov-adrian, Cerdic)
  * weird `@extend` behavior (Kenneth-KT, Cerdic)
  * nested selector issue (ruby vs libsass difference) (designerno1, Cerdic)
  * `pscss` exhausts memory (gsmith-daed, Cerdic)
  * infinite loop compiling mixin with nested `@content` (exigon, Cerdic)
  * nested media queries error (arnoschaefer, Cerdic)
  * set upper bound for php version requirement (staabm)
  * "crunched" formatter features (Daijobou, Cerdic)
  * line comments for `@media` statements (gajcapuder, Cerdic)
  * failed interpolation in placeholder (GuidoJansen, Cerdic)
  * parentheses in selector causes loss of whitespace (Netmosfera, Cerdic)

## **0.8.2** -- May 9, 2019
  * requires php 5.6+

## **0.8.1** -- May 9, 2019
  * grid-row & grid-column shorthand (claytron5000, Cerdic)
  * `@`mixin `@`supports `@`include compilation error (geoidesic, Cerdic)
  * `@`media expression slicing (tdutrion, Cerdic)
  * `@`font-face fix (bloep, Cerdic)
  * mixin crash fix (LucasSbBrands, Cerdic)
  * bracketed lists don't compile (pkerling, Cerdic)
  * wrap successive inline assign into one block (Cerdic)
  * :not(), :nth-child() and other selectors before `@`extend (STV11C, Cerdic)
  * commentsSeen and phpdoc update (nextend)

## **0.8.0** -- May 2, 2019
  * Variables from inner override variables in parents (Daijobou, Cerdic)
  * Bootstrap issues with `@`at-root, self (l2a, Cerdic)
  * `@`supports inside rule (Marat-Tanalin, Cerdic)
  * SourceMapGenerator Former: invalid offset (fabsn182)
  * Number parsing (ange007, robocoder)
  * Travis test updates (Cerdic)
  * Add Bootstrap and Foundation framework tests (Cerdic)

## **0.7.8** -- April 24, 2019
  * Partial support for #rrggbbaa CSS Level 4 colors with alpha (charlymz)
  * Avoid infinitely duplicating parts when extending selector (cyberalien)
  * Fix rooted SCSS URIs normalized incorrectly with double slashes (evanceit)
  * Coding style updates (BrainFooLong)
  * Interpolation support selector (jakejohns, Cerdic)
  * Improve error messages (gabor-udvari, Cerdic)
  * Fix font shorthand syntax (JanST123, Cerdic)
  * Peephole optimizations (oyejorge, Cerdic)
  * Compiler: change some private properties/methods to protected (cyberalien)
  * Fix for "continue" causing PHP 7.3 warning (darkain)
  * Fix error thrown from strpos if needle (basePath) is empty (evanceit)
  * Fix doc for addImportPath, should also accept callable as input (nguyenk)
  * Change Base64 VLQ encoder/decoder implementation
  * Generate inline sourcemap in command-line (dexxa)
  * Fix backslash escape (bastianjoel)

## **0.7.7** -- July 21, 2018
  * Actually merge maps instead of concatenating (s7eph4n)
  * Treat 0 as special unitless number (of2607)
  * Partial fix for call() with ellipsis (gabor-udvari)
  * Misc peephole optimization

## **0.7.6** -- May 23, 2018
  * `mix()` alpha fix (Uriziel01)
  * `transparentize()` alpha sensitive to locale (leonardfischer, timelsass)
  * notices when compiling UIKit (azjezz)
  * faster parsing for base64 data: url()s (wout)

## **0.7.5** -- February 8, 2018
  * Fix `for` loop with units (of2607)
  * Fix side-effects in abs(), ceil(), floor(), and round() (jugyhead)
  * Add option for custom SourceMapGenerator (dleffler)

## **0.7.4** -- December 21, 2017
  * Fat fingered cleanup; broke source maps (dleffler)

## **0.7.3** -- December 19, 2017
  * Add inline sourcemaps (oyejorge, NicolaF)
  * Add file-based sourcemaps (dleffler)

## **0.7.2** -- December 14, 2017
  * Change default precision to 10 to match scss 3.5.0
  * Use number_format instead of locale (Arlisaha)

## **0.7.1** -- October 13, 2017
  * Server moved to exoample/ folder
  * Server::serveFrom() helper removed
  * Removed .phar build
  * Workaround `each()` deprecated in PHP 7.2RC (marinaglancy)

## **0.6.7** -- February 23, 2017
  * fix list interpolation
  * pscss: enable --line-numbers and --debug-info for stdin
  * checkRange() throws RangeException

## **0.6.6** -- September 10, 2016
  * Do not extend decorated tags with another tag (FMCorz)
  * Merge shared direct relationship when extending (FMCorz)
  * Extend resolution was generating invalid selectors (FMCorz)
  * Resolve function arguments using mixin content scope (FMCorz)
  * Let `@`content work when a block isn’t passed in. (diemer)

## **0.6.5** -- June 20, 2016
  * ignore BOM (nwiborg)
  * fix another mixin and variable scope issue (mahagr)
  * Compiler: coerceValue support for #rgb values (thesjg)
  * preserve un-normalized variable name for error message (kissifrot)

## **0.6.4** -- June 15, 2016
  * parsing multiple assignment flags (Limych)
  * `@`warn should not write to stdout (atomicalnet)
  * evaluating null and/or 'foo' (micranet)
  * case insensitive directives regression (Limych)
  * Compiler: scope change to some properties and methods to facilitate subclassing (jo)

## **0.6.3** -- January 14, 2016
  * extend + parent + placeholder fix (atna)
  * nested content infinite loop (Lusito)
  * only divide by 100 if percent (jkrehm)
  * Parser: refactoring and performance optimizations (oyejorge)

## **0.6.2** -- December 16, 2015
  * bin/pscss --iso8859-1
  * add rebeccapurple (from css color draft)
  * improve utf-8 support

## **0.6.1** -- December 13, 2015
  * bin/pscss --continue-on-error
  * fix BEM and `@`extend infinite loop
  * Compiler: setIgnoreErrors(boolean)
  * exception refactoring
  * implement `@`extend !optional and `keywords($args)` built-in

## **0.6.0** -- December 5, 2015
  * handle escaped quotes inside quoted strings (with and without interpolation present)
  * Compiler: undefined sourceParser when re-using a single Compiler instance
  * Parser: `getLineNo()` removed

## **0.5.1** -- November 11, 2015
  * `@`scssphp-import-once
  * avoid notices with custom error handlers that don't check if `error_reporting()` returns 0

## **0.5.0** -- November 11, 2015
  * Raise minimum supported version to PHP 5.4
  * Drop HHVM support/hacks
  * Remove deprecated classmap.php
  * Node\Number units reimplemented as array
  * Compiler: treat `! null === true`
  * Compiler: `str-splice()` fixes
  * Node\Number: fixes incompatible units

## **0.4.0** -- November 8, 2015
  * Parser: remove deprecated `show()` and `to()` methods
  * Parser, Compiler: convert stdClass to Block, Node, and OutputBlock abstractions
  * New control directives: `@`break, `@`continue, and naked `@`return
  * New operator: `<=>` (spaceship) operator
  * Compiler: `index()` - coerce first argument to list
  * Compiler/Parser: fix `@`media nested in mixin
  * Compiler: output literal string instead of division-by-zero exception
  * Compiler: `str-slice()` - handle negative index
  * Compiler: pass kwargs to built-ins and user registered functions as 2nd argument (instead of Compiler instance)

## **0.3.3** -- October 23, 2015
  * Compiler: add `getVariables()` and `addFeature()` API methods
  * Compiler: can pass negative indices to `nth()` and `set-nth()`
  * Compiler: can pass map as args to mixin expecting varargs
  * Compiler: add coerceList(map)
  * Compiler: improve `@`at-root support
  * Nested formatter: suppress empty blocks

## **0.3.2** -- October 4, 2015
  * Fix `@`extend behavior when interpolating a variable that contains a selector list
  * Hoist `@`keyframes so children selectors are not prefixed by parent selector
  * Don't wrap `@`import inside `@`media query
  * Partial `@`at-root support; `with:` and `without:` not yet supported
  * Partial `call()` support; `kwargs` not yet supported
  * String-based keys mismatch in map functions
  * Short-circuit evaluation for `and`, `or`, and `if()`
  * Compiler: getParsedFiles() now includes the main file

## **0.3.1** -- September 11, 2015
  * Fix bootstrap v4-dev regression from 0.3.0

## **0.3.0** -- September 6, 2015
  * Compiler getParsedFiles() now returns a map of imported files and their corresponding timestamps
  * Fix multiple variable scope bugs, including `@`each
  * Fix regression from 0.2.1

## **0.2.1** -- September 5, 2015
  * Fix map-get(null)
  * Fix nested function definition (variable scoping)
  * Fix extend bug with BEM syntax
  * Fix selector regression from 0.1.9

## **0.2.0** -- August 25, 2015
  * Smaller git archives
  * Detect `@`import loops
  * Doc blocks everywhere!

## **0.1.10** -- August 23, 2015
  * Fix 3 year old `@`extend bug
  * Fix autoloader. (ext)

## **0.1.9** -- August 1, 2015
  * Adoption of the Sass Community Guidelines
  * Nested selector fixes with lists, interpolated string, and parent selector
  * Implement list-separator() and set-nth() built-ins
  * Implement `@`warn and `@`error
  * Removed spaceship operator pending discussion with reference implementators

## **0.1.8** -- July 18, 2015
  * Online documentation moved to http://leafo.github.com/scssphp/
  * Fix index() - map support; now returns null (instead of false) when value not found
  * Fix lighten(), darken() - percentages don't require % unit
  * Fix str-slice() - edge cases when starts-at or ends-at is 0
  * Fix type-of() - returns arglist for variable arguments
  * Fix !=
  * Fix `@`return inside `@`each
  * Add box support to generate .phar

## **0.1.7** -- July 1, 2015
  * bin/pscss: added --line-numbers and --debug-info options
  * Compiler: added setLineNumberStyle() and 'q' unit
  * Parser: deprecated show() and to() methods
  * simplified licensing (MIT)
  * refactoring internals and misc bug fixes (maps, empty list, function-exists())

## **0.1.6** -- June 22, 2015
  * !global
  * more built-in functions
  * Server: checkedCachedCompile() (zimzat)
  * Server: showErrorsAsCSS() to display errors in a pseudo-element (khamer)
  * misc bug fixes

## **0.1.5** -- June 2, 2015
  * misc bug fixes

## **0.1.4** -- June 2, 2015
  * add new string functions (okj579)
  * add compileFile() and checkCompile() (NoxNebula, saas786, panique)
  * fix regular expression in findImport() (lucvn)
  * needsCompile() shouldn't compare meta-etag with browser etag (edwinveldhuizen)

## **0.1.3** -- May 31, 2015
  * map support (okj579)
  * misc bug fixes (etu, bgarret, aaukt)

## **0.1.1** -- Aug 12, 2014
  * add stub classes -- a backward compatibility layer (vladimmi)

## **0.1.0** -- Aug 9, 2014
  * raise PHP requirement (5.3+)
  * reformat/reorganize source files to be PSR-2 compliant

## **0.0.15** -- Aug 6, 2014
  * fix regression with default values in functions (torkiljohnsen)

## **0.0.14** -- Aug 5, 2014
  * `@`keyframes $name - didn't work inside mixin (sergeylukin)
  * Bourbon transform(translateX()) didn't work (dovy and greynor)

## **0.0.13** -- Aug 4, 2014
  * handle If-None-Match in client request, and send ETag in response (NSmithUK)
  * normalize quotation marks (NoxNebula)
  * improve handling of escape sequence in selectors (matt3224)
  * add "scss_formatter_crunched" which strips comments
  * internal: generate more accurate parse tree

## **0.0.12** -- July 6, 2014
  * revert erroneous import-partials-fix (smuuf)
  * handle If-Modified-Since in client request, and send Last-Modified in response (braver)
  * add hhvm to travis-ci testing

## **0.0.11** -- July 5, 2014
  * support multi-line continuation character (backslash) per CSS2.1 and CSS3 spec (caiosm1005)
  * imported partials should not be compiled (squarestar)
  * add setVariables() and unsetVariable() to interface (leafo/lessphp)
  * micro-optimizing is_null() (Yahasana)

## **0.0.10** -- April 14, 2014
  * fix media query merging (timonbaetz)
  * inline if should treat null as false (wonderslug)
  * optimizing toHSL() (jfsullivan)

## **0.0.9** -- December 23, 2013
  * fix `@`for/`@`while inside `@`content block (sergeylukin)
  * fix functions in mixin_content (timonbaetz)
  * fix infinite loop when target extends itself (oscherler)
  * fix function arguments are lost inside of `@`content block
  * allow setting number precision (kasperisager)
  * add public function helpers (toBool, get, findImport, assertList, assertColor, assertNumber, throwError) (Burgov, atdt)
  * add optional cache buster prefix to serve() method (iMoses)

## **0.0.8** -- September 16, 2013
  * Avoid IE7 content: counter bug
  * Support transparent as color name
  * Recursively create cache dir (turksheadsw)
  * Fix for INPUT NOT FOUND (morgen32)

## **0.0.7** -- May 24, 2013
  * Port various fixes from leafo/lessphp.
  * Improve filter precision.
  * Parsing large image data-urls does not work.
  * Add == and != ops for colors.
  * `@`if and `@`while directives should treat null like false.
  * Add pscss as bin in composer.json (Christian Lück).
  * Fix !default bug (James Shannon, Alberto Aldegheri).
  * Fix mixin content includes (James Shannon, Christian Brandt).
  * Fix passing of varargs to another mixin.
  * Fix interpolation bug in expToString() (Matti Jarvinen).

## **0.0.5** -- March 11, 2013
  * Better compile time errors
  * Fix top level properties inside of a nested `@media` (Anthon Pang)
  * Fix some issues with `@extends` (Anthon Pang)
  * Enhanced handling of `null` (Anthon Pang)
  * Helper functions shouldn't mix with css builtins (Anthon Pang)
  * Enhance selector parsing (Guilherme Blanco, Anthon Pang)
  * Add Placeholder selector support (Martin Hasoň)
  * Add variable argument support (Martin Hasoň)
  * Add zip, index, comparable functions (Martin Hasoň)
  * A bunch of parser and bug fixes

## **0.0.4** -- Nov 3nd, 2012
  * [Import path can be a function](docs/#import-paths) (Christian Lück).
  * Correctly parse media queries with more than one item (Christian Lück).
  * Add `ie_hex_str`, `abs`, `min`, `max` functions (Martin Hasoň)
  * Ignore expressions inside of `calc()` (Martin Hasoň)
  * Improve operator evaluation (Martin Hasoň)
  * Add [`@content`](http://sass-lang.com/docs/yardoc/file.SASS_REFERENCE.html#mixin-content) support.
  * Misc bug fixes.

## **0.0.3** -- August 2nd, 2012
  * Add missing and/or/not operators.
  * Expression evaluation happens correctly.
  * Import file caching and _partial filename support.
  * Misc bug fixes.

## **0.0.2** -- July 30th, 2012
  * SCSS server is aware of imports
  * added custom function interface
  * compressed formatter
  * wrote <a href="{{ site.baseurl }}/docs/">documentation</a>

## **0.0.1** -- July 29th, 2012 -- Initial Release
