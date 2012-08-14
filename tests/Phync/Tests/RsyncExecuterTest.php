<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'Phync/RsyncExecuter.php';
require_once 'Phync/Event/Dispatcher.php';
require_once 'Phync/FileUtil.php';

class Phync_Tests_RsyncExecuterTest extends Phync_Tests_TestCase
{
    private $executer;

    public function setUp()
    {
        $this->executer = new Phync_RsyncExecuter(array(
            'event_dispatcher' => new Phync_Event_Dispatcher,
            'file_util'        => new Phync_FileUtil,
        ));
    }

    /**
     * @test
     */
    public function isInFileList_デフォルトではfalse()
    {
        $this->assertFalse($this->executer->isInFileList());
    }

    /**
     * @test
     */
    public function isInFileList_ファイルリスト一覧の構築後であればtrue()
    {
        $this->executer->receiveRawStdout("building file list ... done\n");
        $this->assertTrue($this->executer->isInFileList());
    }
}
