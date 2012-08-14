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
     * Construcotr
     *
     * @param Phync_Event_Dispatcher $dispatcher
     */
    public function __construct(Phync_Event_Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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

    private function throwIfNotCallable($callback)
    {
        if (! is_callable($callback)) {
            throw new Phync_Exception_InvalidArgument("Callback must be callable");
        }
    }
}
