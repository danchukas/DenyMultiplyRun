<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-18 08:48
 */

namespace DanchukAS\Mock;

/**
 * Class TypeList
 * @package DanchukAS\Mock
 */
class TypeList
{
    /**
     * @param array $need_type_list
     * @return array
     */
    public static function getMockList(array $need_type_list = [])
    {
        if (empty($need_type_list)) {
            $need_type_list = self::getDefaultList();
        }

        return self::getTypeMockList($need_type_list);
    }

    /**
     * @return array
     */
    private static function getDefaultList()
    {
        $need_type_list = [
            TypeEnum::ARRAY
            , TypeEnum::BOOLEAN
            , TypeEnum::DOUBLE
            , TypeEnum::INTEGER
            , TypeEnum::NULL
            , TypeEnum::OBJECT
            , TypeEnum::RESOURCE
            , TypeEnum::STRING
            , TypeEnum::UNKNOWN
        ];

        return $need_type_list;
    }

    /**
     * @param array $need_type_list
     * @return array
     */
    private static function getTypeMockList(array $need_type_list): array
    {
        $type_list = [];

        foreach ($need_type_list as $type_mock) {
            $class = 'DanchukAS\Mock\Type\\' . ucfirst($type_mock) . 'TypeMock';
            /** @var \DanchukAS\Mock\TypeMock $class */
            $type_list[$type_mock] = $class::getGenerator();
        }
        return $type_list;
    }
}
