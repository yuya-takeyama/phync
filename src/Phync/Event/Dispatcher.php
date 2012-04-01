<?php
require_once dirname(__FILE__) . '/Event.php';
require_once dirname(__FILE__) . '/ObserverInterface.php';

class Phync_Event_Dispatcher
{
    /**
     * @var array<Phync_Event_ListenerInterface>
     */
    private $listeners = array();

    private $observers = array();

    /**
     * イベントリスナーの追加.
     *
     * @param  Phync_Event_ListenerInterface $listener
     * @return void
     */
    public function on($eventName, $listener)
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = array();
        }
        $this->listeners[$eventName][] = $listener;
    }

    public function addObserver(Phync_Event_ObserverInterface $observer)
    {
        $this->observers[] = $observer;
    }

    /**
     * イベントを発火する.
     *
     * @param  string $event イベント名.
     * @param  mixed  $args  イベントリスナーに渡す引数. (可変引数)
     * @return void
     */
    public function dispatch($eventName, $event = NULL)
    {
        if (is_null($event)) {
            $event = new Phync_Event_Event;
        } else if (is_array($event)) {
            $event = new Phync_Event_Event($event);
        }
        $event->setName($eventName);
        foreach ($this->observers as $observer) {
            call_user_func(array($observer, 'update'), $event);
        }
        if (array_key_exists($eventName, $this->listeners)) {
            foreach ($this->listeners[$eventName] as $listener) {
                call_user_func($listener, $event);
            }
        }
    }
}
