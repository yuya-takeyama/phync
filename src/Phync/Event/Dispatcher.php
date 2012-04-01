<?php
require_once dirname(__FILE__) . '/ListenerInterface.php';

class Phync_Event_Dispatcher
{
    /**
     * @var array<Phync_Event_ListenerInterface>
     */
    private $listeners = array();

    /**
     * イベントリスナーの追加.
     *
     * @param  Phync_Event_ListenerInterface $listener
     * @return void
     */
    public function addListener(Phync_Event_ListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * イベントを発火する.
     *
     * @param  string $event イベント名.
     * @param  mixed  $args  イベントリスナーに渡す引数. (可変引数)
     * @return void
     */
    public function dispatch($event)
    {
        $args = func_get_args();
        $event = array_shift($args);
        foreach ($this->listeners as $listener) {
            $listener->on($event, $args);
        }
    }
}
