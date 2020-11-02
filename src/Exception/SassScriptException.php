<?php

namespace ScssPhp\ScssPhp\Exception;

/**
 * An exception thrown by SassScript.
 *
 * This class does not implement SassException on purpose, as it should
 * never be returned to the outside code. The compilation will catch it
 * and replace it with a SassException reporting the location of the
 * error.
 */
class SassScriptException extends \Exception
{
}
