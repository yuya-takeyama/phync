<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Phync_Tests_Logger_NamedTextLoggerTest extends Phync_Tests_TestCase
{
    public function setUp()
    {
        $this->logger = new Phync_Logger_NamedTextLogger;
    }

    /**
     * @test
     */
    public function openLogFile_対象ログファイルリソースを返す()
    {
        $fileUtil = Phake::partialMock('Phync_FileUtil');
        $app   = Phake::partialMock('Phync_Application', array(
            'env'       => array(),
            'option'    => new Phync_Option(array()),
            'config'    => new Phync_Config(array('destinations' => array('localhost'))),
            'file_util' => $fileUtil,
        ));
        Phake::when($app)
            ->getLogDirectory()
            ->thenReturn(dirname(__FILE__) . '/log/');
        $event = Phake::partialMock('Phync_Event_Event', array('app' => $app));

        $this->assertTrue(is_resource($this->logger->openLogFile($event, 'anyone')));
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function openLogFile_ファイルを開けなければRuntimeExceptionを投げる()
    {
        $fileUtil = Phake::partialMock('Phync_FileUtil');
        $app   = Phake::partialMock('Phync_Application', array(
            'env'       => array(),
            'option'    => new Phync_Option(array()),
            'config'    => new Phync_Config(array('destinations' => array('localhost'))),
            'file_util' => $fileUtil,
        ));
        Phake::when($app)
            ->getLogDirectory()
            ->thenReturn('invalid_directory');
        $event = Phake::partialMock('Phync_Event_Event', array('app' => $app));
        $this->logger->openLogFile($event, 'anyone');
    }

    /**
     * @test
     */
    public function write_引数に渡した文字列をlogリソースに書き込む()
    {
        $fileUtil = Phake::partialMock('Phync_FileUtil');
        $app   = Phake::partialMock('Phync_Application', array(
            'env'       => array(),
            'option'    => new Phync_Option(array()),
            'config'    => new Phync_Config(array('destinations' => array('localhost'))),
            'file_util' => $fileUtil,
        ));
        Phake::when($app)
            ->getLogDirectory()
            ->thenReturn(dirname(__FILE__) . '/log/');

        $event = Phake::partialMock('Phync_Event_Event', array('app' => $app));
        Phake::when($event)
            ->getName()
            ->thenReturn('after_config_loading');

        // NamedTextLogger::getNameの入力リソースを差し替える
        $name = 'anyone';
        $fp = fopen('php://temp', 'r+');
        fputs($fp, $name);
        rewind($fp);
        $this->logger->getName($fp);
        fclose($fp);

        $logfile = dirname(__FILE__) . '/log/' . date('Ymd') . "-{$name}.log";
        unlink($logfile);

        $this->logger->update($event);
        $message = 'test message';
        $this->logger->write($message);
        $expect = date('Y-m-d H:i:s') . "\t{$message}" . PHP_EOL;
        $result = file_get_contents($logfile);

        $this->assertSame($expect, $result);
    }
}
