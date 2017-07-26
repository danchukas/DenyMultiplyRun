<?php
declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;

/**
 * Defines application Behat from the specific context.
 */
class BaseUse implements Context
{
    public $suspension = [];

    /** @AfterScenario */
    public function after(AfterScenarioScope $scope)
    {
        foreach ($this->suspension as $var_name => $value) {
            if (false === strpos($var_name, 'file')) {
                continue;
            }
            @unlink($value);
        }

        $this->suspension = [];
    }


    /**
     * @Transform :current_pid
     */
    public function currentPid()
    {
        return getmypid();
    }

    /**
     * @Transform :no_exists_pid
     */
    public function notExistPid()
    {
        $no_exists_pid = 1;
        while (++$no_exists_pid < PHP_INT_MAX) {
            if (false === posix_kill($no_exists_pid, 0)
                && 3 === posix_get_last_error()
            ) {
                break;
            }
        }

        return $no_exists_pid;
    }

    /**
     * @Given an existed file named :pid_file with :no_exists_pid
     */
    public function anExistedFileNamed($pid_file, $no_exists_pid)
    {
        $file_name = tempnam(sys_get_temp_dir(), 'vo_');

        file_put_contents($file_name, $no_exists_pid);

        $this->suspension[$pid_file] = $file_name;
    }

    /**
     * @Given a non existed :pid_file
     */
    public function aNonExistedFileAs($pid_file)
    {
        $this->suspension[$pid_file] = sys_get_temp_dir() . '/' . uniqid('vd_', true);
    }

    /**
     * @When I run
     */
    public function iRun(PyStringNode $command)
    {
        eval($this->substitution($command));
    }

    /**
     * @Transform :need_suspension_command
     */
    public function substitution($string)
    {
        $replacement = array_values($this->suspension);
        foreach ($replacement as &$value) {
            if (is_string($value)) {
                $value = '"' . $value . '"';
            }
        }
        $suspended = str_ireplace(array_keys($this->suspension), $replacement, $string);

        return $suspended;
    }

    /**
     * @Then file :pid_file should updated with :current_pid
     */
    public function fileShouldUpdatedWith($pid_file, $current_pid)
    {
        $this->fileShouldCreatedWith($pid_file, $current_pid);
    }

    /**
     * @Then file :pid_file should created with :current_pid
     */
    public function fileShouldCreatedWith($pid_file, $current_pid)
    {
        Assert::assertStringEqualsFile($this->suspension[$pid_file], $current_pid);
    }


}
