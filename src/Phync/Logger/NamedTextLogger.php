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
require_once dirname(__FILE__) . '/AbstractLogger.php';

class Phync_Logger_NamedTextLogger extends Phync_Logger_AbstractLogger implements Phync_Event_ObserverInterface
{
    public function update(Phync_Event_Event $event)
    {
        switch ($event->getName()) {
        case 'after_config_loading':
            if (!self::$name) $this->getName();
            $this->log = $this->openLogFile($event, self::$name);
            break;
        case 'before_command_execution':
            $this->onBeforeCommandExecution($event);
            break;
        case 'after_command_execution':
            $this->onAfterCommandExecution($event);
            break;
        }
    }

    public function getName($input = STDIN)
    {
        echo "Your name: ";
        while (true) {
            $name = fgets($input);
            if (is_string($name)) {
                $name = chop(preg_replace('/\.+/u', '.', $name));
                if ($name !== '') {
                    self::$name = $name;
                    return;
                }
            }
            echo "Invalid input.", PHP_EOL;
        }
    }

    public function onBeforeCommandExecution(Phync_Event_Event $event)
    {
        $this->write('[COMMAND]', $event->command);
    }

    public function onAfterCommandExecution(Phync_Event_Event $event)
    {
        $this->write('[STATUS]', (string)$event->status);
    }
}
