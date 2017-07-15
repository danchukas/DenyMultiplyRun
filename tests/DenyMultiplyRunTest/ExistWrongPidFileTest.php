<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-15 00:56
 */

namespace DanchukAS\DenyMultiplyRunTest;

use DanchukAS\DenyMultiplyRun\DenyMultiplyRun;
use PHPUnit\Framework\TestCase;

class ExistWrongPidFileTest extends TestCase
{

    private static $existFileName;

    function setUp()
    {
        self::$existFileName = tempnam(sys_get_temp_dir(), 'vo_');
    }

    function tearDown()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @unlink(self::$existFileName);
    }


    function testNoValidPid()
    {
        file_put_contents(self::$existFileName, "12as");

        self::expectException("DanchukAS\DenyMultiplyRun\Exception\ConvertPidFail");
        DenyMultiplyRun::setPidFile(self::$existFileName);
    }


    function testBiggerPid()
    {
        file_put_contents(self::$existFileName, PHP_INT_MAX);

        self::expectException("DanchukAS\DenyMultiplyRun\Exception\PidBiggerMax");
        DenyMultiplyRun::setPidFile(self::$existFileName);
    }

    function testNoAccessFile()
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

        self::expectException("DanchukAS\DenyMultiplyRun\Exception\OpenFileFail");
        DenyMultiplyRun::setPidFile($file_name);
    }


}
