<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'Phync/Application.php';

class Phync_Tests_ApplicationTest extends Phync_Tests_TestCase
{
    /**
     * @test
     */
    public function getLogDirectory()
    {
        $app = new Phync_Application(array('phync'), array('HOME' => '/home/phync'));
        $this->assertEquals('', $app->getLogDirectory());
    }
}
