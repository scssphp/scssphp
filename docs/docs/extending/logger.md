# Customizing the handling of warnings

The compiler supports configuring the reporting of warning and debug messages
of Sass thanks to the `\ScssPhp\ScssPhp\Logger\LoggerInterface`. A custom logger
can be set through the `setLogger` method of the `Compiler` instance.

By default, Sass warnings are reported to STDERR.

The library provides 2 built-in implementations of the interface:
- `\ScssPhp\ScssPhp\Logger\StreamLogger` writes the logs to a PHP stream (a file
  handle for instance)
- `\ScssPhp\ScssPhp\Logger\QuietLogger` ignores all warnings. Its usage is not
  recommended outside some testing scenarios as Sass warnings should not be
  silently ignored.
