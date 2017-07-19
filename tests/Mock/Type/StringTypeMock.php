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
 * Class StringTypeMock
 * @package DanchukAS\Mock\Type
 */
class StringTypeMock extends TypeMock
{
    public static $maxSize;

    /**
     * @return \Generator
     */
    public static function getSample()
    {

        $string_list = [
            "empty" => ""
            , "true, cipher" => "1"
            , "false, cipher, empty" => "0"
            , "false, empty" => "false"
            , "true, 1" => "true"
            , "single ascii" => "f"
            , "cyrillic, multiByte(2 byte)" => "ї"
            , "special" => "0x00"
            , "empty, space, short" => str_repeat(" ", 16)
            , "normal usual" => str_repeat("q", 32)
            , "spec symbols, big" => str_repeat("[]", 64)
            , "spec symbols, huge" => str_repeat("_$~-@\"'\\.!#%^&*()+=/?><,", 10 * 1024)
        ];

        foreach ($string_list as $pattern => $string) {
            yield [$pattern => $string];
        }

    }
}