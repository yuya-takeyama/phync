<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'Phync/CommandGenerator.php';
require_once 'Phync/Config.php';
require_once 'Phync/Option.php';
require_once 'Phync/FileUtil.php';

class Phync_Tests_CommandGeneratorTest extends Phync_Tests_TestCase
{
    public function setUp()
    {
        $config = new Phync_Config(array('destinations' => array('localhost')));
        $this->generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
    }

    /**
     * @test
     */
    public function コマンドライン引数が無ければカレントディレクトリをドライランでrsyncする()
    {
        $option = $this->createOption();
        $this->assertEquals(
            array("rsync -avC --dry-run --delete '/working-dir/' 'localhost:/working-dir/'"),
            $this->generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function rsyncコマンドの配列を生成する()
    {
        $this->markTestIncomplete();
        $option = $this->createOption('/path/to/file');
        $this->assertEquals(
            array("rsync -avC --dry-run --delete '/path/to/file' 'localhost:/path/to/file'"),
            $this->generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function ディレクトリを指定したときは末尾にスラッシュが付く()
    {
        $this->markTestIncomplete();
        $option = $this->createOption('/path/to/dir');
        $this->assertEquals(
            array("rsync -avC --dry-run --delete '/path/to/dir/' 'localhost:/path/to/dir/'"),
            $this->generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function 複数のファイルが指定されていれば複数のコマンドを生成する()
    {
        $this->markTestIncomplete();
        $option = $this->createOption('/path/to/file', '/path/to/dir');
        $this->assertEquals(
            array(
                "rsync -avC --dry-run --delete '/path/to/file' 'localhost:/path/to/file'",
                "rsync -avC --dry-run --delete '/path/to/dir/' 'localhost:/path/to/dir/'",
            ),
            $this->generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function checksumオプションがあればチェックサムを行う()
    {
        $this->markTestIncomplete();
        $option = $this->createOption('--checksum', '/path/to/dir');
        $this->assertEquals(
            array("rsync -avC --dry-run --checksum --delete '/path/to/dir/' 'localhost:/path/to/dir/'"),
            $this->generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function デフォルトでチェックサムを行う設定ならチェックサムを行う()
    {
        $this->markTestIncomplete();
        $config    = $this->createDefaultChecksumConfig();
        $option    = $this->createOption('/path/to/dir');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -avC --dry-run --checksum --delete '/path/to/dir/' 'localhost:/path/to/dir/'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function デフォルトでチェックサムを行う設定でchecksumオプションがあればチェックサムを行う()
    {
        $this->markTestIncomplete();
        $config    = $this->createDefaultChecksumConfig();
        $option    = $this->createOption('--checksum', '/path/to/dir');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -avC --dry-run --checksum --delete '/path/to/dir/' 'localhost:/path/to/dir/'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function デフォルトでチェックサムを行う設定でno_checksumオプションがあればチェックサムを行わない()
    {
        $this->markTestIncomplete();
        $config    = $this->createDefaultChecksumConfig();
        $option    = $this->createOption('--no-checksum', '/path/to/dir');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -avC --dry-run --delete '/path/to/dir/' 'localhost:/path/to/dir/'"),
            $generator->getCommands($option)
        );
    }

    /**
     * Phync_FileUtil のモックオブジェクトを生成する
     *
     * @return Phync_FileUtil
     */
    private function createMockFileUtil()
    {
        $fileUtil = Phake::partialMock('Phync_FileUtil');
        Phake::when($fileUtil)
            ->getCwd()
            ->thenReturn('/working-dir');
        Phake::when($fileUtil)
            ->isDir('/working-dir')
            ->thenReturn(true);
        Phake::when($fileUtil)
            ->isDir('/working-dir/path/to/file')
            ->thenReturn(false);
        Phake::when($fileUtil)
            ->isDir('/working-dir/path/to/dir')
            ->thenReturn(true);
        Phake::when($fileUtil)
            ->getRealPath('/working-dir/path/to/file')
            ->thenReturn('/working-dir/path/to/file');
        Phake::when($fileUtil)
            ->getRealPath('/working-dir/path/to/dir')
            ->thenReturn('/working-dir/path/to/dir');
        return $fileUtil;
    }

    /**
     * デフォルトでチェックサムを行う設定の Phync_Config オブジェクトを生成する
     *
     * @return Phync_Config
     */
    private function createDefaultChecksumConfig()
    {
        $config = Phake::partialMock('Phync_Config', array(
            'destinations' => array('localhost')
        ));
        Phake::when($config)->isDefaultChecksum()->thenReturn(true);
        return $config;
    }
}
