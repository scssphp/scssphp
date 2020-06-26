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

use Exception;

/**
 * Parser Exception
 *
 * @author Oleksandr Savchenko <traveltino@gmail.com>
 */
class ParserException extends \Exception
{
    /**
     * Style exception line.
     *
     * @var integer
     */
    protected $styleLine = 0;

    /**
     * Create new FieldException instance.
     *
     * @param string $message
     * @param string $file
     * @param int $line
     * @param integer $code
     * @param Exception $previous
     */
    public function __construct($message = null, int $styleLine = 0, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->styleLine = $styleLine;
    }

    /**
     * Get style line.
     *
     * @return integer
     */
    public function getStyleLine()
    {
        return $this->styleLine;
    }
}
