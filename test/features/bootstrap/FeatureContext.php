<?php

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Lauft\Behat\BashExtension\Context\BashContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends BashContext
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
        @chdir($dir);
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
