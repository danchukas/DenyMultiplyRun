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
     * @return \array
     */
    public static function getGenerator($count = null)
    {
        if (null === $count) {
            $count = static::getOptimalCount();
        }

        $mock_list = [];

        $generator = static::getSample();
        foreach ($generator as $value) {
            $mock_list[] = $value;
            if (--$count <= 0) {
                break;
            }
        }

        return $mock_list;
    }

    /**
     * @return int
     */
    protected static function getOptimalCount()
    {
        return static::$optimalCount;
    }

    /**
     * @return \Generator sample of type
     */
    abstract public static function getSample();
}