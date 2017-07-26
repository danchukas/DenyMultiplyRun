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
 * Class NullTypeMock
 * @package DanchukAS\Mock\Type
 */
class NullTypeMock extends TypeMock
{
    /**
     * @yield \Generator
     */
    public static function getSample()
    {
        yield ['the only one' => null];
    }
}