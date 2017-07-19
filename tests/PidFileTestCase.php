<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-17 21:51
 */

namespace DanchukAS\DenyMultiplyRun;

use DanchukAS\Mock\TypeList\NotStringList;
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

    private static $tempFileList = [];

    public function setUp()
    {
        self::$noExistFileName = self::getFileName();
        self::$existFileName = self::newTempFileName();
    }

    /**
     * @return string
     */
    private static function getFileName(): string
    {
        $file_name = sys_get_temp_dir() . '/' . uniqid('vd_', true);
        self::$tempFileList[] = $file_name;
        return $file_name;
    }

    /**
     * @return bool|string
     */
    private static function newTempFileName()
    {
        $file_name = tempnam(sys_get_temp_dir(), 'vo_');
        self::$tempFileList[] = $file_name;
        return $file_name;
    }

    public function tearDown()
    {
        foreach (self::$tempFileList as $file_name) {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @unlink($file_name);
        }
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
            $not_string_list = NotStringList::getList();

            foreach ($not_string_list as &$param) {
                if (!is_array($param)) {
                    var_dump($param);
                }
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

    /**
     * return array
     */
    public function deletePidFileParam()
    {
        static $param = null;

        if (is_null($param)) {
            // existed file without write access for current user.
            // for Ubuntu is /etc/hosts.
            $file_name = "/etc/hosts";

            $message = $this->adminOrNotUnix($file_name);

            $param = [
                "noExistFileName" => [self::getFileName()]
                , "wrongParam" => [null]
                , "accessDenied" => [$file_name, $message]
            ];
        }

        return $param;
    }

    /**
     * @param $file_name
     * @return null|string
     */
    private function adminOrNotUnix($file_name)
    {
        $message = null;

        if (!file_exists($file_name)) {
            $message = "test only for *nix.";
        } elseif (is_writable($file_name)) {
            $message = "test runned under super/admin user. Change user.";
        }
        return $message;
    }

    /**
     * return array
     */
    public function setPidFileParam()
    {
        static $param = null;

        if (is_null($param)) {
            $param = [
                "lockedPidFile" => [
                    self::lockedPidFile()
                    , "DanchukAS\DenyMultiplyRun\Exception\LockFileFail"
                ]
                , "fileHasExistPid" => [
                    self::fileWithExistedPid()
                    , "DanchukAS\DenyMultiplyRun\Exception\ProcessExisted"
                ]
                , "" => [
                    self::noValidPidFile()
                    , "DanchukAS\DenyMultiplyRun\Exception\ConvertPidFail"
                ]
            ];
        }

        return $param;
    }

    /**
     *
     */
    private static function lockedPidFile()
    {
        while (true) {
            $file_name = self::newTempFileName();
            $file_resource = fopen($file_name, "r+");
            flock($file_resource, LOCK_EX);

            yield $file_name;
        }
    }

    /**
     *
     */
    private static function fileWithExistedPid()
    {
        while (true) {
            $file_name = self::newTempFileName();
            file_put_contents($file_name, getmypid());

            yield $file_name;
        }
    }

    /**
     *
     */
    private static function noValidPidFile()
    {
        while (true) {
            $file_name = self::newTempFileName();
            file_put_contents($file_name, "12as");

            yield $file_name;
        }
    }
}
