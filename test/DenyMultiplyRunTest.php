<?php
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-06-22 18:10
 */

use PHPUnit\Framework\TestCase;
use DanchukAS\DenyMultiplyRun\DenyMultiplyRun;
use DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail;


class DenyMultiplyRunTest extends TestCase
{

    /**
     * @expectedException Error
     */
    function testConstructor()
    {

        // Because not founded how disable inspection "Call to private from invalid context" for phpstorm.
        //new $class; new DenyMultiplyRun;
        $class = "DenyMultiplyRun";
        new $class;
    }


    function testSetPidFile_if_exist_file()
    {

        $file_name = tempnam(sys_get_temp_dir(), 'vo_');
        DenyMultiplyRun::setPidFile($file_name);
        unlink($file_name);
    }

    function testSetPidFile_if_exist_file_with_pid()
    {

        $file_name = tempnam(sys_get_temp_dir(), 'vo_');
        file_put_contents($file_name, getmypid());
        DenyMultiplyRun::setPidFile($file_name);
        unlink($file_name);
    }


    function testSetPidFile_if_not_exist_file()
    {

        $file_name = sys_get_temp_dir() . '/' . uniqid('vd_', true);
        DenyMultiplyRun::setPidFile($file_name);
        unlink($file_name);
    }


    function testDeletePidFile()
    {
        $file_name = tempnam(sys_get_temp_dir(), 'vo_');
        DenyMultiplyRun::deletePidFile($file_name);
        self::assertFalse(is_file($file_name));

    }


    function testDeleteUnExistedPidFile()
    {
        $file_name = sys_get_temp_dir() . '/' . uniqid('vd_', true);
        DenyMultiplyRun::deletePidFile($file_name);
        self::assertFalse(self::hasOutput());
    }


    /**
     * @expectedException DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail
     */
    function testNegDeleteExistedPidFile()
    {
        $file_name = "/etc/hosts";
        DenyMultiplyRun::deletePidFile($file_name);
    }


}
