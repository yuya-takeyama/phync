<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'Phync/Event/Dispatcher.php';
require_once 'Phync/Event/Event.php';

class Phync_Tests_Event_DispatcherTest extends Phync_Tests_TestCase
{
    /**
     * @test
     */
    public function dispatch_リスナーを指定した引数で呼び出す()
    {
        $event = new Phync_Event_Event;
        $listener = $this->getMock('stdClass', array('call'));
        $listener->expects($this->once())
            ->method('call')
            ->with($event);
        $dispatcher = new Phync_Event_Dispatcher;
        $dispatcher->on('foo_event', array($listener, 'call'));
        $dispatcher->dispatch('foo_event', $event);
    }
}
