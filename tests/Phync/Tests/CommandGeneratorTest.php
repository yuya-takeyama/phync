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

class Phync_Tests_CommandGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $config = new Phync_Config(array('destinations' => array('localhost')));
        $fileUtil = Phake::partialMock('Phync_FileUtil');
        Phake::when($fileUtil)
            ->isDir('/path/to/file')
            ->thenReturn(false);
        Phake::when($fileUtil)
            ->isDir('/path/to/dir')
            ->thenReturn(true);
        Phake::when($fileUtil)
            ->getRealPath('/path/to/file')
            ->thenReturn('/path/to/file');
        Phake::when($fileUtil)
            ->getRealPath('/path/to/dir')
            ->thenReturn('/path/to/dir');
        $this->generator = new Phync_CommandGenerator($config, $fileUtil);
    }

    /**
     * @test
     */
    public function rsyncコマンドの配列を生成する()
    {
        $option = new Phync_Option(array('phync', '/path/to/file'));
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
        $option = new Phync_Option(array('phync', '/path/to/dir'));
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
        $option = new Phync_Option(array(
            'phync',
            '/path/to/file',
            '/path/to/dir'
        ));
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
        $option = new Phync_Option(array('phync', '--checksum', '/path/to/dir'));
        $this->assertEquals(
            array("rsync -avC --dry-run --checksum --delete '/path/to/dir/' 'localhost:/path/to/dir/'"),
            $this->generator->getCommands($option)
        );
    }
}
