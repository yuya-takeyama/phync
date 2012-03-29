<?php
class Phync_CommandGenerator
{
    public function getCommands(Phync_Config $config, Phync_Option $option)
    {
        $commands = array();
        foreach ($config->getDestinations() as $destination) {
            $command = "rsync -avC";
            if ($option->isDryRun()) {
                $command .= " --dry-run";
            }
            $command .= " --delete";
            if ($config->hasExcludeFrom()) {
                $command .= ' ' . escapeshellarg("--exclude-from={$config->getExcludeFrom()}");
            }
            if ($config->hasRsyncPath()) {
                $command .= ' ' . escapeshellarg("--rsync-path={$config->getRsyncPath()}");
            }
            if ($config->hasRsh()) {
                $command .= ' ' . escapeshellarg("--rsh={$config->getRsh()}");
            }
            foreach ($option->getFiles() as $file) {
                $commands[] = $command . ' ' . escapeshellarg($file) . ' ' . escapeshellarg("{$destination}:{$file}");
            }
        }
        return $commands;
    }
}
