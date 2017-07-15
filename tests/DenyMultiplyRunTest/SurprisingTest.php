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
 * Class SurprisingTest
 * поглиблені тести на "всі найбільш можливі ситуації"
 * @package DanchukAS\DenyMultiplyRunTest
 */
class SurprisingTest extends TestCase
{

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
     * @expectedException \DanchukAS\DenyMultiplyRun\Exception\ProcessExisted
     */
    function testDoubleCall()
    {
        $file_name = self::$noExistFileName;
        DenyMultiplyRun::setPidFile($file_name);
        DenyMultiplyRun::setPidFile($file_name);

    }


    function testDeleteNoExistedPidFile()
    {
        self::expectException("DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail");
        DenyMultiplyRun::deletePidFile(self::$noExistFileName);
    }


    function testDeletePidFileWrongParam()
    {
        self::expectException("DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail");
        DenyMultiplyRun::deletePidFile(null);
    }


    function testDeleteNoAccessFile()
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

    /**
     * @dataProvider notString
     * @param mixed $notString
     */
    function testWrongTypeParam($notString)
    {
        self::expectException("TypeError");
        DenyMultiplyRun::setPidFile($notString);

    }

    /**
     * @dataProvider WrongParam
     * @param string $no_valid_file_name
     */
    function testWrongParam(string $no_valid_file_name)
    {
        self::expectException("Exception");
        DenyMultiplyRun::setPidFile($no_valid_file_name);
    }


    /**
     * @return array
     */
    function notString()
    {
        $r = fopen(__FILE__, "r");
        fclose($r);

        return [
            [null]
            , [false]
            , [0]
            , [[]]
            , [function () {
            }]
            , [new \Exception]
            , [$r]];
    }


    /**
     * @return array
     */
    function WrongParam()
    {
        return [[""], ["."], ["/"], ['//']];
    }


    /**
     * @dataProvider notString
     */
    function testLockedFileBeforeClose($badResource)
    {
        $method = new \ReflectionMethod("DanchukAS\DenyMultiplyRun\DenyMultiplyRun", "closePidFile");

        $method->setAccessible(true);
        self::expectException("DanchukAS\DenyMultiplyRun\Exception\CloseFileFail");
        $method->invoke(null, $badResource);
        $method->setAccessible(false);

    }



}
