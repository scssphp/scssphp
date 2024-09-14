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
interface SassRuntimeException extends SassException
{
    public function withAdditionalSpan(FileSpan $span, string $label, ?\Throwable $previous = null): MultiSpanSassRuntimeException;
}
