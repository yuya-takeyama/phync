<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__) . '/Config.php';
require_once dirname(__FILE__) . '/Option.php';
require_once dirname(__FILE__) . '/FileUtil.php';
require_once dirname(__FILE__) . '/Event/Dispatcher.php';
require_once dirname(__FILE__) . '/Event/Event.php';
require_once dirname(__FILE__) . '/Logger/NamedTextLogger.php';
require_once dirname(__FILE__) . '/CommandGenerator.php';
require_once dirname(__FILE__) . '/Exception/ConfigNotFound.php';
require_once dirname(__FILE__) . '/Exception/InvalidArgument.php';
require_once dirname(__FILE__) . '/Exception/FileNotFound.php';
require_once dirname(__FILE__) . '/Exception/Abort.php';

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
     * @var Phync_Event_Dispatcher
     */
    private $dispatcher;

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
     * @param array $params
     */
    public function __construct($params)
    {
        $this->env        = $params['env'];
        $this->option     = $params['option'];
        $this->config     = $params['config'];
        $this->fileUtil   = $params['file_util'];
        $this->dispatcher = new Phync_Event_Dispatcher;

        $this->dispatcher->addObserver(new Phync_Logger_NamedTextLogger);

        $this->dispatcher->on('after_config_loading', array($this, 'validateFiles'));
        $this->dispatcher->on('before_all_command_execution', array($this, 'displayCommands'));
        $this->dispatcher->on('before_all_command_execution', array($this, 'confirmExecution'));
        $this->dispatcher->on('before_all_command_execution', array($this, 'displayBeforeExecutionMessage'));
        $this->dispatcher->on('after_all_command_execution', array($this, 'displayExitStatus'));
    }

    /**
     * コンストラクタに適切な引数を渡して実行する
     */
    public static function start()
    {
        try {
            $self = new self(array(
                'env'       => $_SERVER,
                'option'    => new Phync_Option($_SERVER['argv']),
                'config'    => self::loadConfig(),
                'file_util' => new Phync_FileUtil,
            ));
            return $self->run();
        }
        catch (Exception $e) {
            $klass = get_class($e);
            echo "{$klass}: {$e->getMessage()}", PHP_EOL;
            exit(Phync_Application::STATUS_EXCEPTION);
        }
    }

    public function run()
    {
        echo "Phync ver. " . Phync::VERSION, PHP_EOL, PHP_EOL;
        $this->dispatcher->dispatch('after_config_loading', $this->getEvent());
        $generator = new Phync_CommandGenerator($this->config, $this->fileUtil);
        $commands  = $generator->getCommands($this->option);
        $this->dispatcher->dispatch('before_all_command_execution', array(
            'app'      => $this,
            'commands' => $commands
        ));
        foreach ($commands as $command) {
            $this->dispatcher->dispatch('before_command_execution', array(
                'app'     => $this,
                'command' => $command,
            ));
            passthru($command, $status);
            $this->dispatcher->dispatch('after_command_execution', array(
                'app'     => $this,
                'command' => $command,
                'status'  => $status
            ));
        }
        $this->dispatcher->dispatch('after_all_command_execution', $this->getEvent());
    }

    public static function loadConfig()
    {
        $file = '.phync' . DIRECTORY_SEPARATOR . 'config.php';
        if (file_exists($file) && is_readable($file)) {
            $config = include $file;
            try {
                return new Phync_Config($config);
            } catch (Exception $e) {
                throw new RuntimeException(self::getConfigExample($e->getMessage()));
            }
        } else {
            throw new Phync_Exception_ConfigNotFound(self::getConfigExample("Configuration file \"{$file}\" is not found."));
        }
    }

    public function getLogDirectory()
    {
        if ($this->config->hasLogDirectory()) {
            return $this->fileUtil->getRealPath($this->config->getLogDirectory());
        } else {
            return  $this->fileUtil->getRealPath('.phync' . DIRECTORY_SEPARATOR . 'log');
        }
    }

    public static function getConfigExample($message)
    {
        return <<<__EXAMPLE__
{$message}

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
    'rsync_path'   => '/usr/bin/rsync',
    'rsh'          => '/usr/bin/ssh',
);
__EXAMPLE__;
    }

    public function getUsage($message)
    {
        return <<<__USAGE__
{$message}

Usage:
  phync [--execute] file [more files...]
__USAGE__;
    }

    public function getOption()
    {
        return $this->option;
    }

    public function getEvent()
    {
        return new Phync_Event_Event(array('app' => $this));
    }

    public static function validateFiles($event)
    {
        $files = $event->app->getOption()->getFiles();
        foreach ($files as $file) {
            if (!file_exists($file)) {
                throw new Phync_Exception_FileNotFound("\"{$file}\" is not found.");
            }
        }
    }

    public function displayCommands($event)
    {
        echo "Generated commands:", PHP_EOL;
        foreach ($event->commands as $command) {
            echo $command, PHP_EOL;
        }
    }

    public function confirmExecution($event)
    {
        if ($event->app->option->isDryRun()) {
            return;
        }
        while (true) {
            echo "Execute these commands? (Y/N) [N]: ";
            $answer = fgets(STDIN);
            if (is_string($answer)) {
                $flag = strtoupper(substr(chop($answer), 0, 1));
                if ($flag === '') {
                    $flag = 'N';
                }
                if ($flag === 'Y') {
                    return;
                } else if ($flag === 'N') {
                    throw new Phync_Exception_Abort('Aborted execution.');
                }
            }
            echo "Invalid input.", PHP_EOL;
        }
    }

    public function displayBeforeExecutionMessage()
    {
        echo "Executing rsync commands...", PHP_EOL;
    }

    public function displayExitStatus($event)
    {
        if ($event->app->getOption()->isDryRun() === false) {
            echo PHP_EOL, "Exit in execute mode.", PHP_EOL;
        } else {
            echo PHP_EOL, "Exit in dry-run mode.", PHP_EOL;
        }
    }
}
