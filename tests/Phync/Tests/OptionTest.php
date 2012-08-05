<?php
require_once 'Phync/Option.php';

class Phync_Tests_OptionTest extends PHPUnit_Framework_TestCase
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
    public function hasFiles_ファイル名の指定が無ければfalse()
    {
        $option = $this->createOption();
        $this->assertFalse($option->hasFiles());
    }

    /**
     * @test
     */
    public function hasFiles_ファイル名が指定されていればtrue()
    {
        $option = $this->createOption('README.md');
        $this->assertTrue($option->hasFiles());
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
    public function getFiles_指定されたファイル名を配列で取得する()
    {
        $option = $this->createOption('foo.txt', 'bar.txt');
        $this->assertEquals(array('foo.txt', 'bar.txt'), $option->getFiles());
    }

    private function createOption()
    {
        $args = func_get_args();
        $argv = array_merge(array('phync'), $args);
        return new Phync_Option($argv);
    }
}
