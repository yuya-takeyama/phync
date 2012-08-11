<?php
class Phync_Config
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        if ($this->hasDestinations() === false) {
            throw new RuntimeException("No destinations are specified.");
        }
    }

    public function hasDestinations()
    {
        return isset($this->config['destinations']) && is_array($this->config['destinations']);
    }

    public function getDestinations()
    {
        return $this->config['destinations'];
    }

    public function hasExcludeFrom()
    {
        return isset($this->config['exclude_from']) && is_string($this->config['exclude_from']);
    }

    public function getExcludeFrom()
    {
        return $this->config['exclude_from'];
    }

    public function hasRsyncPath()
    {
        return isset($this->config['rsync_path']) && is_string($this->config['rsync_path']);
    }

    public function getRsyncPath()
    {
        return $this->config['rsync_path'];
    }

    public function hasRsh()
    {
        return isset($this->config['rsh']) && is_string($this->config['rsh']);
    }

    public function getRsh()
    {
        return $this->config['rsh'];
    }

    /**
     * デフォルトでチェックサムを行うか
     *
     * @return bool
     */
    public function isDefaultChecksum()
    {
        return array_key_exists('default_checksum', $this->config) &&
            (bool) $this->config['default_checksum'];
    }
}
