<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Phync_Logger_FileDiffLogger extends Phync_Logger_AbstractLogger implements Phync_Event_ObserverInterface
{
    /**
     * @var string
     */
    private $diff;

    /**
     * Status that indicates whether the run already.
     *
     * @var boolean
     */
    private $executed = false;

    public function update(Phync_Event_Event $event)
    {
        if ($this->executed === true) {
            return;
        }

        switch ($event->getName()) {
            case 'after_config_loading':
                if (!self::$name) {
                    $this->getName();
                }
                $this->log = $this->openLogFile($event, self::$name);
                break;
            case 'before_command_execution':
                $fileUtil = new Phync_FileUtil;
                $this->onBeforeCommandExecution($event, $fileUtil);
                break;
            default:
                break;
        }
    }

    public function onBeforeCommandExecution(Phync_Event_Event $event, Phync_FileUtil $fileUtil)
    {
        $config = $event->app->getConfig();
        $files = $this->getFileList($event, $config, $fileUtil);

        list($destinationHost) = $config->getDestinations();
        $workingDir = $fileUtil->getCwd();
        $rsh = $config->getRsh();

        foreach ($files as $fileOrDir) {
            $targetPath = $workingDir . DIRECTORY_SEPARATOR . $fileOrDir;
            $this->getFileDiff($targetPath, $rsh, $destinationHost, $fileUtil);
        }

        $this->write('[DIFF]', PHP_EOL . $this->diff);
        $this->executed = true;
    }

    /**
     * Returns target file list.
     * Performing a dry-run in order to obtain a result reflecting the settings for 'exclude_from'.
     *
     * @param   Phync_Event_Event   $event
     * @param   Phync_Config        $config
     * @param   Phync_FileUtil      $fileUtil
     * @return  array
     * @access  public
     */
    public function getFileList( Phync_Event_Event $event, Phync_Config $config, Phync_FileUtil $fileUtil)
    {
        $option = clone $event->app->getOption();
        $option->setDryRun();
        $commandGenerator = new Phync_CommandGenerator($config, $fileUtil);
        list($command) = $commandGenerator->getCommands($option);
        $message = `{$command}`;

        return $this->extractFileList($message, $fileUtil);
    }

    /**
     * Extracts file list from result of dry-run.
     *
     * @param   string  $message
     * @return  array   $files
     * @access  public
     */
    public function extractFileList($message)
    {
        $files = array();
        foreach (explode("\n", $message) as $line) {
            // directory
            if (empty($line) || $line === './') {
                continue;
            }
            if (substr($line, -1, 1) === '/') {
                continue;
            }
            // header, footer
            if (substr($line, 0, 18) === 'building file list' || substr($line, 0, 5) === 'sent ' || substr($line, 0, 10) === 'total size') {
                continue;
            }
            // symlink
            if (strpos($line, ' -> ') !== false) {
                $pos = strpos($line, ' -> ');
                $files[] = substr($line, 0, $pos);
                continue;
            }
            $files[] = $line;
        }

        return $files;
    }

    /**
     * Returns diff.
     *
     * @param   string          $targetPath
     * @param   string          $rsh
     * @param   Phync_FileUtil  $destinationHost
     * @return  void
     * @access  public
     */
    public function getFileDiff($targetPath, $rsh, $destinationHost, Phync_FileUtil $fileUtil)
    {
        if ($fileUtil->isLink($targetPath)) {
            $this->diff .= "{$targetPath} is link." . PHP_EOL;
        } elseif ($fileUtil->isDir($targetPath)) {
            foreach(new DirectoryIterator($targetPath) as $fileOrDir) {
                $this->getFileDiff($fileOrDir->getPathname(), $rsh, $destinationHost, $fileUtil);
            }
        } elseif ($fileUtil->isFile($targetPath)) {
            $this->diff .= $targetPath . PHP_EOL;
            $this->diff .= `{$rsh} {$destinationHost} cat {$targetPath} 2> /dev/null | diff - {$targetPath}`;
            $this->diff .= PHP_EOL;
        }

        return;
    }
}
