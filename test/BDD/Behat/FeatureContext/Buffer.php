<?php
declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;


/**
 * Defines application Behat from the specific context.
 */
class Buffer implements Context
{

    public $variableList = [];

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {

        $this->variableList['PID current process'] = getmypid();
    }

    /**
     * @Given a non existed file as $:arg1
     */
    public function aNonExistedFileAs($arg1)
    {
        $arg1 = str_replace("$", "", $arg1);
        $this->variableList[$arg1] = sys_get_temp_dir() . '/' . uniqid('vd_', true);;
    }






    /**
     * @Given an existed file named :arg1 with no write access
     */
    public function anExistedFileNamedWithNoWriteAccess($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then called method throw Exception.
     */
    public function calledMethodThrowException()
    {
        throw new PendingException();
    }


    /**
     * @Given a non existed file named :arg1
     */
    public function aNonExistedFileNamed($arg1)
    {
        throw new PendingException();
    }


    /**
     * @Given a non existed file as :arg1
     */
    public function aNonExistedFileAs2($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Given an existed file named :arg1
     */
    public function anExistedFileNamed($arg1)
    {
        throw new PendingException();
    }
}
