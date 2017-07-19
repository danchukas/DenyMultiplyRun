<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-18 08:46
 */

namespace DanchukAS\Mock\Type;


use DanchukAS\Mock\TypeMock;

/**
 * Class ResourceTypeMock
 * @package DanchukAS\Mock\Type
 */
class ResourceTypeMock extends TypeMock
{
    /**
     * @return \Generator
     */
    public static function getSample()
    {

        $resource = sem_get(1);
        yield ['native memory' => $resource];


// @todo: add native res
//        $r = 	opendir();
//        $r = shmop_open();

//        shm_attach();
//        xml_parser_create();
//        gzopen();


        if (function_exists('imagecreate')) {
            $resource = \imagecreate(1, 1);
            yield ['gd imagecreate' => $resource];
            \imagedestroy($resource);
        }
    }
}