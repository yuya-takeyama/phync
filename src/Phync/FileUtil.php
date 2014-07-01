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
 * Wrapper class of file operation.
 *
 * @author Yuya Takeyama
 */
class Phync_FileUtil
{
    /**
     * @var array
     */
    private $memo;

    public function __construct()
    {
        $this->memo = array();
    }

    public function getRealPath($path)
    {
        return File_Util::realPath($path);
    }

    public function getRelativePath($path, $root, $separator = DIRECTORY_SEPARATOR)
    {
        return File_Util::relativePath($this->getRealPath($path), $this->getRealPath($root), $separator);
    }

    public function isDir($path)
    {
        $args = func_get_args();
        $key  = $this->_generateCacheKey(__METHOD__, $args);

        if (array_key_exists($key, $this->memo) === false) {
            $this->memo[$key] = is_dir($path);
        }

        return $this->memo[$key];
    }

    public function isFile($path)
    {
        $args = func_get_args();
        $key  = $this->_generateCacheKey(__METHOD__, $args);

        if (array_key_exists($key, $this->memo) === false) {
            $this->memo[$key] = is_file($path);
        }

        return $this->memo[$key];
    }

    public function isLink($path)
    {
        $args = func_get_args();
        $key  = $this->_generateCacheKey(__METHOD__, $args);

        if (array_key_exists($key, $this->memo) === false) {
            $this->memo[$key] = is_link($path);
        }

        return $this->memo[$key];
    }

    public function shellescape($arg)
    {
        return escapeshellarg($arg);
    }

    /**
     * カレントワーキングディレクトリを取得する
     *
     * カレントディレクトリがシンボリックリンクだった場合、そのシンボリックリンク自体のパスを返す
     * PHP の getcwd() ではシンボリックリンクが解決されてしまうので、その代替
     *
     * @TODO OS によって挙動が違わないかの調査
     *
     * @return string
     */
    public function getCwd()
    {
        $args = func_get_args();
        $key  = $this->_generateCacheKey(__METHOD__, $args);

        if (array_key_exists($key, $this->memo) === false) {
            $this->memo[$key] = chop(`pwd`);
        }

        return $this->memo[$key];
    }

    private function _generateCacheKey($method, $args)
    {
        return "{$method}\t" . json_encode($args);
    }
}
