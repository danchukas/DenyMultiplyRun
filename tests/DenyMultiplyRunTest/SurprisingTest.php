<?php
declare(strict_types = 1);


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


    public function setUp()
    {
        self::$noExistFileName = sys_get_temp_dir() . '/' . uniqid('vd_', true);
        self::$existFileName = tempnam(sys_get_temp_dir(), 'vo_');
    }

    public function tearDown()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @unlink(self::$noExistFileName);
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @unlink(self::$existFileName);
    }


    /**
     * @expectedException \DanchukAS\DenyMultiplyRun\Exception\ProcessExisted
     */
    public function testDoubleCall()
    {
        $file_name = self::$noExistFileName;
        DenyMultiplyRun::setPidFile($file_name);
        DenyMultiplyRun::setPidFile($file_name);

    }


    public function testDeleteNoExistedPidFile()
    {
        $this->expectException("DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail");
        DenyMultiplyRun::deletePidFile(self::$noExistFileName);
    }


    public function testDeletePidFileWrongParam()
    {
        $this->expectException("DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail");
        DenyMultiplyRun::deletePidFile(null);
    }


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
    }

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
     * @dataProvider WrongParam
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
        $r = fopen(__FILE__, "r");
        fclose($r);

        return [
            [null]
            , [false]
            , [0]
            , [[]]
            , [function() {
            }]
            , [new \Exception]
            , [$r]];
    }


    /**
     * @return array
     */
    public function WrongParam()
    {
        return [[""], ["."], ["/"], ['//']];
    }


    /**
     * @dataProvider notString
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
