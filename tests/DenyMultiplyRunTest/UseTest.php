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
 * Class UseTest
 * test base usage DenyMultiplyRun
 *
 * @package DanchukAS\DenyMultiplyRunTest
 */
class UseTest extends TestCase
{

    private static $randomFileName;

    function setUp()
    {
        self::$randomFileName = sys_get_temp_dir() . '/' . uniqid('vd_', true);
    }

    function tearDown()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @unlink(self::$randomFileName);
    }

    /**
     * DenyMultiplyRun лише для статичного визова методів
     * щоб зменшити використання пам'яті і полегшити дебаг і розуміння.
     * @expectedException \Error
     */
    function testConstructor()
    {

        // Because not founded how disable inspection "Call to private from invalid context" for phpstorm.
        //new $class; new DenyMultiplyRun;
        $class = "DenyMultiplyRun";
        new $class;
    }


    function testUsualUse()
    {
        $file_name = self::$randomFileName;

        $count_try = 2;
        while (--$count_try) {
            DenyMultiplyRun::setPidFile($file_name);
            self::assertStringEqualsFile($file_name, getmypid());

            DenyMultiplyRun::deletePidFile($file_name);
            self::assertFileNotExists($file_name);
        }
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
