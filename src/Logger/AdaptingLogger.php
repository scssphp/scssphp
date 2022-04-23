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
use ScssPhp\ScssPhp\Util;
use ScssPhp\ScssPhp\Util\Path;

/**
 * @internal
 */
final class AdaptingLogger implements LocationAwareLoggerInterface
{
    /**
     * @var LoggerInterface
     * @readonly
     */
    private $logger;

    private function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function adaptLogger(LoggerInterface $logger): LocationAwareLoggerInterface
    {
        if ($logger instanceof LocationAwareLoggerInterface) {
            return $logger;
        }

        return new self($logger);
    }

    public function warn(string $message, bool $deprecation = false, ?FileSpan $span = null, ?Trace $trace = null)
    {
        if ($span === null) {
            $formattedMessage = $message;
        } elseif ($trace !== null) {
            // If there's a span and a trace, the span's location information is
            // probably duplicated in the trace, so we just use it for highlighting.
            $formattedMessage = $message;
            // TODO implement the highlight of a span
        } else {
            $formattedMessage = ' on ' . $span->message("\n" . $message);
        }

        if ($trace !== null) {
            $formattedMessage .= "\n" . Util::indent(rtrim($trace->getFormattedTrace()), 4);
        }

        $this->logger->warn($formattedMessage, $deprecation);
    }

    public function debug(string $message, FileSpan $span = null)
    {
        $location = '';
        if ($span !== null) {
            $url = $span->getStart()->getSourceUrl() === null ? '-' : Path::prettyUri($span->getStart()->getSourceUrl());
            $line = $span->getStart()->getLine() + 1;
            $location = "$url:$line ";
        }

        $this->logger->debug(sprintf("%sDEBUG: %s", $location, $message));
    }
}
