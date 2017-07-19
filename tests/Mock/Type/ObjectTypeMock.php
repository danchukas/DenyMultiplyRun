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
 * Class ObjectTypeMock
 * @package DanchukAS\Mock\Type
 */
class ObjectTypeMock extends TypeMock
{
    protected static $optimalCount = 5;

    /**
     * @return \Generator
     */
    public static function getSample()
    {
        $sample_list = [
            "anonymous class" => new class
            {
            }
            , "anonymous function" => function () {
            }
            , "minimal items" => new \stdClass
            , "usual" => new \Exception
            , "with namespace" => new self
        ];

        foreach ($sample_list as $expression => $value) {
            yield [$expression => $value];
        }
    }
}