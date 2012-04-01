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
        $this->files   = $files;

        $this->dryRun = true;

        $this->parse();
    }

    /**
     * 引数をパースする.
     *
     * @return void
     */
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

    /**
     * 実行フラグをセットする.
     *
     * @param  bool $pred
     * @return void
     */
    public function setExecute($pred)
    {
        $this->dryRun = ((bool)$pred) === false;
    }

    /**
     * ドライランで実行するか.
     *
     * @return bool
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }

    /**
     * 引数にファイルが指定されているか.
     *
     * @return bool
     */
    public function hasFiles()
    {
        return is_array($this->files) && count($this->files) > 0;
    }

    /**
     * 引数にファイルとして指定された文字列を全て取得する.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
}
