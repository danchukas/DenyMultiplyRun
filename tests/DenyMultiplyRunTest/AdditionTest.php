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


class AdditionTest extends TestCase
{

    private static $lastError;

    /**
     * @expectedException Error
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
        $file_name = tempnam(sys_get_temp_dir(), 'vo_');


        self::waitError();
        DenyMultiplyRun::setPidFile($file_name);
        $wait_error = "pid-file exist, but file empty. pid-file updated with pid this process: %i";
        self::catchError($wait_error);

        unlink($file_name);
    }

    private static function waitError()
    {

        set_error_handler(function (int $messageType, string $messageText) {
            self::$lastError = $messageText;
        });

        self::$lastError = null;
    }

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

        $file_name = tempnam(sys_get_temp_dir(), 'vo_');
        file_put_contents($file_name, getmypid());

        DenyMultiplyRun::setPidFile($file_name);
    }

    function testSetPidFile_if_exist_file_with_no_exist_pid()
    {

        $file_name = tempnam(sys_get_temp_dir(), 'vo_');
        $pid = 1;
        while (++$pid < PHP_INT_MAX) {
            if (false === posix_kill($pid, 0)
                && 3 === posix_get_last_error()
            ) {
                break;
            }
        }
        file_put_contents($file_name, $pid);

        self::waitError();
        DenyMultiplyRun::setPidFile($file_name);
        $wait_error = "pid-file exist, but process with contained ID(%i) in it is not exist. pid-file updated with pid this process: %i";
        self::catchError($wait_error);

        unlink($file_name);
    }

    /**
     * @expectedException DanchukAS\DenyMultiplyRun\Exception\PidBiggerMax
     */
    function testSetPidFile_if_exist_file_with_bigger_pid()
    {

        $file_name = tempnam(sys_get_temp_dir(), 'vo_');
        file_put_contents($file_name, PHP_INT_MAX);

        DenyMultiplyRun::setPidFile($file_name);

        unlink($file_name);
    }

    /**
     * @expectedException DanchukAS\DenyMultiplyRun\Exception\ConvertPidFail
     */
    function testSetPidFile_if_exist_file_with_novalidate_pid()
    {

        $file_name = tempnam(sys_get_temp_dir(), 'vo_');
        file_put_contents($file_name, "12as");

        DenyMultiplyRun::setPidFile($file_name);

        unlink($file_name);
    }



    function testDeleteUnExistedPidFile()
    {
        $file_name = sys_get_temp_dir() . '/' . uniqid('vd_', true);
        DenyMultiplyRun::deletePidFile($file_name);
        self::assertFalse(self::hasOutput());
    }


    /**
     * @expectedException DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail
     */
    function testNegDeleteExistedPidFile()
    {
        $file_name = "/etc/hosts";
        DenyMultiplyRun::deletePidFile($file_name);
    }


}
