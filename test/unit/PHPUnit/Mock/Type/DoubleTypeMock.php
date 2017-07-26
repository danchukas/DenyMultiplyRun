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
 * Class DoubleTypeMock
 * @package DanchukAS\Mock\Type
 */
class DoubleTypeMock extends TypeMock
{
    protected static $optimalCount = 3;

    /**
     * @return \Generator
     */
    public static function getSample()
    {
        yield ['double(big)' => PHP_INT_MAX / 0.3];
        yield ['double(small)' => PHP_INT_MIN / 0.9];
        yield ['double(near zero)' => -1.0 / 3.0];
    }
}