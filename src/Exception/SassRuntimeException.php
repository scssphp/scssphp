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

namespace ScssPhp\ScssPhp\Exception;

use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\StackTrace\Trace;
use ScssPhp\ScssPhp\Util;

/**
 * @internal
 */
final class SassRuntimeException extends \Exception implements SassException
{
    /**
     * @var string
     * @readonly
     */
    private $originalMessage;

    /**
     * @var FileSpan
     * @readonly
     */
    private $span;

    private readonly Trace $sassTrace;

    public function __construct(string $message, FileSpan $span, ?Trace $sassTrace = null, \Throwable $previous = null)
    {
        $this->originalMessage = $message;
        $this->span = $span;
        $this->sassTrace = $sassTrace ?? new Trace([Util::frameForSpan($span, 'root stylesheet')]);

        $formattedMessage = $span->message($message); // TODO add the highlighting

        foreach (explode("\n", $this->sassTrace->getFormattedTrace()) as $frame) {
            if ($frame === '') {
                continue;
            }
            $formattedMessage .= "\n";
            $formattedMessage .= '  ' . $frame;
        }

        parent::__construct($formattedMessage, 0, $previous);
    }

    /**
     * Gets the original message without the location info in it.
     */
    public function getOriginalMessage(): string
    {
        return $this->originalMessage;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function getSassTrace(): Trace
    {
        return $this->sassTrace;
    }
}
