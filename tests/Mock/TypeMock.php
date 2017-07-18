<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-18 08:46
 */

namespace DanchukAS\Mock;

/**
 * Class TypeMock
 * @package DanchukAS\Mock
 */
abstract class TypeMock
{

    protected static $optimalCount = 1;

    /**
     * @param int $count Кількість елементів для генератора
     * @return \Generator
     */
    public static function getGenerator($count = null)
    {
        if (is_null($count)) {
            $count = static::getOptimalCount();
        }

        while ($count--) {
            yield static::getSample();
        }
    }

    /**
     * @return int
     */
    protected static function getOptimalCount()
    {
        return static::$optimalCount;
    }

    /**
     * @return mixed sample of type
     */
    abstract public static function getSample();
}