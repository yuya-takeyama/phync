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
    /**
     * Phync_FileUtil にディレクトリとして扱わせるパス
     *
     * @var array
     */
    private static $mockDirs = array(
        '/working-dir',
        '/working-dir/dir',
    );

    /**
     * Phync_FileUtil にファイルとして扱わせるパス
     *
     * @var array
     */
    private static $mockFiles = array(
        '/working-dir/file',
        '/working-dir/another_file',
        '/working-dir//dir/file',
    );

    /**
     * Phync_FileUtil の getRealPath() への入力とその返り値
     *
     * @var array
     */
    private static $mockRealPaths = array(
        '.'            => '/working-dir',
        './'           => '/working-dir',
        'file'         => '/working-dir/file',
        'file/'        => '/working-dir/file',
        './file'       => '/working-dir/file',
        './file/'      => '/working-dir/file',
        'dir'          => '/working-dir/dir',
        'dir/'         => '/working-dir/dir',
        './dir'        => '/working-dir/dir',
        './dir/'       => '/working-dir/dir',
        'dir/file'     => '/working-dir/dir/file',
        'another_file' => '/working-dir/another_file',
    );

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
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/working-dir/'"),
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
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/working-dir/'"),
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
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/working-dir/' --include '/file' --exclude '*'"),
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
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/working-dir/' --include '/dir/' --include '/dir/*' --include '/dir/**/*' --exclude '*'"),
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
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/working-dir/' --include '/dir/file' --include '/dir/' --exclude '*'"),
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
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/working-dir/' --include '/file' --include '/another_file' --exclude '*'"),
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
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/working-dir/' --include '/file' --include '/another_file' --include '/dir/' --include '/dir/*' --include '/dir/**/*' --exclude '*'"),
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
            array("rsync -av --dry-run --checksum --delete '/working-dir/' 'localhost:/working-dir/'"),
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
            array("rsync -av --dry-run --checksum --delete '/working-dir/' 'localhost:/working-dir/'"),
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
            array("rsync -av --dry-run --checksum --delete '/working-dir/' 'localhost:/working-dir/'"),
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
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/working-dir/'"),
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
        foreach (self::$mockDirs as $dir) {
            Phake::when($fileUtil)
                ->isDir($dir)
                ->thenReturn(true);
        }
        foreach (self::$mockFiles as $file) {
            Phake::when($fileUtil)
                ->isDir($file)
                ->thenReturn(false);
        }
        foreach (self::$mockRealPaths as $relative => $real) {
            Phake::when($fileUtil)
                ->getRealPath($relative)
                ->thenReturn($real);
        }
        Phake::when($fileUtil)
            ->getCwd()
            ->thenReturn('/working-dir');
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
