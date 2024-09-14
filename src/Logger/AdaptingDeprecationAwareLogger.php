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

use ScssPhp\ScssPhp\Deprecation;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\SourceSpan\SourceSpan;
use ScssPhp\ScssPhp\StackTrace\Trace;

/**
 * @internal
 */
final class AdaptingDeprecationAwareLogger implements DeprecationAwareLoggerInterface
{
    private function __construct(private readonly LoggerInterface $logger)
    {
    }

    public static function adaptLogger(LoggerInterface $logger): DeprecationAwareLoggerInterface
    {
        if ($logger instanceof DeprecationAwareLoggerInterface) {
            return $logger;
        }

        return new self($logger);
    }

    public function warnForDeprecation(Deprecation $deprecation, string $message, ?FileSpan $span = null, ?Trace $trace = null): void
    {
        if ($deprecation->isFuture()) {
            return;
        }

        $this->logger->warn($message, $deprecation, $span, $trace);
    }

    public function warn(string $message, ?Deprecation $deprecation = null, ?FileSpan $span = null, ?Trace $trace = null): void
    {
        $this->logger->warn($message, $deprecation, $span, $trace);
    }

    public function debug(string $message, SourceSpan $span): void
    {
        $this->logger->debug($message, $span);
    }
}
