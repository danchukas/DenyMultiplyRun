<?php
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-15 01:03
 */

namespace DanchukAS\DenyMultiplyRunTest;

use DanchukAS\DenyMultiplyRun\DenyMultiplyRun;
use PHPUnit\Framework\TestCase;

class ExistRightPidFileTest extends TestCase
{

    private static $lastError;

    private static $existFileName;

    public function setUp()
    {
        self::$existFileName = tempnam(sys_get_temp_dir(), 'vo_');
    }

    public function tearDown()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @unlink(self::$existFileName);
    }

    public function testEmptyFile()
    {
        self::waitError();
        DenyMultiplyRun::setPidFile(self::$existFileName);
        $wait_error = "[" . E_USER_NOTICE . "] pid-file exist, but file empty."
            . " pid-file updated with pid this process: %i";
        self::catchError($wait_error);
    }

    private static function waitError()
    {

        /** @noinspection PhpUnusedParameterInspection */
        set_error_handler(function (int $messageType, string $messageText) {
            self::$lastError = "[$messageType] " . $messageText;
        });

        self::$lastError = null;
    }

    /**
     * перевіряє чи помилка відбулась, і саме та яка очікувалась.
     * @param string $message
     */
    private static function catchError(string $message)
    {
        restore_error_handler();
        self::assertStringMatchesFormat("$message", self::$lastError);
    }

    /**
     * @expectedException \DanchukAS\DenyMultiplyRun\Exception\ProcessExisted
     */
    public function testExistedPid()
    {

        $file_name = self::$existFileName;
        file_put_contents($file_name, getmypid());

        DenyMultiplyRun::setPidFile($file_name);
    }

    public function testNoExistedPid()
    {

        $no_exist_pid = 1;
        while (++$no_exist_pid < PHP_INT_MAX) {
            if (false === posix_kill($no_exist_pid, 0)
                && 3 === posix_get_last_error()
            ) {
                break;
            }
        }
        file_put_contents(self::$existFileName, $no_exist_pid);

        self::waitError();
        DenyMultiplyRun::setPidFile(self::$existFileName);
        $wait_error = "[" . E_USER_NOTICE . "] pid-file exist"
            . ", but process with contained ID(%i) in it is not exist."
            . " pid-file updated with pid this process: %i";
        self::catchError($wait_error);
    }

    public function testLockedFile()
    {
        $file_resource = fopen(self::$existFileName, "r+");
        flock($file_resource, LOCK_EX);

        $this->expectException("DanchukAS\DenyMultiplyRun\Exception\LockFileFail");
        DenyMultiplyRun::setPidFile(self::$existFileName);
    }

}
