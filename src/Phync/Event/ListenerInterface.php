<?php
interface Phync_Event_ListenerInterface
{
    public function on($event, $args);
}
