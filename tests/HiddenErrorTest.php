<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-06-22 18:10
 */

namespace DanchukAS\HelperTest;

use DanchukAS\Helper\HiddenError;
use PHPUnit\Framework\TestCase;


require __DIR__ . '/../vendor/autoload.php';

class HiddenErrorTest extends TestCase
{

    function tearDown()
    {
        error_clear_last();
    }

    /**
     * @expectedException \Error
     */
    function testConstructor()
    {
        $class = "HiddenError";
        new $class();
    }


    function testCall()
    {
        self::markTestSkipped("wait apply fix for set_error_handle");
        $retry = 3;
        while ($retry--) {
            HiddenError::enable();
            HiddenError::disable();
        }
        self::assertNull(error_get_last());
    }

    /**
     * @expectedException \Error
     */
    function testWrongParam()
    {
        self::markTestSkipped("wait apply fix for set_error_handle");
        HiddenError::enable();
        strpos();
    }


    /**
     * @expectedException \Error
     */
    function testUndefinedFunction()
    {
        self::markTestIncomplete("maybe has logic error");
        return;
        HiddenError::enable();
        try {
            assdfdSF();
        } catch (\Error $throwable) {

        }
    }

    function testHandleAsCallback()
    {

        self::markTestIncomplete("maybe has logic error");
        return;

        try {
            error_clear_last();

            set_error_handler(function () {
            });
            restore_error_handler();
            self::assertNull(error_get_last());

            set_error_handler([__CLASS__, __FUNCTION__]);
            restore_error_handler();
            self::assertNull(error_get_last());

            echo @set_error_handler(1);
            self::assertNotNull(error_get_last());
            error_clear_last();

            echo @set_error_handler(['NotExistClass' . uniqid('_uniqid_'), 'anyMethod']);
            self::assertNotNull(error_get_last());
            error_clear_last();

            echo @set_error_handler([__CLASS__, 'notExistMethod' . uniqid('_uniqid_')]);
            self::assertNotNull(error_get_last());
            error_clear_last();

            echo @set_error_handler('notExistFunction' . uniqid('_uniqid_'));
            self::assertNotNull(error_get_last());
            error_clear_last();

        } catch (\Error $error) {
            self::markTestIncomplete("Author not tested. set_error_handler throw Error.");
        } catch (\Exception $exception) {
            self::markTestIncomplete("Author not tested. set_error_handler throw any Exception.");
        } catch (\Throwable $throwable) {
            self::markTestIncomplete("Author not tested. set_error_handler throw any Throwable.");
        }
    }


    function handle0()
    {
    }

    function handle1()
    {

        try {
            $return_set_error = set_error_handler("handle2");
        } catch (\Throwable $throwable) {
            throw new LogicException(PHP_EOL . " Test incomplete.");
        }

        switch ($return_set_error) {
            case null :
                echo PHP_EOL . "Warning: Exists 'handle1', but set_error_handler return NULL.";
                break;
            case 'handle1':
                echo PHP_EOL . "OK. set_error_handler return 'handle1'.";
                break;
            default:
                echo PHP_EOL . "Warning: Exist 'handle1', but set_error_handler return ";
                var_dump($return_set_error);
        }
    }

    function handle2()
    {
        echo PHP_EOL . "OK. 'handle2' runned.";
    }

    function handle3()
    {
    }

    function handle4()
    {
    }

    function handle5()
    {
    }


    function testSetErrorHandle()
    {

// implement error

// Starting point.
        set_error_handler("handle0");

//
        set_error_handler("handle1");

// @todo if comment trigger_error and uncomment call handle1 all right.
// handle1();

// generate error for handle1
        trigger_error("generate error1");

// generate error for handle2
        trigger_error("generate error2");

// result
        $return_set_error = set_error_handler("handle3");

        switch ($return_set_error) {
            case null :
                echo PHP_EOL . "Warning: Exists 'handle2', but set_error_handler return NULL.";
                break;
            case 'handle2':
                echo PHP_EOL . "OK. set_error_handler return 'handle2'.";
                break;
            default:
                echo PHP_EOL . "Warning: Exist 'handle2', but set_error_handler return ";
                var_dump($return_set_error);
        }

// pop handle3 from stack
        restore_error_handler();

// pop handle2 from stack
        restore_error_handler();

// now must be handle1 on top and is active. Test.
// but handle0 on top and is active.
        $return_set_error = set_error_handler("handle3");
        switch ($return_set_error) {
            case null :
                echo PHP_EOL . "Warning: Exists 'handle1', but set_error_handler return NULL.";
                break;
            case 'handle1':
                echo PHP_EOL . "OK. set_error_handler return 'handle1'.";
                break;
            case 'handle0':
                echo PHP_EOL . "ERROR. Exists 'handle1', but set_error_handler return 'handle0'."
                    . " So handle2 was not existed in stack.";
                break;
            default:
                echo PHP_EOL . "Warning: Exist 'handle1', but set_error_handler return ";
                var_dump($return_set_error);
        }
    }

}


