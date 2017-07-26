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
 * Class UnknownTypeMock
 * @package DanchukAS\Mock\Type
 */
class UnknownTypeMock extends TypeMock
{

    /**
     * @return \Generator
     */
    public static function getSample()
    {
        $file_name = self::newTempFile();
        $resource = fopen($file_name, 'rb');
        fclose($resource);
        $unknown = $resource;
        yield [$unknown];

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @unlink($file_name);

        if (function_exists('imagecreate')) {
            $resource = \imagecreate(1, 1);
            \imagedestroy($resource);
            $unknown = $resource;
            yield [$unknown];
        }
    }

    /**
     * @return bool|string
     */
    private static function newTempFile()
    {
        return tempnam(sys_get_temp_dir(), 'vo_');
    }
}