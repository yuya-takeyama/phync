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
     * @dataProvider provideCwd
     */
    public function コマンドライン引数がカレントディレクトリならincludeを追加しない($cwd)
    {
        $option = $this->createOption($cwd);
        $this->assertEquals(
            array("rsync -avC --dry-run --delete '/working-dir/' 'localhost:/working-dir/'"),
            $this->generator->getCommands($option)
        );
    }

    public function provideCwd()
    {
        return array(
            array('.'),
            array('./'),
            array('/working-dir'),
        );
    }

    /**
     * @test
     * @dataProvider provideFilePath
     */
    public function 特定のファイルだけをアップするコマンドを生成する($file)
    {
        $option = $this->createOption($file);
        $this->assertEquals(
            array("rsync -avC --dry-run --delete '/working-dir/' 'localhost:/working-dir/' --include '/file' --exclude '*'"),
            $this->generator->getCommands($option)
        );
    }

    public function provideFilePath()
    {
        return array(
            array('file'),
            array('file/'),
            array('./file'),
            array('./file/'),
            array('/working-dir/file'),
            array('/working-dir/file/'),
        );
    }

    /**
     * @test
     * @dataProvider provideDirPath
     */
    public function 特定のディレクトリ全体をアップするコマンドを生成する($dir)
    {
        $option = $this->createOption($dir);
        $this->assertEquals(
            array("rsync -avC --dry-run --delete '/working-dir/' 'localhost:/working-dir/' --include '/dir/' --include '/dir/*' --include '/dir/**/*' --exclude '*'"),
            $this->generator->getCommands($option)
        );
    }

    public function provideDirPath()
    {
        return array(
            array('dir'),
            array('dir/'),
            array('./dir'),
            array('./dir/'),
            array('/working-dir/dir'),
            array('/working-dir/dir/'),
        );
    }

    /**
     * @test
     */
    public function 深い階層のファイルを指定するとき()
    {
        $option = $this->createOption('dir/file');
        $this->assertEquals(
            array("rsync -avC --dry-run --delete '/working-dir/' 'localhost:/working-dir/' --include '/dir/file' --include '/dir/' --exclude '*'"),
            $this->generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function 複数のファイルが指定されているとき()
    {
        $option = $this->createOption('file', 'another_file');
        $this->assertEquals(
            array("rsync -avC --dry-run --delete '/working-dir/' 'localhost:/working-dir/' --include '/file' --include '/another_file' --exclude '*'"),
            $this->generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function ファイルとディレクトリが混ざっているとき()
    {
        $option = $this->createOption('file', 'another_file', 'dir');
        $this->assertEquals(
            array("rsync -avC --dry-run --delete '/working-dir/' 'localhost:/working-dir/' --include '/file' --include '/another_file' --include '/dir/' --include '/dir/*' --include '/dir/**/*' --exclude '*'"),
            $this->generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function checksumオプションがあればチェックサムを行う()
    {
        $option = $this->createOption('--checksum');
        $this->assertEquals(
            array("rsync -avC --dry-run --checksum --delete '/working-dir/' 'localhost:/working-dir/'"),
            $this->generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function デフォルトでチェックサムを行う設定ならチェックサムを行う()
    {
        $config    = $this->createDefaultChecksumConfig();
        $option    = $this->createOption();
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -avC --dry-run --checksum --delete '/working-dir/' 'localhost:/working-dir/'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function デフォルトでチェックサムを行う設定でchecksumオプションがあればチェックサムを行う()
    {
        $config    = $this->createDefaultChecksumConfig();
        $option    = $this->createOption('--checksum');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -avC --dry-run --checksum --delete '/working-dir/' 'localhost:/working-dir/'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function デフォルトでチェックサムを行う設定でno_checksumオプションがあればチェックサムを行わない()
    {
        $config    = $this->createDefaultChecksumConfig();
        $option    = $this->createOption('--no-checksum');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -avC --dry-run --delete '/working-dir/' 'localhost:/working-dir/'"),
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
            ->isDir('/working-dir/file')
            ->thenReturn(false);
        Phake::when($fileUtil)
            ->isDir('/working-dir/another_file')
            ->thenReturn(false);
        Phake::when($fileUtil)
            ->isDir('/working-dir/dir')
            ->thenReturn(true);
        Phake::when($fileUtil)
            ->isDir('/working-dir/dir/file')
            ->thenReturn(false);
        Phake::when($fileUtil)
            ->getRealPath('.')
            ->thenReturn('/working-dir');
        Phake::when($fileUtil)
            ->getRealPath('./')
            ->thenReturn('/working-dir');
        Phake::when($fileUtil)
            ->getRealPath('file')
            ->thenReturn('/working-dir/file');
        Phake::when($fileUtil)
            ->getRealPath('file/')
            ->thenReturn('/working-dir/file');
        Phake::when($fileUtil)
            ->getRealPath('./file')
            ->thenReturn('/working-dir/file');
        Phake::when($fileUtil)
            ->getRealPath('./file/')
            ->thenReturn('/working-dir/file');
        Phake::when($fileUtil)
            ->getRealPath('dir')
            ->thenReturn('/working-dir/dir');
        Phake::when($fileUtil)
            ->getRealPath('dir/')
            ->thenReturn('/working-dir/dir');
        Phake::when($fileUtil)
            ->getRealPath('./dir')
            ->thenReturn('/working-dir/dir');
        Phake::when($fileUtil)
            ->getRealPath('./dir/')
            ->thenReturn('/working-dir/dir');
        Phake::when($fileUtil)
            ->getRealPath('dir/file')
            ->thenReturn('/working-dir/dir/file');
        Phake::when($fileUtil)
            ->getRealPath('another_file')
            ->thenReturn('/working-dir/another_file');
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