<?php

namespace ScssPhp\ScssPhp\Exception;

/**
 * Internal exception thrown in places not having access to reporting the location.
 *
 * This class does not implement SassException on purpose, as it should never be returned to the outside code.
 *
 * @internal
 */
class SassScriptException extends \Exception
{
}
