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
use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Exception\SimpleSassException;
use ScssPhp\ScssPhp\Exception\SimpleSassRuntimeException;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\SourceSpan\SourceSpan;
use ScssPhp\ScssPhp\StackTrace\Trace;

/**
 * A logger that wraps an inner logger to have special handling for
 * deprecation warnings.
 *
 * @internal
 */
final class DeprecationHandlingLogger implements DeprecationAwareLoggerInterface
{
    private const MAX_REPETITIONS = 5;

    /**
     * @var array<value-of<Deprecation>, int>
     */
    private array $warningCounts = [];

    /**
     * @param Deprecation[] $fatalDeprecations
     * @param Deprecation[] $futureDeprecations
     */
    public function __construct(
        private readonly LoggerInterface $inner,
        private readonly array $fatalDeprecations,
        private readonly array $futureDeprecations,
        private readonly bool $limitRepetition = true
    ) {
    }

    public function warn(string $message, ?Deprecation $deprecation = null, ?FileSpan $span = null, ?Trace $trace = null): void
    {
        $this->inner->warn($message, $deprecation, $span, $trace);
    }

    /**
     * Processes a deprecation warning.
     *
     * If $deprecation is in {@see $fatalDeprecations}, this shows an error.
     *
     * If it's a future deprecation that hasn't been opted into or it's a
     * deprecation that's already been warned for {@see self::MAX_REPETITIONS} times and
     * {@see limitRepetitions} is true, the warning is dropped.
     *
     * Otherwise, this is passed on to {@see warn}.
     */
    public function warnForDeprecation(Deprecation $deprecation, string $message, ?FileSpan $span = null, ?Trace $trace = null): void
    {
        if (\in_array($deprecation, $this->fatalDeprecations, true)) {
            $message .= "\n\nThis is only an error because you've set the {$deprecation->value} deprecation to be fatal.\nRemove this setting if you need to keep using this feature.";

            if ($span !== null && $trace !== null) {
                throw new SimpleSassRuntimeException($message, $span, $trace);
            }

            if ($span !== null) {
                throw new SimpleSassException($message, $span);
            }

            throw new SassScriptException($message);
        }

        if ($deprecation->isFuture() && !\in_array($deprecation, $this->futureDeprecations, true)) {
            return;
        }

        if ($this->limitRepetition) {
            $count = $this->warningCounts[$deprecation->value] = ($this->warningCounts[$deprecation->value] ?? 0) + 1;

            if ($count > self::MAX_REPETITIONS) {
                return;
            }
        }

        $this->warn($message, $deprecation, $span, $trace);
    }

    public function debug(string $message, SourceSpan $span): void
    {
        $this->inner->debug($message, $span);
    }

    /**
     * Prints a warning indicating the number of deprecation warnings that were
     * omitted due to repetition.
     */
    public function summarize(): void
    {
        $total = 0;

        foreach ($this->warningCounts as $count) {
            if ($count > self::MAX_REPETITIONS) {
                $total += $count - self::MAX_REPETITIONS;
            }
        }

        if ($total > 0) {
            $this->inner->warn("$total repetitive deprecation warnings omitted.\nRun in verbose mode to see all warnings.");
        }
    }
}
