<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Phync_Tests_OptionTest extends Phync_Tests_TestCase
{
    /**
     * @test
     */
    public function isDryRun_デフォルトではtrue()
    {
        $option = $this->createOption();
        $this->assertTrue($option->isDryRun());
    }

    /**
     * @test
     */
    public function isDryRun_setExecuteでtrueを指定したらfalseになる()
    {
        $option = $this->createOption();
        $option->setExecute(true);
        $this->assertFalse($option->isDryRun());
    }

    /**
     * @test
     */
    public function isDryRun_setExecuteでfalseを指定したらtrueになる()
    {
        $option = $this->createOption();
        $option->setExecute(false);
        $this->assertTrue($option->isDryRun());
    }

    /**
     * @test
     */
    public function isDryRun_executeオプションが指定されていればfalse()
    {
        $option = $this->createOption('--execute');
        $this->assertFalse($option->isDryRun());
    }

    /**
     * @test
     */
    public function isChecksum_デフォルトではfalse()
    {
        $option = $this->createOption();
        $this->assertFalse($option->isChecksum());
    }

    /**
     * @test
     */
    public function isChecksum_setChecksumでtrueを指定したらtrueになる()
    {
        $option = $this->createOption();
        $option->setChecksum(true);
        $this->assertTrue($option->isChecksum());
    }

    /**
     * @test
     */
    public function isChecksum_setChecksumでfalseを指定したらfalseになる()
    {
        $option = $this->createOption();
        $option->setChecksum(false);
        $this->assertFalse($option->isChecksum());
    }

    /**
     * @test
     */
    public function isChecksum_checksumオプションが指定されていればtrue()
    {  
        $option = $this->createOption('--checksum');
        $this->assertTrue($option->isChecksum());
    }

    /**
     * @test
     */
    public function isChecksumSet_デフォルトはfalse()
    {
        $option = $this->createOption();
        $this->assertFalse($option->isChecksumSet());
    }

    /**
     * @test
     */
    public function isChecksumSet_checksumがtrueならtrue()
    {
        $option = $this->createOption();
        $option->setChecksum(true);
        $this->assertTrue($option->isChecksumSet());
    }

    /**
     * @test
     */
    public function isChecksumSet_checksumがfalseならtrue()
    {
        $option = $this->createOption();
        $option->setChecksum(false);
        $this->assertTrue($option->isChecksumSet());
    }

    /**
     * @test
     */
    public function getFiles_指定されたファイル名を配列で取得する()
    {
        $option = $this->createOption('foo.txt', 'bar.txt');
        $this->assertEquals(array('foo.txt', 'bar.txt'), $option->getFiles());
    }

    /**
     * @test
     */
    public function getFiles_ファイル名が指定されていなければ空の配列()
    {
        $option = $this->createOption();
        $this->assertEquals(array(), $option->getFiles());
    }

    /**
     * @test
     */
    public function getConfigFile_デフォルトの設定ファイルのとき()
    {
        $option = $this->createOption();
        $this->assertEquals('.phync/config.php', $option->getConfigFile());
    }

    /**
     * @test
     */
    public function getConfigFile_configオプションが指定されているとき()
    {
        $file   = 'another-config-file.php';
        $option = $this->createOption("--config={$file}");
        $this->assertEquals($file, $option->getConfigFile());
    }

    /**
     * @test
     * @expectedException Phync_Exception_InvalidCommandLineOption
     */
    public function getConfigFile_configオプションに引数が無いとき()
    {
        $file   = 'another-config-file.php';
        $option = $this->createOption('--config', $file);
        $this->assertEquals($file, $option->getConfigFile());
    }

    /**
     * @test
     */
    public function isFileDiff_デフォルトはfalse()
    {
        $option = $this->createOption();
        $this->assertFalse($option->isFileDiff());
    }

    /**
     * @test
     */
    public function isFileDiff_file_diffオプションが指定されていればtrue()
    {
        $option = $this->createOption('--file-diff');
        $this->assertTrue($option->isFileDiff());
    }

    /**
     * @test
     */
    public function isFileDiff_setFileDiffでtrueを指定したらtrueになる()
    {
        $option = $this->createOption();
        $option->setFileDiff(true);
        $this->assertTrue($option->isFileDiff());
    }
}
