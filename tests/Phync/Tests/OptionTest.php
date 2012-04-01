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

    private function createOption()
    {
        $argv = array_merge(array('phync'), func_get_args());
        return new Phync_Option($argv);
    }
}