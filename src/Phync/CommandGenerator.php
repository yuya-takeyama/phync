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
            $commands[] = sprintf('%s %s', $command, $this->getArgsToSyncCwd($destination));
        }
        return $commands;
    }

    public function getArgsToSyncCwd($destination)
    {
        $util = $this->fileUtil;
        $path = $util->getRealPath($util->getCwd()) . DIRECTORY_SEPARATOR;
        return sprintf('%s %s', $util->shellescape($path), $util->shellescape("{$destination}:{$path}"));
    }
}
