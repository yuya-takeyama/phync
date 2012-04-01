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
    public function isDryRun_executeオプションが指定されていればfalse()
    {
        $option = $this->createOption('--execute');
        $this->assertFalse($option->isDryRun());
    }

    private function createOption()
    {
        $argv = array_merge(array('phync'), func_get_args());
        return new Phync_Option($argv);
    }
}
