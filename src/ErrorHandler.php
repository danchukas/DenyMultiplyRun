<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-24 14:12
 */

namespace DanchukAS\DenyMultiplyRun;

/**
 * Class ErrorHandler
 * @package DanchukAS\DenyMultiplyRun
 */
class ErrorHandler
{
    /**
     * Для перехвата помилок що не кидають ексепшини.
     *
     * @var \LogicException
     */
    public static $lastError;


    public static function startErrorHandle()
    {
        \set_error_handler([__CLASS__, 'errorHandle']);

        self::$lastError = null;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param int $messageType
     * @param string $messageText
     * @param string $messageFile
     * @param int $messageLine
     *
     * @return bool
     */
    public static function errorHandle(int $messageType, string $messageText, string $messageFile, int $messageLine)
    {
        // добавляємо лише інформацію яка є.
        // все інше добавляти має обробник самого проекта.
        $message = "[$messageType] $messageText in $messageFile on line $messageLine";

        self::$lastError = new \LogicException($message);

        // Перехопити перехопили, кидаєм далі обробляти.
        return false;
    }


}