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
    public function getRealPath($path)
    {
        return File_Util::realPath($path);
    }

    public function isDir($path)
    {
        return is_dir($path);
    }

    public function isFile($path)
    {
        return is_file($path);
    }

    public function shellescape($arg)
    {
        return escapeshellarg($arg);
    }
}
