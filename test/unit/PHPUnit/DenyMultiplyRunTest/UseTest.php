<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-06-22 18:10
 */

namespace DanchukAS\DenyMultiplyRunTest;

use DanchukAS\DenyMultiplyRun\DenyMultiplyRun;
use DanchukAS\Mock\PidFileTestCase;

/**
 * Class UseTest
 * test base usage DenyMultiplyRun
 *
 * @package DanchukAS\DenyMultiplyRunTest
 */
class UseTest extends PidFileTestCase
{

    public function testUsualUse()
    {
        $file_name = self::$noExistFileName;

        $count_try = 2;
        while (--$count_try) {
            DenyMultiplyRun::setPidFile($file_name);
            self::assertStringEqualsFile($file_name, getmypid());

            DenyMultiplyRun::deletePidFile($file_name);
            self::assertFileNotExists($file_name);
        }
    }
}
