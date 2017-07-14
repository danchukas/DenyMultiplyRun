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

    function setUp()
    {
        self::$noExistFileName = sys_get_temp_dir() . '/' . uniqid('vd_', true);
    }

    function tearDown()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @unlink(self::$noExistFileName);
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


    /**
     * @expectedException \DanchukAS\DenyMultiplyRun\Exception\ProcessExisted
     */
    function testDoubleCall()
    {
        $file_name = self::$randomFileName;
        DenyMultiplyRun::setPidFile($file_name);
        DenyMultiplyRun::setPidFile($file_name);

    }


}
