<?php
require_once dirname(__FILE__) . '/Config.php';
require_once dirname(__FILE__) . '/Option.php';
require_once dirname(__FILE__) . '/CommandGenerator.php';

/**
 * Phync: Simple rsync wrapper in PHP.
 *
 * @author Yuya Takeyama
 */
class Phync_Application
{
    const STATUS_EXCEPTION = 255;

    private $env;

    /**
     * @var Phync_Option
     */
    private $option;

    /**
     * @var Phync_Config
     */
    private $config;

    /**
     * Constructor.
     *
     * @param  array $argv PHP の $argv 変数を渡す.
     * @param  array $env  PHP の $_SERVER 変数を渡す.
     */
    public function __construct($argv, $env)
    {
        $this->env    = $env;
        $this->option = new Phync_Option($argv);
    }

    public function run()
    {
        $this->loadConfig();

        if ($this->option->hasFiles() === false) {
            throw new RuntimeException($this->getUsage("No files are specified."));
        } else {
            $generator = new Phync_CommandGenerator;
            $commands  = $generator->getCommands($this->config, $this->option);
            echo "Generated commands:", PHP_EOL;
            foreach ($commands as $command) {
                echo $command, PHP_EOL;
            }
            echo PHP_EOL;
            echo "Executing rsync command...", PHP_EOL;
            foreach ($commands as $command) {
                passthru($command);
            }
            if ($this->option->isDryRun() === false) {
                echo PHP_EOL, "Exit in execute mode.", PHP_EOL;
            } else {
                echo PHP_EOL, "Exit in dry-run mode.", PHP_EOL;
            }
        }
    }

    private function loadConfig()
    {
        $file = $this->env['HOME'] . DIRECTORY_SEPARATOR . '.phync/config.php';
        if (file_exists($file) && is_readable($file)) {
            $config = include $file;
            try {
                $this->config = new Phync_Config($config);
            } catch (Exception $e) {
                throw new RuntimeException($this->getConfigExample($e->getMessage()));
            }
        } else {
            throw new RuntimeException($this->getConfigExample("Configuration file \"{$config}\" not found."));
        }
    }

    public function getConfigExample($message)
    {
        return <<<__EXAMPLE__
Config Error: {$message}

Example:
<?php
return array(
    // Destination servers.
    'destinations' => array(
        'foo.example.com',
        'bar.example.com',
        'baz.example.com',
    ),
    'exclude_from' => '/path/to/exclude.lst',
    'rsync_path'   => '/usr/bin/rsnyc',
);
__EXAMPLE__;
    }

    public function getUsage($message)
    {
        return <<<__USAGE__
Argument Error: {$message}

Usage:
  phync [--execute] file [more files...]
__USAGE__;
    }
}
