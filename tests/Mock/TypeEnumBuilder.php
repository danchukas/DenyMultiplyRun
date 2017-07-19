<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-18 12:17
 */

namespace DanchukAS\Mock;


/**
 * Class TypeEnumBuilder
 * @package DanchukAS\Mock
 */
class TypeEnumBuilder
{
    // available types
    protected static $boolean = 'boolean';
    protected static $integer = 'integer';
    protected static $double = 'double';
    protected static $string = 'string';
    protected static $array = 'array';
    protected static $object = 'object';
    protected static $resource = 'resource';
    protected static $null = 'null';
    protected static $unknown = 'unknown';

    public static function rebuild()
    {
        $type_list = self::getTypeList();

        $property_list = [];

        foreach ($type_list as $type_name) {
            $property_name = strtoupper($type_name);
            if (5 > strlen($type_name)) {
                $no_inspect_name = "/** @noinspection PhpConstantNamingConventionInspection */\n\t\t";
                $property_name = $no_inspect_name . $property_name;
            }
            $property_list[] = "const $property_name = \"$type_name\";";

        }

        $property_code = implode("\n\t", $property_list);

        $template = file_get_contents(__DIR__ . '/TypeEnum.tpl');

        $file_content = str_replace('{PROPERTY}', $property_code, $template);

        file_put_contents(__DIR__ . '/TypeEnum.php', $file_content);

    }

    /**
     * @return array
     */
    private static function getTypeList()
    {
        $type_list = [];

        $type_dir = __DIR__ . '/Type';
        // SCANDIR_SORT_ASCENDING - щоб уникнути зайвих комітів пов'язаних з білдом при різних умовах.
        $directory = scandir($type_dir, SCANDIR_SORT_ASCENDING);

        foreach ($directory as $file_mock) {
            if (is_file($type_dir . '/' . $file_mock)) {
                $type_mock = basename($file_mock, 'TypeMock.php');
                $type_list[] = strtolower($type_mock);
            }
        }

        return $type_list;
    }

}