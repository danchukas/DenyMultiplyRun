<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-21 19:11
 */

//use \DanchukAS\DenyMultiplyRun\DenyMultiplyRun;

//require_once __DIR__ . '/../vendor/autoload.php';
//spl_autoload_register(function ($class){
//    $path = str_replace("DanchukAS\DenyMultiplyRun\\", __DIR__ . "/../src/", $class, $count);
//    if ($count > 0) {
//        $path = realpath(str_replace("\\", "/", $path) . '.php');
//        require_once $path;
//    }
//}, true, true);

//require __DIR__ . '/../src/DenyMultiplyRun.php';

//$FN = "/tmp/run/some_job_or_daemon.pid";

//    DenyMultiplyRun::setPidFile($pid_file);
//    DenyMultiplyRun::deletePidFile($pid_file);
//if (!is_file($pid_file)) {

//$r = fopen("/tmp/run/some_job_or_daemon.pid", 'x');
//fwrite($r, (string)getmypid());
//fclose($r);
////}
//
//unlink("/tmp/run/some_job_or_daemon.pid");


var_dump(stream_get_meta_data(tmpfile()));