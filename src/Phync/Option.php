<?php
require_once 'Console/Getopt.php';
require_once 'File/Util.php';

class Phync_Option
{
    private $options;

    private $files;

    public function __construct($argv)
    {
        $opt = new Console_Getopt;
        list($options, $files) = $opt->getopt($argv, '', array('execute'));
        $this->options = $options;
        // @TODO コンストラクタでは存在チェックをしないようにする
        foreach ($files as $key => $value) {
            if (file_exists($value)) {
                $files[$key] = File_Util::realPath($value);
            } else {
                throw new RuntimeException("File Not Found: {$value}");
            }
        }
        $this->files   = $files;

        $this->dryRun = true;

        $this->parse();
    }

    public function parse()
    {
        foreach ($this->options as $key => $value) {
            switch ($key) {
                case '--execute':
                    $this->setExecute(true);
                    break;
            }
        }
    }

    public function setExecute($pred)
    {
        $this->dryRun = ((bool)$pred) === false;
    }

    public function isDryRun()
    {
        return $this->dryRun;
    }

    public function hasFiles()
    {
        return is_array($this->files) && count($this->files) > 0;
    }

    public function getFiles()
    {
        return $this->files;
    }
}
