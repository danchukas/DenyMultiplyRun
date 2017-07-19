<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-18 08:46
 */

namespace DanchukAS\Mock\Type;

use DanchukAS\Mock\TypeEnum;
use DanchukAS\Mock\TypeList;
use DanchukAS\Mock\TypeMock;

/**
 * Class NullTypeMock
 * @package DanchukAS\Mock\Type
 */
class ArrayTypeMock extends TypeMock
{
    /**
     * @return \Generator
     */
    public static function getSample()
    {
        yield ["empty" => []];

        $type_enum = [
            TypeEnum::STRING
            , TypeEnum::BOOLEAN
            , TypeEnum::DOUBLE
            , TypeEnum::INTEGER
            , TypeEnum::NULL
            , TypeEnum::OBJECT
            , TypeEnum::RESOURCE
            , TypeEnum::UNKNOWN
        ];

        foreach (TypeList::getMockList($type_enum) as $type_gen_list) {
            foreach ($type_gen_list as $type_mock) {
                yield [$type_mock];
            }
        }
    }

    /**
     * @return int
     */
    protected static function getOptimalCount()
    {
        static $count = null;
        if (is_null($count)) {
            $type_enum = [
                TypeEnum::STRING
                , TypeEnum::BOOLEAN
                , TypeEnum::DOUBLE
                , TypeEnum::INTEGER
                , TypeEnum::NULL
                , TypeEnum::OBJECT
                , TypeEnum::RESOURCE
                , TypeEnum::UNKNOWN
            ];
            $count = count(TypeList::getMockList($type_enum));
            self::$optimalCount = $count;
        }

        return parent::getOptimalCount();
    }
}