<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-15 01:17
 */

namespace DanchukAS\DenyMultiplyRunTest;

use PHPUnit\Framework\TestCase;

/**
 * Class DenyUseTest
 * Як використовувати заборонено.
 * @package DanchukAS\DenyMultiplyRunTest
 */
class DenyUseTest extends TestCase
{
    /**
     * @expectedException \Error
     */
    public function testConstructor()
    {
        // Because not founded how disable inspection "Call to private from invalid context" for phpstorm.
        //new $class; new DenyMultiplyRun;
        $class = "DenyMultiplyRun";
        new $class;
    }

}
