<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__) . '/../Event/ObserverInterface.php';
require_once dirname(__FILE__) . '/../Event/Event.php';
require_once dirname(__FILE__) . '/../FileUtil.php';

class Phync_Logger_FileDiffLogger extends Phync_Logger_AbstractLogger implements Phync_Event_ObserverInterface
{
    /**
     * @var string
     */
    private $diff;

    public function update(Phync_Event_Event $event)
    {
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
    }

    /**
     * ファイル一覧を取得
     * exclude_from 設定を反映した結果を取得するために dry-run 結果からファイル名を抽出する
     * 
     * @param   Phync_Event_Event
     * @param   Phync_Config
     * @param   Phync_FileUtil
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
     * dry-run 結果出力からファイル名を抽出
     *
     * @param   string
     * @return  array
     * @access  public
     */
    public function extractFileList($message)
    {
        $files = array();
        foreach (explode("\n", $message) as $line) {
            // ディレクトリ
            if (empty($line) || $line === './') {
                continue;
            }
            if (substr($line, -1, 1) === '/') {
                continue;
            }
            // ヘッダ、フッタ行
            if (substr($line, 0, 18) === 'building file list' || substr($line, 0, 5) === 'sent ' || substr($line, 0, 10) === 'total size') {
                continue;
            }
            // シンボリックリンク
            if (strpos($line, ' -> ') !== false) {
                $pos = strpos($line, ' -> ');
                $files[] = substr($line, 0, $pos);
                continue;
            }
            $files[] = $line;
        }

        return $files;
    }

    public function getFileDiff($targetPath, $rsh, $destinationHost, Phync_FileUtil $fileUtil)
    {
        if ($fileUtil->isLink($targetPath)) {
            $this->diff .= "{$targetPath} is link." . PHP_EOL;
            return;
        } elseif ($fileUtil->isDir($targetPath)) {
            foreach(new DirectoryIterator($targetPath) as $fileOrDir) {
                $this->getFileDiff($fileOrDir->getPathname(), $rsh, $destinationHost, $fileUtil);
            }
        } elseif ($fileUtil->isBinary($targetPath)) {
            $this->diff .= "{$targetPath} is binary." . PHP_EOL;
            return;
        } elseif ($fileUtil->isFile($targetPath)) {
            $this->diff .= $targetPath . PHP_EOL;
            $this->diff .= `{$rsh} {$destinationHost} cat {$targetPath} 2> /dev/null | diff - {$targetPath}`;
            $this->diff .= PHP_EOL;
        }
    }
}
