<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Logger;

use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\StackTrace\Trace;

/**
 * Interface implemented by loggers for warnings and debug messages.
 *
 * The official Sass implementation recommends that loggers report the
 * messages immediately rather than waiting for the end of the
 * compilation, to provide a better debugging experience when the
 * compilation does not end (error or infinite loop after the warning
 * for instance).
 */
interface LocationAwareLoggerInterface extends LoggerInterface
{
    /**
     * Emits a warning with the given message.
     *
     * If $span is passed, it's the location in the Sass source that generated
     * the warning. If $trace is passed, it's the Sass stack trace when the
     * warning was issued.
     * If $deprecation is true, it indicates that this is a deprecation
     * warning. Implementations should surface all this information to
     * the end user.
     *
     * @return void
     */
    public function warn(string $message, bool $deprecation = false, ?FileSpan $span = null, ?Trace $trace = null);

    /**
     * Emits a debugging message associated with the given span.
     *
     * @return void
     */
    public function debug(string $message, FileSpan $span = null);
}
