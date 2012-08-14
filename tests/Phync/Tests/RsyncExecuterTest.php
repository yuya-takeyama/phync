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
        $fileUtil = Phake::partialMock('Phync_FileUtil');
        Phake::when($fileUtil)
            ->isDir('path/to/dir/')
            ->thenReturn(true);
        Phake::when($fileUtil)
            ->isDir('path/to/file')
            ->thenReturn(false);
        Phake::when($fileUtil)
            ->isFile('path/to/dir/')
            ->thenReturn(false);
        Phake::when($fileUtil)
            ->isFile('path/to/file')
            ->thenReturn(true);

        $this->executer = new Phync_RsyncExecuter(array(
            'event_dispatcher' => new Phync_Event_Dispatcher,
            'file_util'        => $fileUtil,
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

    /**
     * @test
     */
    public function setInFileList_isInFileListをtrueにする()
    {
        $this->executer->setInFileList();
        $this->assertTrue($this->executer->isInFileList());
    }

    /**
     * @test
     */
    public function isUploadDirLine_ディレクトリの同期を示す行であればtrue()
    {
        $this->executer->setInFileList();
        $this->assertTrue($this->executer->isUploadDirLine("path/to/dir/\n", $path));
    }

    /**
     * @test
     */
    public function isUploadDirLine_ディレクトリの同期を示す行であれば変数pathにパス名をセットする()
    {
        $this->executer->setInFileList();
        $this->executer->isUploadDirLine("path/to/dir/\n", $path);
        $this->assertEquals('path/to/dir/', $path);
    }

    /**
     * @test
     */
    public function isUploadDirLine_ディレクトリの同期を示す行でなければfalse()
    {
        $this->executer->setInFileList();
        $this->assertFalse($this->executer->isUploadDirLine("path/to/file\n", $path));
    }

    /**
     * @test
     */
    public function isUploadDirLine_ディレクトリの同期を示す行でなければ変数pathはNULL()
    {
        $this->executer->setInFileList();
        $this->executer->isUploadDirLine("path/to/file\n", $path);
        $this->assertNull($path);
    }

    /**
     * @test
     */
    public function isUploadFileLine_ファイルのアップロードを示す行であればtrue()
    {
        $this->executer->setInFileList();
        $this->assertTrue($this->executer->isUploadFileLine("path/to/file\n", $path));
    }

    /**
     * @test
     */
    public function isUploadFileLine_ファイルのアップロードを示す行であれば変数pathにパス名をセットする()
    {
        $this->executer->setInFileList();
        $this->executer->isUploadFileLine("path/to/file\n", $path);
        $this->assertEquals('path/to/file', $path);
    }

    /**
     * @test
     */
    public function isUploadFileLine_ファイルのアップロードを示す行でなければfalse()
    {
        $this->executer->setInFileList();
        $this->assertFalse($this->executer->isUploadFileLine("path/to/dir/\n", $path));
    }

    /**
     * @test
     */
    public function isUploadFileLine_ファイルのアップロードを示す行でなければ変数pathはNULL()
    {
        $this->executer->setInFileList();
        $this->executer->isUploadFileLine("path/to/dir/\n", $path);
        $this->assertNull($path);
    }

}
