<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Phync_Tests_Logger_FileDiffLoggerTest extends Phync_Tests_TestCase
{
    public function setUp()
    {
        $this->logger = new Phync_Logger_FileDiffLogger;
    }

    /**
     * @test
     */
    public function extractFileList_dryrun結果出力からファイル一覧のみを抽出()
    {
        $message = <<<__EOS__
building file list ... done
./
hello.php
phynctest/
phynctest/hello.php
phynctest/test.txt
phynctest/ticket01.gif
phynctest/directory/
phynctest/directory/hello.link.php -> hello.php
phynctest/directory/directory2/
phynctest/directory/test.txt

sent 548 bytes  received 80 bytes  1256.00 bytes/sec
total size is 8461  speedup is 13.47
__EOS__;

        $expect = array(
            'hello.php',
            'phynctest/hello.php',
            'phynctest/test.txt',
            'phynctest/ticket01.gif',
            'phynctest/directory/hello.link.php',
            'phynctest/directory/test.txt',
        );
        $this->assertSame($expect, $this->logger->extractFileList($message));
    }

    /**
     * @test
     */
    public function isEnabled_デフォルトはfalse()
    {
        $fileUtil = Phake::partialMock('Phync_FileUtil');
        $option = Phake::partialMock('Phync_Option', array());
        Phake::when($option)
            ->isFileDiff()
            ->thenReturn(false);

        $app = Phake::partialMock('Phync_Application', array(
            'env'       => array(),
            'option'    => $option,
            'config'    => new Phync_Config(array('destinations' => array('localhost'))),
            'file_util' => $fileUtil,
        ));

        $event = Phake::partialMock('Phync_Event_Event', array('app' => $app));
        $this->assertFalse($this->logger->isEnabled($event));
    }

    /**
     * @test
     */
    public function isEnabled_設定ファイルかコマンドラインオプションで設定されていればtrue()
    {
        // 設定ファイル
        $fileUtil = Phake::partialMock('Phync_FileUtil');
        $option = Phake::partialMock('Phync_Option', array());
        Phake::when($option)
            ->isFileDiff()
            ->thenReturn(true);

        $app = Phake::partialMock('Phync_Application', array(
            'env'       => array(),
            'option'    => $option,
            'config'    => new Phync_Config(array('destinations' => array('localhost'))),
            'file_util' => $fileUtil,
        ));

        $event = Phake::partialMock('Phync_Event_Event', array('app' => $app));
        $this->assertTrue($this->logger->isEnabled($event));

        // コマンドラインオプション
        $option = Phake::partialMock('Phync_Option', array());
        Phake::when($option)
            ->isFileDiff()
            ->thenReturn(false);

        $app = Phake::partialMock('Phync_Application', array(
            'env'       => array(),
            'option'    => $option,
            'config'    => new Phync_Config(
                array(
                    'destinations'  => array('localhost'),
                    'file_diff'     => true,
                )
            ),
            'file_util' => $fileUtil,
        ));

        $event = Phake::partialMock('Phync_Event_Event', array('app' => $app));
        $this->assertTrue($this->logger->isEnabled($event));
    }
}
