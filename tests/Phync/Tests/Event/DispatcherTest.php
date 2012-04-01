<?php
require_once 'Phync/Event/Dispatcher.php';

class Phync_Tests_Event_DispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function dispatch_リスナーを指定した引数で呼び出す()
    {
        $listener = $this->getMock('Phync_Event_ListenerInterface');
        $listener->expects($this->once())
            ->method('on')
            ->with('event_name', array('arg1', 'arg2', 'arg3'));
        $dispatcher = new Phync_Event_Dispatcher;
        $dispatcher->addListener($listener);
        $dispatcher->dispatch('event_name', 'arg1', 'arg2', 'arg3');
    }
}
