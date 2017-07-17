<?php
declare(strict_types = 1);


/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-06-22 18:10
 */

namespace DanchukAS\DenyMultiplyRunTest;

use DanchukAS\DenyMultiplyRun\DenyMultiplyRun;
use DanchukAS\DenyMultiplyRun\PidFileTestCase;

/**
 * Class SurprisingTest
 * поглиблені тести на "всі найбільш можливі ситуації"
 * @package DanchukAS\DenyMultiplyRunTest
 */
class SurprisingTest extends PidFileTestCase
{

    /**
     * @expectedException \DanchukAS\DenyMultiplyRun\Exception\ProcessExisted
     */
    public function testDoubleCall()
    {
        $file_name = self::$noExistFileName;
        DenyMultiplyRun::setPidFile($file_name);
        DenyMultiplyRun::setPidFile($file_name);
    }/** @noinspection PhpMethodNamingConventionInspection */


    /**
     * Delete if no exist pid file.
     */
    public function testDeleteNoExistedPidFile()
    {
        $this->expectException("DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail");
        DenyMultiplyRun::deletePidFile(self::$noExistFileName);
    }


    /** @noinspection PhpMethodNamingConventionInspection */
    public function testDeletePidFileWrongParam()
    {
        $this->expectException("DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail");
        DenyMultiplyRun::deletePidFile(null);
    }


    /** @noinspection PhpMethodNamingConventionInspection */
    public function testDeleteNoAccessFile()
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

        $this->expectException("DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail");
        DenyMultiplyRun::deletePidFile($file_name);
    }/** @noinspection PhpMethodNamingConventionInspection */

    /**
     * @dataProvider notString
     * @param mixed $notString
     */
    public function testWrongTypeParam($notString)
    {
        $this->expectException("TypeError");
        DenyMultiplyRun::setPidFile($notString);
    }

    /**
     * @dataProvider wrongParam
     * @param string $no_valid_file_name
     */
    public function testWrongParam(string $no_valid_file_name)
    {
        $this->expectException("Exception");
        DenyMultiplyRun::setPidFile($no_valid_file_name);
    }


    /**
     * @return array
     */
    public function notString()
    {
        $right_resource = fopen(__FILE__, "r");
        fclose($right_resource);
        $fail_resource = $right_resource;

        return [
            [null]
            , [false]
            , [0]
            , [[]]
            , [function () {
            }]
            , [new \Exception]
            , [$fail_resource]
        ];
    }


    /**
     * @return array
     */
    public function wrongParam()
    {
        return [[""], ["."], ["/"], ['//']];
    }/** @noinspection PhpMethodNamingConventionInspection */


    /**
     * @dataProvider notString
     * @param mixed $badResource from dataProvider
     */
    public function testLockedFileBeforeClose($badResource)
    {
        $method = new \ReflectionMethod("DanchukAS\DenyMultiplyRun\DenyMultiplyRun", "closePidFile");

        $method->setAccessible(true);
        $this->expectException("DanchukAS\DenyMultiplyRun\Exception\CloseFileFail");
        $method->invoke(null, $badResource);
        $method->setAccessible(false);
    }
}
