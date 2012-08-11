<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__) . '/../Event/ObserverInterface.php';
require_once dirname(__FILE__) . '/../Event/Event.php';

class Phync_Logger_NamedTextLogger implements Phync_Event_ObserverInterface
{
    private $name;

    private $log;

    public function update(Phync_Event_Event $event)
    {
        switch ($event->getName()) {
        case 'after_config_loading':
            $this->name = $this->getName();
            $file  = $event->app->getLogDirectory() . DIRECTORY_SEPARATOR .
                date('Ymd') . '-' . $this->name . '.log';
            $this->log = @fopen($file, 'a');
            if ($this->log === false) {
                throw new RuntimeException("Failed to open log file: \"{$file}\"");
            }
            break;
        case 'before_command_execution':
            $this->onBeforeCommandExecution($event);
            break;
        case 'after_command_execution':
            $this->onAfterCommandExecution($event);
            break;
        }
    }

    public function getName()
    {
        echo "Your name: ";
        while (true) {
            $name = fgets(STDIN);
            if (is_string($name)) {
                $name = chop(preg_replace('/\.+/u', '.', $name));
                if ($name !== '') {
                    return $name;
                }
            }
            echo "Invalid input.", PHP_EOL;
        }
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

    public function onBeforeCommandExecution(Phync_Event_Event $event)
    {
        $this->write('[COMMAND]', $event->command);
    }

    public function onAfterCommandExecution(Phync_Event_Event $event)
    {
        $this->write('[STATUS]', (string)$event->status);
    }

    public function __destruct()
    {
        if (is_resource($this->log)) {
            fclose($this->log);
        }
    }
}
