<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-15 01:03
 */

namespace DanchukAS\DenyMultiplyRunTest;

use DanchukAS\DenyMultiplyRun\DenyMultiplyRun;
use DanchukAS\Mock\PidFileTestCase;

/** @noinspection PhpClassNamingConventionInspection */

/**
 * Class ExistRightPidFileTest
 * Тести з вірним підфайлом.
 * @package DanchukAS\DenyMultiplyRunTest
 */
class ExistRightPidFileTest extends PidFileTestCase
{

    private static $lastError;

    public function testEmptyFile()
    {
        //@todo when add Debug mode fix check
//        self::waitError();
        DenyMultiplyRun::setPidFile(self::$existFileName);
//        $wait_error = '[' . E_USER_NOTICE . '] pid-file exist, but file empty.'
//            . ' pid-file updated with pid this process: %i';
//        self::catchError($wait_error);
        self::assertStringEqualsFile(self::$existFileName, getmypid());
    }

//    private static function waitError()
//    {
//
//        /** @noinspection PhpUnusedParameterInspection */
//        set_error_handler(function (int $messageType, string $messageText) {
//            self::$lastError = "[$messageType] " . $messageText;
//        });
//
//        self::$lastError = null;
//    }
//
//    /**
//     * перевіряє чи помилка відбулась, і саме та яка очікувалась.
//     * @param string $message
//     */
//    private static function catchError(string $message)
//    {
//        restore_error_handler();
//        self::assertStringMatchesFormat("$message", self::$lastError);
//    }

    public function testNoExistedPid()
    {

        $no_exist_pid = $this->getNoExistPid();
        file_put_contents(self::$existFileName, $no_exist_pid);

        //@todo when add Debug mode fix check
//        self::waitError();
        DenyMultiplyRun::setPidFile(self::$existFileName);
//        $wait_error = '[' . E_USER_NOTICE . '] pid-file exist'
//            . ', but process with contained ID(%i) in it is not exist.'
//            . ' pid-file updated with pid this process: %i';
//        self::catchError($wait_error);
        self::assertStringEqualsFile(self::$existFileName, getmypid());
    }

    /**
     * @dataProvider setPidFileParam
     * @param \Generator $filename
     * @param string $exception
     */
    public function testSetPidFile(\Generator $filename, string $exception)
    {
        $this->expectException($exception);
        DenyMultiplyRun::setPidFile($filename->current());
    }


}
