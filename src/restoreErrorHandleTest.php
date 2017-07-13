<?php
// it does not matter.
declare(strict_types=1);

// implement error
function handle0()
{
    print __FUNCTION__;
}

function handle1()
{

    restore_error_handler();
    print __FUNCTION__;
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

// Starting point.
set_error_handler("handle0");

// It is begin.
set_error_handler("handle1");

// if comment trigger_error and uncomment call handle1 all right.
// handle1();

// generate error for handle1
trigger_error("generate error1");

// generate error for handle0
trigger_error("generate error0");

// result
$return_set_error = set_error_handler("handle3");

switch ($return_set_error) {

    // now $return_set_error = null instead 'handle2'
    case null :
        echo PHP_EOL . "Warning: Exists 'handle0', but set_error_handler return NULL.";
        break;

    case 'handle0':
        echo PHP_EOL . "OK. set_error_handler return 'handle0'.";
        break;
    default:
        echo PHP_EOL . "Warning: Exist 'handle0', but set_error_handler return ";
        var_dump($return_set_error);
}


/*
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

    // now $return_set_error = 'handle0' instead 'handle1'
    case 'handle0':
        echo PHP_EOL . "ERROR. Exists 'handle1', but set_error_handler return 'handle0'."
            . " So handle2 was not existed in stack.";
        break;

    default:
        echo PHP_EOL . "Warning: Exist 'handle1', but set_error_handler return ";
        var_dump($return_set_error);
}

*/