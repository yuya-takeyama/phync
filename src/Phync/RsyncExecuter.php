<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__) . '/Exception/InvalidArgument.php';

/**
 * rsync コマンドを実行し、出力に応じてイベントを発生させる
 *
 * @author Yuya Takeyama
 */
class Phync_RsyncExecuter
{
    /**
     * @var Phync_Event_Dispatcher
     */
    private $dispatcher;

    /**
     * @var bool
     */
    private $isInFileList;

    /**
     * Construcotr
     *
     * @param array $params
     */
    public function __construct($params)
    {
        $this->dispatcher   = $params['event_dispatcher'];
        $this->fileUtil     = $params['file_util'];
        $this->isInFileList = false;
    }

    /**
     * rsync コマンドを実行する
     *
     * @param  string $command
     * @return int    終了ステータス
     */
    public function execute($command)
    {
        $fdSpecs = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $process = proc_open($command, $fdSpecs, $pipes);
        stream_set_blocking($pipes[1], 1);
        stream_set_blocking($pipes[2], 1);

        if (is_resource($process)) {
            while (feof($pipes[1]) === false || feof($pipes[2]) === false) {
                $ret = stream_select($read = array($pipes[1], $pipes[2]), $write = NULL, $except = NULL, 5);
                if ($ret === false) {
                    // Error
                } else if ($ret !== 0) {
                    foreach ($read as $sock) {
                        if ($sock === $pipes[1]) {
                            $line = fgets($sock, 4096);
                            if ($line) {
                                $this->receiveRawStdout($line);
                            }
                        } else if ($sock === $pipes[2]) {
                            $line = fgets($sock, 4096);
                            if ($line) {
                                $this->receiveRawStderr($line);
                            }
                        }
                    }
                }
            }
            fclose($pipes[1]);
            fclose($pipes[2]);
            return proc_close($process);
        }
    }

    public function receiveRawStdout($line)
    {
        $this->dispatcher->dispatch('stdout', array(
            'line' => $line,
        ));
        if ($this->isInFileList()) {
            if ($this->isUploadSymlinkLine($line, $path, $toPath)) {
                $this->dispatcher->dispatch('stdout.upload_symlink_line', array(
                    'line'    => $line,
                    'path'    => $path,
                    'to_path' => $toPath,
                ));
            } else if ($this->isUploadDirLine($line, $path)) {
                $this->dispatcher->dispatch('stdout.upload_dir_line', array(
                    'line' => $line,
                    'path' => $path,
                ));
            } else if ($this->isUploadFileLine($line, $path)) {
                $this->dispatcher->dispatch('stdout.upload_file_line', array(
                    'line' => $line,
                    'path' => $path,
                ));
            } else if ($this->isCreateDirLine($line, $path)) {
                $this->dispatcher->dispatch('stdout.create_dir_line', array(
                    'line' => $line,
                    'path' => $path,
                ));
            } else if ($this->isDeleteFileLine($line, $path)) {
                $this->dispatcher->dispatch('stdout.delete_file_line', array(
                    'line' => $line,
                    'path' => $path,
                ));
            } else if ($this->isDeleteDirLine($line, $path)) {
                $this->dispatcher->dispatch('stdout.delete_dir_line', array(
                    'line' => $line,
                    'path' => $path,
                ));
            } else {
                $this->dispatcher->dispatch('stdout.normal_line', array(
                    'line' => $line,
                ));
            }
        } else {
            $this->dispatcher->dispatch('stdout.normal_line', array(
                'line' => $line,
            ));
        }
        if (! $this->isInFileList() && preg_match('/^building file list \.\.\./', $line)) {
            $this->isInFileList = true;
        }
    }

    public function receiveRawStderr($line)
    {
        $this->dispatcher->dispatch('stderr', array(
            'line' => $line,
        ));
    }

    public function onStdout($callback)
    {
        $this->throwIfNotCallable($callback);
        $this->dispatcher->on('stdout', $callback);
    }

    public function onStderr($callback)
    {
        $this->throwIfNotCallable($callback);
        $this->dispatcher->on('stderr', $callback);
    }

    public function onNormalLine($callback)
    {
        $this->throwIfNotCallable($callback);
        $this->dispatcher->on('stdout.normal_line', $callback);
    }

    public function onUploadDirLine($callback)
    {
        $this->throwIfNotCallable($callback);
        $this->dispatcher->on('stdout.upload_dir_line', $callback);
    }

    public function onUploadFileLine($callback)
    {
        $this->throwIfNotCallable($callback);
        $this->dispatcher->on('stdout.upload_file_line', $callback);
    }

    public function onCreateDirLine($callback)
    {
        $this->throwIfNotCallable($callback);
        $this->dispatcher->on('stdout.create_dir_line', $callback);
    }

    public function onUploadSymlinkLine($callback)
    {
        $this->throwIfNotCallable($callback);
        $this->dispatcher->on('stdout.upload_symlink_line', $callback);
    }

    public function onDeleteFileLine($callback)
    {
        $this->throwIfNotCallable($callback);
        $this->dispatcher->on('stdout.delete_file_line', $callback);
    }

    public function onDeleteDirLine($callback)
    {
        $this->throwIfNotCallable($callback);
        $this->dispatcher->on('stdout.delete_dir_line', $callback);
    }

    private function throwIfNotCallable($callback)
    {
        if (! is_callable($callback)) {
            throw new Phync_Exception_InvalidArgument("Callback must be callable");
        }
    }

    /**
     * ファイル一覧の出力中であるか
     *
     * @return bool
     */
    public function isInFileList()
    {
        return $this->isInFileList;
    }

    public function setInFileList($flag = true)
    {
        $this->isInFileList = $flag;
    }

    public function isUploadDirLine($line, &$path)
    {
        if ($this->isInFileList()) {
            $parsedPath = chop($line);
            if ($this->fileUtil->isDir($parsedPath)) {
                $path = $parsedPath;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isUploadFileLine($line, &$path)
    {
        if ($this->isInFileList()) {
            $parsedPath = chop($line);
            if ($this->fileUtil->isFile($parsedPath)) {
                $path = $parsedPath;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isCreateDirLine($line, &$path)
    {
        if ($this->isInFileList()) {
            if (preg_match('/^created directory (.*)\n$/', $line, $matches)) {
                $parsedPath = $matches[1];
                if ($this->fileUtil->isDir($parsedPath)) {
                    $path = $parsedPath;
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isUploadSymlinkLine($line, &$path, &$toPath)
    {
        if ($this->isInFileList()) {
            if (preg_match('/^(.*) -> (.*)\n$/', $line, $matches)) {
                $parsedPath   = $matches[1];
                $parsedToPath = $matches[2];
                if ($this->fileUtil->isLink($parsedPath)) {
                    $path   = $parsedPath;
                    $toPath = $parsedToPath;
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isDeleteFileLine($line, &$path)
    {
        if ($this->isInFileList()) {
            if (preg_match('/^deleting (.*[^\/])\n$/', $line, $matches)) {
                $parsedPath = $matches[1];
                $path = $parsedPath;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isDeleteDirLine($line, &$path)
    {
        if ($this->isInFileList()) {
            if (preg_match('/^deleting (.*\/)\n$/', $line, $matches)) {
                $parsedPath = $matches[1];
                $path = $parsedPath;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
