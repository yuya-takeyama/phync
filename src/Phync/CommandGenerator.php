<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'File/Util.php';

/**
 * rsync command generator.
 *
 * @author Yuya Takeyama
 */
class Phync_CommandGenerator
{
    /**
     * @var Phync_Config
     */
    private $config;

    /**
     * @var Phync_FileUtil
     */
    private $fileUtil;

    public function __construct(Phync_Config $config, Phync_FileUtil $fileUtil)
    {
        $this->config   = $config;
        $this->fileUtil = $fileUtil;
    }

    public function getCommands(Phync_Option $option)
    {
        $commands = array();
        foreach ($this->config->getDestinations() as $destination) {
            $command = "rsync -avC";
            if ($option->isDryRun()) {
                $command .= " --dry-run";
            }
            if ($this->config->isDefaultChecksum() && ! $option->isChecksumSet()) {
                $command .= " --checksum";
            } else if ($option->isChecksum()) {
                $command .= " --checksum";
            }
            $command .= " --delete";
            if ($this->config->hasExcludeFrom()) {
                $command .= ' ' . $this->fileUtil->shellescape("--exclude-from={$this->config->getExcludeFrom()}");
            }
            if ($this->config->hasRsyncPath()) {
                $command .= ' ' . $this->fileUtil->shellescape("--rsync-path={$this->config->getRsyncPath()}");
            }
            if ($this->config->hasRsh()) {
                $command .= ' ' . $this->fileUtil->shellescape("--rsh={$this->config->getRsh()}");
            }
            foreach ($option->getFiles() as $file) {
                $commands[] = $command . ' ' . $this->getFileArgument($destination, $file);
            }
        }
        return $commands;
    }

    /**
     * ファイルとそのアップロード先を指定する引数を取得する.
     *
     * @param  string $destination
     * @param  string $file
     * @return string
     */
    private function getFileArgument($destination, $file)
    {
        $file = $this->fileUtil->getRealPath($file);
        if ($this->fileUtil->isDir($file)) {
            $file .= "/";
        }
        return $this->fileUtil->shellescape($file) . ' ' .
            $this->fileUtil->shellescape("{$destination}:" . $file);
    }
}
