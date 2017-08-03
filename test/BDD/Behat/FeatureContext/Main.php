<?php
declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use DanchukAS\Mock\PidFileTestCase;
use PHPUnit\Framework\Assert;

/**
 * Defines application Behat from the specific context.
 */
class Main implements Context
{
    private $varList = [];

    private $mainFile;
    private $mainCommand;

    private $attempts = [];

    private $pathAutoload = __DIR__ . '/../../../../vendor/autoload.php';

    /**
     * @Given access for create\/rewrite :pid_file
     * @param string $pid_file
     */
    public function accessForCreateRewrite(string $pid_file)
    {
        $filename = PidFileTestCase::generateTempFile();

        Assert::assertFileIsWritable($filename);

        $this->varList[$pid_file] = $filename;
    }

    /**
     * @Given runned :file_link which contains:
     * @param $file_link
     * @param PyStringNode $command
     */
    public function with($file_link, PyStringNode $command)
    {
        $filename = PidFileTestCase::generateTempFile();
        $do_seconds = 3;

        foreach ($this->varList as $var_name => $var_value) {
            if (is_array($var_value)) {
                continue;
            }

            if (is_string($var_value)) {
                $var_value = '"' . $var_value . '"';
            }

            $command = str_replace($var_name, $var_value, $command);
        }


        $file_content = <<<CONTENT
<?php 
    require "$this->pathAutoload";
    $command
    sleep($do_seconds);
CONTENT;

        file_put_contents($filename, $file_content);

        $this->mainFile = $filename;
        $this->mainCommand = $command;

        $this->varList[$file_link] = [
            'filename' => $filename
            , 'time' => $do_seconds
        ];

        static $descriptor_spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
            3 => ['pipe', 'w'] // for return result code
        ];
        $shell_command = "php -f {$filename}; echo $? >&3";
        $process_res = proc_open($shell_command, $descriptor_spec, $pipes, null, []);
        /** @var array $proc_details */
        $proc_details = proc_get_status($process_res);

        $this->varList[$file_link] = [
            'filename' => $filename
            , 'time' => $do_seconds
            , 'pid' => $proc_details['pid']
            , 'resource' => $process_res
            , 'pipes' => $pipes
        ];

    }

    /**
     * @When try run <some-php-script> <any> times during running already runned :file_link
     */
    public function tryRunSomePhpScriptAnyTimesDuringRunningAlreadyRunned($file_link, TableNode $table)
    {
        $this->attempts = [];

        foreach ($table as $example) {

            while ($example['any']-- > 0) {
                $result_code = $this->run_php_file($example['some-php-script']);
                $this->attempts[$example['some-php-script']] = $result_code;
            }

            if (// Посилає сигнал процесу щоб дізнатись чи він існує.
                // Якщо true - точно існує.
                // якщо false - процес може і бути, але запущений під іншим користувачем або інші ситуації.
                false === posix_kill($this->varList[$file_link]['pid'], 0)
                // 3 = No such process (тестувалось в Ubuntu 14, FreeBSD 9. В інших ОС можуть відрізнятись)
                // Якщо процеса точно в даний момент нема такого.
                // Для визова цієї функції необхідний попередній визов posix_kill.
                && posix_get_last_error() === 3
            ) {
                Assert::fail();
            }
        }

    }

    private function run_php_file($php_file)
    {
        if ('<similar-php-script>' === $php_file
            && !isset($this->varList[$php_file])
        ) {
            $filename = PidFileTestCase::generateTempFile();
            copy($this->mainFile, $filename);
            $this->varList[$php_file]['filename'] = $filename;
        }

        $file_name = $this->varList[$php_file]['filename'];

        system("php -f {$file_name} 1>/dev/null 2>/dev/null", $result_code);

        return $result_code;
    }

    /**
     * @Then all attempts are failed
     */
    public function allAttemptsAreFailed()
    {
        $success_run_code = 0;

        foreach ($this->attempts as $php_script => $result_code) {
            Assert::assertNotEquals($success_run_code, $result_code);
        }
    }


    /**
     * @Then given runned :php_script work regardless of subsequent attempts
     */
    public function givenRunnedWorkRegardlessOfSubsequentAttempts($php_script)
    {

        $handle_exit_code = $this->varList[$php_script]['pipes'][3];

        $exit_code = !feof($handle_exit_code)
            ? rtrim(fgets($handle_exit_code, 5), "\n")
            : null;

        foreach ($this->varList[$php_script]['pipes'] as $pipe) {
            fclose($pipe);
        }

        proc_close($this->varList[$php_script]['resource']);

        $success_exit_code = 0;
        Assert::assertEquals($success_exit_code, $exit_code);
    }


}
