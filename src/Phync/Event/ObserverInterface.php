<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__) . '/Event.php';

interface Phync_Event_ObserverInterface
{
    public function update(Phync_Event_Event $event);
}
