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
    const /** @noinspection PhpConstantNamingConventionInspection */
        ZERO = "zero, false, not positive";
    const MINUS_ONE = "false, negative";
    const /** @noinspection PhpConstantNamingConventionInspection */
        ONE = "true, positive";
    const /** @noinspection PhpConstantNamingConventionInspection */
        MAX = "max";
    const /** @noinspection PhpConstantNamingConventionInspection */
        MIN = "min";
    const USUAL = "usual";

    protected static $recommendCount = 6;

    /**
     * @return \Generator|int
     */
    public static function getSample()
    {
        $sample_list = [
            self::ZERO => 0
            , self::MINUS_ONE => -1
            , self::ONE => 1
            , self::MAX => PHP_INT_MAX
            , self::MIN => PHP_INT_MIN
        ];
        foreach ($sample_list as $expression => $value) {
            yield [$expression => $value];
        }

        yield [self::USUAL => random_int(PHP_INT_MIN, PHP_INT_MAX)];
//        for ($sequence_number = 2; ; $sequence_number++) {
//            yield [self::USUAL . "_$sequence_number" => random_int(PHP_INT_MIN, PHP_INT_MAX)];
//        }
    }
}