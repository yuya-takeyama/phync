<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
        '/working-dir/dir/next-dir',
        '/working-dir/dir/next-dir/more-dir',
    );

    /**
     * Phync_FileUtil にファイルとして扱わせるパス
     *
     * @var array
     */
    private static $mockFiles = array(
        '/working-dir/file',
        '/working-dir/another_file',
        '/working-dir/dir/file',
    );

    /**
     * Phync_FileUtil にディレクトリへのシンボリックリンクとして扱わせるパス
     *
     * @var array
     */
    private static $mockSymlinksToDir = array(
        '/working-dir/path_to/symlink_to_dir',
    );

    /**
     * Phync_FileUtil の getRealPath() への入力とその返り値
     *
     * @var array
     */
    private static $mockRealPaths = array(
        '.'                     => '/working-dir',
        './'                    => '/working-dir',
        'file'                  => '/working-dir/file',
        'file/'                 => '/working-dir/file',
        './file'                => '/working-dir/file',
        './file/'               => '/working-dir/file',
        'dir'                   => '/working-dir/dir',
        'dir/'                  => '/working-dir/dir',
        './dir'                 => '/working-dir/dir',
        './dir/'                => '/working-dir/dir',
        'dir/file'              => '/working-dir/dir/file',
        'another_file'          => '/working-dir/another_file',
        'dir/next-dir'          => '/working-dir/dir/next-dir',
        'dir/next-dir/more-dir' => '/working-dir/dir/next-dir/more-dir',
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
     * @dataProvider provideDeepDirPath
     */
    public function 深い階層のディレクトリを指定するとき($dir, $command)
    {
        $option = $this->createOption($dir);
        $this->assertEquals(
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/working-dir/' {$command} --exclude '*'"),
            $this->generator->getCommands($option)
        );
    }

    public function provideDeepDirPath()
    {
        return array(
            array(
                'dir',
                "--include '/dir/' --include '/dir/*' --include '/dir/**/*'"
            ),
            array(
                'dir/next-dir',
                "--include '/dir/' --include '/dir/next-dir/' --include '/dir/next-dir/*' --include '/dir/next-dir/**/*'"
            ),
            array(
                'dir/next-dir/more-dir',
                "--include '/dir/' --include '/dir/next-dir/' --include '/dir/next-dir/more-dir/' --include '/dir/next-dir/more-dir/*' --include '/dir/next-dir/more-dir/**/*'"
            ),
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
    public function コマンドライン引数が無くてリモートの対象ディレクトリが指定されているとき()
    {
        $option    = $this->createOption();
        $config    = $this->createConfigWithRemoteTargetDir('/target-dir');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/target-dir/'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     * @dataProvider provideCwd
     */
    public function コマンドライン引数がカレントディレクトリでリモート対象ディレクトリが指定されているとき($cwd)
    {
        $option    = $this->createOption();
        $config    = $this->createConfigWithRemoteTargetDir('/target-dir');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/target-dir/'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     * @dataProvider provideFilePath
     */
    public function リモート対象ディレクトリが指定されていて特定のファイルだけをアップするとき($file)
    {
        $option    = $this->createOption($file);
        $config    = $this->createConfigWithRemoteTargetDir('/target-dir');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/target-dir/' --include '/file' --exclude '*'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     * @dataProvider provideDirPath
     */
    public function リモート対象ディレクトリが指定されていて特定のディレクトリ全体をアップするとき($dir)
    {
        $option    = $this->createOption($dir);
        $config    = $this->createConfigWithRemoteTargetDir('/target-dir');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/target-dir/' --include '/dir/' --include '/dir/*' --include '/dir/**/*' --exclude '*'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function リモート対象ディレクトリが指定されていて深い階層のファイルを指定するとき()
    {
        $option    = $this->createOption('dir/file');
        $config    = $this->createConfigWithRemoteTargetDir('/target-dir');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/target-dir/' --include '/dir/file' --include '/dir/' --exclude '*'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     * @dataProvider provideDeepDirPath
     */
    public function リモート対象ディレクトリが指定されていて深い階層のディレクトリを指定するとき($dir, $command)
    {
        $option    = $this->createOption($dir);
        $config    = $this->createConfigWithRemoteTargetDir('/target-dir');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/target-dir/' {$command} --exclude '*'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function リモート対象ディレクトリが指定されていて複数のファイルが指定されているとき()
    {
        $option    = $this->createOption('file', 'another_file');
        $config    = $this->createConfigWithRemoteTargetDir('/target-dir');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/target-dir/' --include '/file' --include '/another_file' --exclude '*'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function リモート対象ディレクトリが指定されていてファイルとディレクトリが混ざっているとき()
    {
        $option    = $this->createOption('file', 'another_file', 'dir');
        $config    = $this->createConfigWithRemoteTargetDir('/target-dir');
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/target-dir/' --include '/file' --include '/another_file' --include '/dir/' --include '/dir/*' --include '/dir/**/*' --exclude '*'"),
            $generator->getCommands($option)
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
     * @test
     */
    public function SSHユーザ名が設定されているとき()
    {
        $config = $this->createConfigWithSshUserName('testuser');
        $option    = $this->createOption();
        $generator = new Phync_CommandGenerator($config, $this->createMockFileUtil());
        $this->assertEquals(
            array("rsync -av --dry-run --delete '/working-dir/' 'testuser@localhost:/working-dir/'"),
            $generator->getCommands($option)
        );
    }

    /**
     * @test
     */
    public function ディレクトリを指すシンボリックリンクはファイルとして扱う()
    {
        $option = $this->createOption('/working-dir/path_to/symlink_to_dir');
        $this->assertEquals(
            array("rsync -av --dry-run --delete '/working-dir/' 'localhost:/working-dir/' --include '/path_to/symlink_to_dir' --include '/path_to/' --exclude '*'"),
            $this->generator->getCommands($option)
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

        foreach (self::$mockSymlinksToDir as $link) {
            Phake::when($fileUtil)
                ->isDir($link)
                ->thenReturn(true);
            Phake::when($fileUtil)
                ->isLink($link)
                ->thenReturn(true);
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

    /**
     * SSH ユーザ名の設定を持つ Phync_Config オブジェクトを生成する
     *
     * @return Phync_Config
     */
    private function createConfigWithSshUserName($sshUserName)
    {
        $config = Phake::partialMock('Phync_Config', array(
            'destinations' => array('localhost')
        ));
        Phake::when($config)->getSshUserName()->thenReturn($sshUserName);
        return $config;
    }

    /**
     * リモートの対象ディレクトリの設定を持つ Phync_Config オブジェクトを生成する
     *
     * @return Phync_Config
     */
    private function createConfigWithRemoteTargetDir($remoteTargetDir)
    {
        $config = Phake::partialMock('Phync_Config', array(
            'destinations' => array('localhost')
        ));
        Phake::when($config)->getRemoteTargetDir()->thenReturn($remoteTargetDir);
        return $config;
    }
}
