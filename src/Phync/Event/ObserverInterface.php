<?php
require_once dirname(__FILE__) . '/Event.php';

interface Phync_Event_ObserverInterface
{
    public function update(Phync_Event_Event $event);
}
