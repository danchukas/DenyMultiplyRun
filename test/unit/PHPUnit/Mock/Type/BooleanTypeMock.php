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
 * Class BooleanTypeMock
 * @package DanchukAS\Mock\Type
 */
class BooleanTypeMock extends TypeMock
{
    protected static $optimalCount = 2;

    /**
     * @return \Generator
     */
    public static function getSample()
    {
        yield ['bool(false)' => false];
        yield ['bool(true)' => true];
    }
}