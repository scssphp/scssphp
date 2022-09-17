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
 * A logger that silently ignores all messages.
 */
final class QuietLogger implements LocationAwareLoggerInterface
{
    public function warn(string $message, bool $deprecation = false, ?FileSpan $span = null, ?Trace $trace = null): void
    {
    }

    public function debug(string $message, FileSpan $span = null): void
    {
    }
}
