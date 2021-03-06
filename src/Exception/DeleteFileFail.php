<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-06-16 18:00
 */

namespace DanchukAS\DenyMultiplyRun\Exception;

/**
 * Class DeleteFileFail
 *
 * @package DanchukAS\DenyMultiplyRun
 */
class DeleteFileFail extends \Exception
{
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @link  http://php.net/manual/en/exception.construct.php
     *
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param \Throwable $previous [optional] The previous throwable used for the exception chaining.
     *
     * @since 5.1.0
     */
    public function __construct($message, $code = 8, \Throwable $previous = null)
    {
        $message = "Видалити pid-файл не вдалось: $message";
        parent::__construct($message, $code, $previous);
    }
}
