<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-18 12:17
 */

namespace DanchukAS\Mock;


class TypeEnumBuilder
{
    // available types
    protected static $boolean = "boolean";
    protected static $integer = "integer";
    protected static $double = "double";
    protected static $string = "string";
    protected static $array = "array";
    protected static $object = "object";
    protected static $resource = "resource";
    protected static $null = "null";
    protected static $unknown = "unknown";

    public static function rebuild()
    {
        $typeList = self::getTypeList();

        $property_list = [];

        foreach ($typeList as $type) {
            $property_name = strtoupper($type);
            if (5 > strlen($type)) {
                $no_inspect_name = "/** @noinspection PhpConstantNamingConventionInspection */\n\t\t";
                $property_name = $no_inspect_name . $property_name;
            }
            $property_list[] = "public const $property_name = \"$type\";";

        }

        $property_code = implode("\n\t", $property_list);

        $template = file_get_contents(__DIR__ . '/TypeEnum.tpl');

        $code = str_replace("{PROPERTY}", $property_code, $template);

        file_put_contents(__DIR__ . "/TypeEnum.php", $code);

    }

    /**
     * @return array
     */
    private static function getTypeList()
    {
        $typeList = [];

        $type_dir = __DIR__ . "/Type";
        $directory = scandir($type_dir);

        foreach ($directory as $file) {
            if (is_file($type_dir . '/' . $file)) {
                $type = basename($file, "TypeMock.php");
                $typeList[] = strtolower($type);
            }
        }

        return $typeList;
    }

}