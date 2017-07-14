<?php
declare(strict_types=1);


/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-06-22 18:10
 */

namespace DanchukAS\DenyMultiplyRunTest;

use DanchukAS\DenyMultiplyRun\DenyMultiplyRun;
use PHPUnit\Framework\TestCase;


/**
 * Class AdditionTest
 * поглиблені тести на "всі найбільш можливі ситуації"
 * @package DanchukAS\DenyMultiplyRunTest
 */
class AdditionTest extends TestCase
{

    private static $lastError;

    private static $noExistFileName;

    private static $existFileName;

    function setUp()
    {
        self::$noExistFileName = sys_get_temp_dir() . '/' . uniqid('vd_', true);
        self::$existFileName = tempnam(sys_get_temp_dir(), 'vo_');
    }

    function tearDown()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @unlink(self::$noExistFileName);
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @unlink(self::$existFileName);
    }

    /**
     * @expectedException \Error
     */
    function testConstructor()
    {

        // Because not founded how disable inspection "Call to private from invalid context" for phpstorm.
        //new $class; new DenyMultiplyRun;
        $class = "DenyMultiplyRun";
        new $class;
    }

    function testSetPidFile_if_exist_file()
    {
        self::waitError();
        DenyMultiplyRun::setPidFile(self::$existFileName);
        $wait_error = "pid-file exist, but file empty. pid-file updated with pid this process: %i";
        self::catchError($wait_error);
    }

    private static function waitError()
    {

        /** @noinspection PhpUnusedParameterInspection */
        set_error_handler(function (int $messageType, string $messageText) {
            self::$lastError = $messageText;
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
    function testSetPidFile_if_exist_file_with_exist_pid()
    {

        $file_name = self::$existFileName;
        file_put_contents($file_name, getmypid());

        DenyMultiplyRun::setPidFile($file_name);
    }

    function testSetPidFile_if_exist_file_with_no_exist_pid()
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
        $wait_error = "pid-file exist, but process with contained ID(%i) in it is not exist. "
            . "pid-file updated with pid this process: %i";
        self::catchError($wait_error);
    }


    function testSetPidFile_if_exist_file_with_bigger_pid()
    {
        file_put_contents(self::$existFileName, PHP_INT_MAX);

        self::expectException("DanchukAS\DenyMultiplyRun\Exception\PidBiggerMax");
        DenyMultiplyRun::setPidFile(self::$existFileName);
    }

    function testSetPidFile_if_exist_file_with_novalidate_pid()
    {
        file_put_contents(self::$existFileName, "12as");

        self::expectException("DanchukAS\DenyMultiplyRun\Exception\ConvertPidFail");
        DenyMultiplyRun::setPidFile(self::$existFileName);
    }


    function testDeleteNoExistedPidFile()
    {
        DenyMultiplyRun::deletePidFile(self::$noExistFileName);
        self::assertFalse(self::hasOutput());
    }


    function testNegDeleteExistedPidFile()
    {
        // existed file without write access for current user.
        // for Ubuntu is /etc/hosts.
        $file_name = "/etc/hosts";

        if (!file_exists($file_name)) {
            self::markTestSkipped("test only for *nix.");
        }

        if (is_writable($file_name)) {
            self::markTestSkipped("test runned under super/admin user. Change user.");
        }

        self::expectException("DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail");
        DenyMultiplyRun::deletePidFile($file_name);
    }


    function testPidFileLocked()
    {
        $file_resource = fopen(self::$existFileName, "r+");
        flock($file_resource,LOCK_EX);

        self::expectException("DanchukAS\DenyMultiplyRun\Exception\LockFileFail");
        DenyMultiplyRun::setPidFile(self::$existFileName);
    }

}
