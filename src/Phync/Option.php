<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'Console/Getopt.php';

class Phync_Option
{
    private $options;

    private $files;

    private $checksum;

    private $configFile;

    public function __construct($argv)
    {
        $opt = new Console_Getopt;
        list($options, $files) = $opt->getopt($argv, '', array(
            'execute',
            'checksum',
            'no-checksum',
            'config==',
        ));
        $this->options = $options;
        $this->files   = $files;

        $this->dryRun   = true;
        $this->checksum = NULL;

        $this->parse();
    }

    /**
     * 引数をパースする.
     *
     * @return void
     */
    public function parse()
    {
        foreach ($this->options as $option) {
            list($key, $value) = $option;
            $this->_parse($key, $value);
        }
    }

    private function _parse($key, $value)
    {
        switch ($key) {
        case '--execute':
            $this->setExecute(true);
            break;
        case '--checksum':
            $this->setChecksum(true);
            break;
        case '--no-checksum':
            $this->setChecksum(false);
            break;
        case '--config':
            $this->setConfigFile($value);
            break;
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
     * チェックサムを行うか.
     *
     * @return bool
     */
    public function isChecksum()
    {
        return (bool) $this->checksum;
    }

    /**
     * チェックサムが明示的に指定されているか
     *
     * @return bool
     */
    public function isChecksumSet()
    {
        return isset($this->checksum);
    }

    /**
     * チェックサムフラグをセットする.
     *
     * @param  bool $pred
     * @return void
     */
    public function setChecksum($pred)
    {
        $this->checksum = ((bool)$pred);
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

    /**
     * 設定ファイルのパスを取得する.
     *
     * @return string
     */
    public function getConfigFile()
    {
        if (is_null($this->configFile)) {
            return '.phync' . DIRECTORY_SEPARATOR . 'config.php';
        } else {
            return $this->configFile;
        }
    }

    /**
     * 設定ファイルのパスをセットする.
     *
     * @param  string
     * @return void
     */
    public function setConfigFile($configFile)
    {
        $this->configFile = $configFile;
    }
}
