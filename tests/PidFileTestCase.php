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
 * @package DanchukAS\DenyMultiplyRunTest
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

    /**
     * @return array
     */
    public function notFileNameProvider()
    {
        static $not_file_name = null;

        if (is_null($not_file_name)) {
            $not_file_name = $this->notStringProvider() + $this->noValidFileNameProvider();
        }

        return $not_file_name;
    }

    /**
     * @return array
     */
    public function notStringProvider()
    {
        static $not_string_list = null;
        if (is_null($not_string_list)) {
            $right_resource = fopen(__FILE__, "r");
            fclose($right_resource);
            $fail_resource = $right_resource;

            $not_string_list = [
                "null" => [null]
                , "boolean" => [false]
                , "int" => [0]
                , "array" => [[]]
                , "function" => [function () {
                }]
                , "object" => [new \Exception]
                , "resource" => [$fail_resource]
            ];

            foreach ($not_string_list as &$param) {
                $param["throw"] = "TypeError";
            }
        }

        return $not_string_list;
    }

    /**
     * @return array
     */
    public function noValidFileNameProvider()
    {
        static $file_name_list = null;

        if (is_null($file_name_list)) {
            $file_name_list = [
                [""]
                , ["."]
                , ["/"]
                , ['//']
            ];

            foreach ($file_name_list as &$param) {
                $param["throw"] = "Exception";
            }
        }

        return $file_name_list;
    }
}
