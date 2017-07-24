<?php
declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use PHPUnit\Framework\Assert;


/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
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
     * @When I run :arg1
     */
    public function iRun($arg1)
    {
        extract($this->variableList, EXTR_SKIP);
        eval($arg1);

    }


    /**
     * @Then file :arg2 should created with :arg1
     */
    public function fileShouldCreatedWith($arg1, $arg2)
    {
        Assert::assertStringEqualsFile($arg2, $this->variableList[$arg1]);
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
     * @Given an existed file named :arg1
     */
    public function anExistedFileNamed($arg1)
    {
        $arg1 = str_replace("$", "", $arg1);
        $this->variableList[$arg1] = sys_get_temp_dir() . '/' . uniqid('vd_', true);;
    }

    /**
     * @Then file :arg2 should updated with :arg1
     */
    public function fileShouldUpdatedWith($arg1, $arg2)
    {
        Assert::assertStringEqualsFile($arg2, $this->variableList[$arg1]);

    }

}
