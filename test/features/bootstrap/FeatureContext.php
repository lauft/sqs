<?php

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Lauft\Behat\BashExtension\Context\BashContext;
use Symfony\Component\Process\Process;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends BashContext
{
    private $testEtcDir;
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
        $this->rootDirectory = $dir;
        $this->process = new Process(null);
        $this->process->setEnv(array(
            'PATH=/bin:/usr/bin:'.$dir.DIRECTORY_SEPARATOR.'bin',
            'DEBUG=1'
        ));
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

        PHPUnit_Framework_Assert::assertEquals($expectedContent, $content);
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
        PHPUnit_Framework_Assert::assertFileExists($this->workingDir . DIRECTORY_SEPARATOR . $path);
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
        PHPUnit_Framework_Assert::assertFileNotExists($this->workingDir . DIRECTORY_SEPARATOR . $path);
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
        PHPUnit_Framework_Assert::assertFileExists($this->workingDir . DIRECTORY_SEPARATOR . $path);
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
        PHPUnit_Framework_Assert::assertFileNotExists($this->workingDir . DIRECTORY_SEPARATOR . $path);
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
        $this->process->setEnv(array('BEHAT_PARAMS' => (string) $value));
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
        PHPUnit_Framework_Assert::assertFileExists($path);

        $fileContent = trim(file_get_contents($path));
        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $fileContent = str_replace(PHP_EOL, "\n", $fileContent);
        }

        PHPUnit_Framework_Assert::assertEquals($this->getExpectedOutput($text), $fileContent);
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
