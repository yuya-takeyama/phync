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
            $commands[] = sprintf(
                '%s %s%s',
                $command,
                $this->getArgsToSyncCwd($destination),
                $this->getArgsForSpecificFiles($option->getFiles())
            );
        }
        return $commands;
    }

    public function getArgsToSyncCwd($destination)
    {
        $util = $this->fileUtil;
        $path = $util->getRealPath($util->getCwd()) . DIRECTORY_SEPARATOR;
        return sprintf('%s %s', $util->shellescape($path), $util->shellescape("{$destination}:{$path}"));
    }

    public function getArgsForSpecificFiles($files)
    {
        if (count($files) === 0) {
            return '';
        }
        $result = '';
        $util   = $this->fileUtil;
        foreach ($files as $file) {
            if ($util->isDir($util->getRealPath($file))) {
                $result .= $this->generateIncludeOptionForDir($file);
            } else {
                $result .= $this->generateIncludeOptionForFile($file);
            }
        }
        $result .= " --exclude '*'";
        return $result;
    }

    private function generateIncludeOptionForFile($file)
    {
        $util   = $this->fileUtil;
        $result = '';
        $names = explode(DIRECTORY_SEPARATOR, $file);
        $count = count($names);
        for ($i = 0; $i < $count; $i++) {
            $result .= ' --include ' . $util->shellescape('/' . join(DIRECTORY_SEPARATOR, $names) . ($i > 0 ? '/' : ''));
            array_pop($names);
        }
        return $result;
    }

    private function generateIncludeOptionForDir($file)
    {
        $util = $this->fileUtil;
        $file = $util->getRelativePath($file, $util->getCwd());
        return sprintf(
            ' --include %s --include %s --include %s',
            $util->shellescape("/{$file}/"),
            $util->shellescape("/{$file}/*"),
            $util->shellescape("/{$file}/**/*")
        );
    }
}
