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
 * Class IntegerTypeMock
 * @package DanchukAS\Mock\Type
 */
class IntegerTypeMock extends TypeMock
{
    protected static $recommendCount = 6;

    /**
     * @return \Generator|int
     */
    public static function getSample()
    {
        $sample_list = [
            "zero, false, not positive" => 0
            , "false, negative" => -1
            , "true, positive" => 1
            , "max" => PHP_INT_MAX
            , "min" => PHP_INT_MIN
        ];
        foreach ($sample_list as $expression => $value) {
            yield [$expression => $value];
        }

        yield ["usual" => random_int(PHP_INT_MIN, PHP_INT_MAX)];
    }
}