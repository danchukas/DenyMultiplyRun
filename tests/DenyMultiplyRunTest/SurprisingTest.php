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
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * Delete if no exist pid file.
     * @dataProvider deletePidFileParam
     * @param $param
     * @param string|null $message
     */
    public function testDeletePidFile($param, string $message = null)
    {
        if (!is_null($message)) {
            self::markTestSkipped($message);
        }

        $this->expectException("DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail");
        DenyMultiplyRun::deletePidFile($param);
    }

    /**
     * @dataProvider notFileNameProvider
     * @param mixed $no_valid_file_name
     * @param string $throwable_type
     */
    public function testWrongParam($no_valid_file_name, string $throwable_type)
    {
        $this->expectException($throwable_type);
        DenyMultiplyRun::setPidFile($no_valid_file_name);
    }


    /** @noinspection PhpMethodNamingConventionInspection */
    /**
     * @dataProvider notStringProvider
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
