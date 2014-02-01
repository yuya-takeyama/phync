<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Phync_Logger_AbstractLogger
{
    /**
     * @var resource
     */
    protected $log;
    protected static $name;

    public function openLogFile(Phync_Event_Event $event, $name)
    {
        $file  = $event->app->getLogDirectory() . DIRECTORY_SEPARATOR .
            date('Ymd') . '-' . $name . '.log';
        $log = @fopen($file, 'a');
        if ($log === false) {
            throw new RuntimeException("Failed to open log file: \"{$file}\"");
        }

        return $log;
    }

    public function write($messages)
    {
        $messages = func_get_args();
        $text = date('Y-m-d H:i:s');
        foreach ($messages as $message) {
            $text .= "\t" . $message;
        }
        $text .= PHP_EOL;
        fputs($this->log, $text);
    }

    public function __destruct()
    {
        if (is_resource($this->log)) {
            fclose($this->log);
        }
    }
}
