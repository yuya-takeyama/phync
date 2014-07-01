<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Phync_Tests_ApplicationTest extends Phync_Tests_TestCase
{
    /**
     * @test
     */
    public function getLogDirectory_ログディレクトリの設定が無ければデフォルトのディレクトリ()
    {
        $fileUtil = Phake::partialMock('Phync_FileUtil');
        Phake::when($fileUtil)
            ->getRealPath('.phync/log')
            ->thenReturn('/working-dir/.phync/log');
        $app = new Phync_Application(array(
            'env'       => array(),
            'option'    => new Phync_Option(array()),
            'config'    => new Phync_Config(array('destinations' => array('localhost'))),
            'file_util' => $fileUtil,
        ));
        $this->assertEquals('/working-dir/.phync/log', $app->getLogDirectory());
    }

    /**
     * @test
     */
    public function getLogDirectory_ログディレクトリの設定があればそのディレクトリ()
    {
        $logDir = '/path/to/log/directory';
        $app = new Phync_Application(array(
            'env'       => array(),
            'option'    => new Phync_Option(array()),
            'config'    => new Phync_Config(array(
                'destinations'  => array('localhost'),
                'log_directory' => $logDir,
            )),
            'file_util' => new Phync_FileUtil,
        ));
        $this->assertEquals($logDir, $app->getLogDirectory());
    }
}
