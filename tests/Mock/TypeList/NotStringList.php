<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-18 11:33
 */

namespace DanchukAS\Mock\TypeList;


use DanchukAS\Mock\TypeEnum;
use DanchukAS\Mock\TypeList;

/**
 * Class NotStringList
 * @package DanchukAS\Mock\TypeList
 */
class NotStringList
{
    /**
     * @return array
     */
    public static function getList()
    {
        $type_enum = [
            TypeEnum::ARRAY
            , TypeEnum::BOOLEAN
            , TypeEnum::DOUBLE
            , TypeEnum::INTEGER
            , TypeEnum::NULL
            , TypeEnum::OBJECT
            , TypeEnum::RESOURCE
            , TypeEnum::UNKNOWN
        ];
        $type_gen_list = TypeList::getMockList($type_enum);

        $type_data_list = [];
        $real_value = null;
        /** @noinspection PhpVariableNamingConventionInspection */
        foreach ($type_gen_list as $type => $generator) {
            foreach ($generator as $value) {
                $type_data_list[] = $value;
            }
        }

        return $type_data_list;
    }
}
