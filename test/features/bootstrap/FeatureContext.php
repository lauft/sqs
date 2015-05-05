<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private static $varDir;
    private static $binDir;
    private static $etcDir;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /** @var Process|null */
    private $process;

    /** @var array<string,string> */
    private $processEnv;

    /** @var string */
    private $workingDir;

    /** @var string */
    private $testBinDir;

    /** @var string */
    private $testVarDir;

    /**
     * Cleans test folders in the temporary directory.
     *
     * @BeforeSuite
     */
    public static function cleanTestFolders()
    {
        if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sqs')) {
            self::clearDirectory($dir);
        }

        self::installSqsAt($dir);
    }

    /**
     * Cleans test folders in the temporary directory.
     *
     * @AfterSuite
     */
    public static function afterSuite()
    {
        if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sqs')) {
            self::clearDirectory($dir);
        }
    }

    /**
     * Prepares test folders in the temporary directory.
     *
     * @BeforeScenario
     */
    public function prepareTestFolders()
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sqs';

        if (!is_dir($dir)) {
            throw new Exception('preparation of '.$dir.' failed!');
        }

        $this->workingDir = $dir;
        $this->process = null;
        $this->processEnv = array(
            'PATH' => '/bin:/usr/bin:' . $dir . DIRECTORY_SEPARATOR . 'bin',
            'DEBUG' => '1',
        );
    }

    private static function installSqsAt($testsite)
    {
        self::$varDir = $testsite . DIRECTORY_SEPARATOR . 'var';
        self::$binDir = $testsite . DIRECTORY_SEPARATOR . 'bin';
        self::$etcDir = $testsite . DIRECTORY_SEPARATOR . 'etc';

        mkdir(self::$varDir, 0777, true);
        mkdir(self::$binDir, 0777, true);
        mkdir(self::$etcDir, 0777, true);

        $output = array();
        $return = '';
        chdir(getcwd().DIRECTORY_SEPARATOR.'..');
        exec('./configure --prefix '.$testsite, $output, $return);
        exec('make');
        exec('make install');

        exec('cp test/sqstesttask '.self::$binDir);
    }

    /**
     * @BeforeFeature
     * @param BeforeFeatureScope $scope
     */
    public static function beforeFeature(BeforeFeatureScope $scope)
    {
        exec('rm -rf '.self::$varDir.DIRECTORY_SEPARATOR.'sqs'.DIRECTORY_SEPARATOR.'*');
    }

    /**
     * @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public static function beforeScenario(BeforeScenarioScope $scope)
    {
        exec('pgrep -f sqs && kill -9 $(pgrep -f sqs)');
        exec('rm -rf '.self::$varDir.DIRECTORY_SEPARATOR.'sqs'.DIRECTORY_SEPARATOR.'*');
    }

    /**
     * @AfterScenario
     * @param AfterScenarioScope $scope
     */
    public static function afterScenario(AfterScenarioScope $scope)
    {
        exec('pgrep -f sqs && kill -9 $(pgrep -f sqs)');
        exec('rm -rf '.self::$varDir.DIRECTORY_SEPARATOR.'sqs'.DIRECTORY_SEPARATOR.'*');
    }

    /**
     * Creates a file with specified name and context in current workdir.
     *
     * @Given /^(?:there is )?a file named "([^"]*)" with:$/
     *
     * @param   string       $filename name of the file (relative path)
     * @param   PyStringNode $content  PyString string instance
     */
    public function aFileNamedWith($filename, PyStringNode $content)
    {
        $content = strtr((string) $content, array("'''" => '"""'));
        $this->createFile($this->workingDir . '/' . $filename, $content);
    }

    /**
     * @Given /^(?:there is )?a directory "([^"]*)"$/
     * @param $filename
     */
    public function aDirectory($filename)
    {
        mkdir($this->workingDir.DIRECTORY_SEPARATOR.$filename);
    }

    /**
     * @Given job :cmd is running
     * @Given :cnt jobs :cmd are running
     */
    public function iWaitForJobToRun($cmd, $cnt = 1, $timeout = 60)
    {
        $process = Process::fromShellCommandLine('while [ '.$cnt.' -gt $(pgrep -f '.$cmd.' | wc -l) ] ; do sleep 1 ; done');
        $process->setTimeout($timeout);
        $process->run();
        Assert::assertEquals(0, $process->getExitCode());
    }

    /**
     * @When I run :command with :arguments
     *
     * @param string $commandLine
     * @param string $arguments
     */
    public function iRun($commandLine, $arguments)
    {
        $arguments = strtr($arguments, array('\'' => '"'));

        $this->process = Process::fromShellCommandLine($commandLine . ' ' . $arguments);
        $this->process->setWorkingDirectory($this->workingDir);
        $this->process->setEnv($this->processEnv);
        $this->process->start();
        $this->process->wait();
    }

    /**
     * Asserts a file exists with specified name and context in current workdir.
     *
     * @Given /^there should be a file named "([^"]*)" with:$/
     *
     * @param   string $filename name of the file (relative path)
     * @param   PyStringNode $expectedContent PyString string instance
     * @throws Exception
     */
    public function thereShouldBeAFileNamedWith($filename, PyStringNode $expectedContent)
    {
        $expectedContent = strtr((string) $expectedContent, array("'''" => '"""'));

        $path = $this->workingDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($path)) {
            throw new Exception('invalid path "'.$path.'"');
        }

        $content = file_get_contents($path);

        Assert::assertEquals($expectedContent, $content);
    }

    /**
     * Moves user to the specified path.
     *
     * @Given /^I am in the "([^"]*)" path$/
     * @When /^I go to the "([^"]*)" path$/
     *
     * @param string $path
     */
    public function iAmInThePath($path)
    {
        $this->moveToNewPath($path);
    }

    /**
     * Checks whether a file at provided path exists.
     *
     * @Given /^file "([^"]*)" exists$/
     * @Then /^file "([^"]*)" should exist$/
     *
     * @param string $path
     */
    public function fileShouldExist($path)
    {
        Assert::assertFileExists($this->workingDir . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Checks whether a file at provided path exists.
     *
     * @Given /^file "([^"]*)" does not exist$/
     * @Then /^file "([^"]*)" should not exist$/
     *
     * @param string $path
     */
    public function fileShouldNotExist($path)
    {
        Assert::assertFileDoesNotExist($this->workingDir . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Checks whether a directory at provided path exists.
     *
     * @Given /^directory "([^"]*)" exists$/
     * @Then /^directory "([^"]*)" should exist$/
     *
     * @param string $path
     */
    public function directoryShouldExist($path)
    {
        // TODO add check for file type
        Assert::assertFileExists($this->workingDir . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Checks whether a directory at provided path exists.
     *
     * @Given /^directory "([^"]*)" does not exist$/
     * @Then /^directory  "([^"]*)" should not exist$/
     *
     * @param string $path
     */
    public function directoryShouldNotExist($path)
    {
        Assert::assertFileDoesNotExist($this->workingDir . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Sets specified ENV variable
     *
     * @When /^"BEHAT_PARAMS" environment variable is set to:$/
     *
     * @param PyStringNode $value
     */
    public function iSetEnvironmentVariable(PyStringNode $value)
    {
        $this->processEnv['BEHAT_PARAMS'] = (string) $value;
    }

    /**
     * Checks whether previously ran command passes|fails with provided output.
     *
     * @Then /^it should (fail|pass) with:$/
     *
     * @param   string       $success "fail" or "pass"
     * @param   PyStringNode $text    PyString text instance
     */
    public function itShouldPassWith($success, PyStringNode $text)
    {
        $this->itShouldExitWith($success);
        $this->theOutputShouldContain($text);
    }

    /**
     * Checks whether specified file exists and contains specified string.
     *
     * @Then /^"([^"]*)" file should contain:$/
     *
     * @param   string       $path file path
     * @param   PyStringNode $text file content
     */
    public function fileShouldContain($path, PyStringNode $text)
    {
        $path = $this->workingDir . '/' . $path;
        Assert::assertFileExists($path);

        $fileContent = trim(file_get_contents($path));
        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $fileContent = str_replace(PHP_EOL, "\n", $fileContent);
        }

        Assert::assertEquals($this->getExpectedOutput($text), $fileContent);
    }

    /**
     * Checks whether last command output contains provided string.
     *
     * @Then the output should contain:
     *
     * @param   PyStringNode $text PyString text instance
     */
    public function theOutputShouldContain(PyStringNode $text)
    {
        Assert::assertStringContainsString($this->getExpectedOutput($text), $this->getOutput());
    }

    /**
     * @Then the output should match:
     *
     * @param PyStringNode $string
     */
    public function theOutputShouldMatch(PyStringNode $string)
    {
        Assert::assertMatchesRegularExpression('/^'.$string.'$/', $this->getOutput());
    }

    private function getExpectedOutput(PyStringNode $expectedText)
    {
        $text = strtr($expectedText, array('\'\'\'' => '"""', '%%TMP_DIR%%' => sys_get_temp_dir() . DIRECTORY_SEPARATOR));

        // windows path fix
        if ('/' !== DIRECTORY_SEPARATOR) {
            $text = preg_replace_callback(
                '/ features\/[^\n ]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, $text
            );
            $text = preg_replace_callback(
                '/\<span class\="path"\>features\/[^\<]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, $text
            );
            $text = preg_replace_callback(
                '/\+[fd] [^ ]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, $text
            );
        }

        return $text;
    }

    /**
     * Checks whether previously ran command failed|passed.
     *
     * @Then /^it should (fail|pass)$/
     *
     * @param   string $success "fail" or "pass"
     */
    public function itShouldExitWith($success)
    {
        if ('fail' === $success) {
            if (0 === $this->getExitCode()) {
                echo 'Actual output:' . PHP_EOL . PHP_EOL . $this->getOutput();
            }

            Assert::assertNotEquals(0, $this->getExitCode());
        } else {
            if (0 !== $this->getExitCode()) {
                echo 'Actual output:' . PHP_EOL . PHP_EOL . $this->getOutput();
            }

            Assert::assertEquals(0, $this->getExitCode());
        }
    }

    private function getExitCode()
    {
        return $this->process->getExitCode();
    }

    private function getOutput()
    {
        $output = $this->process->getErrorOutput() . $this->process->getOutput();

        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $output = str_replace(PHP_EOL, "\n", $output);
        }

        // Replace wrong warning message of HHVM
        $output = str_replace('Notice: Undefined index: ', 'Notice: Undefined offset: ', $output);

        return trim(preg_replace("/ +$/m", '', $output));
    }

    private function createFile($filename, $content)
    {
        $path = dirname($filename);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents($filename, $content);
    }

    private function moveToNewPath($path)
    {
        $newWorkingDir = $this->workingDir .'/' . $path;
        if (!file_exists($newWorkingDir)) {
            mkdir($newWorkingDir, 0777, true);
        }

        $this->workingDir = $newWorkingDir;
    }

    private static function clearDirectory($path)
    {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);

        foreach ($files as $file) {
            $file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                self::clearDirectory($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }
}
