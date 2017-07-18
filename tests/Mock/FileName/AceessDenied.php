<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-18 08:46
 */

namespace DanchukAS\Mock\Type;

/**
 * Class NullTypeMock
 * @package DanchukAS\Mock\Type
 */
class AceessDenied
{
    /**
     * @return null
     */
    public static function getSample()
    {
        yield ["the only one" => null];
    }
}