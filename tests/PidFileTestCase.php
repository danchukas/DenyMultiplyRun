<?php
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-17 21:51
 */

namespace DanchukAS\DenyMultiplyRun;

use PHPUnit\Framework\TestCase;

/**
 * Class PidFileTestCase
 * included shared test settings.
 * @package DanchukAS\DenyMultiplyRun
 */
abstract class PidFileTestCase extends TestCase
{
    protected static $noExistFileName;

    protected static $existFileName;


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
}