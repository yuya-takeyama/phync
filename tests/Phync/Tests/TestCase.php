<?php
class Phync_Tests_TestCase extends PHPUnit_Framework_TestCase
{
    protected function createOption()
    {
        $args = func_get_args();
        $argv = array_merge(array('phync'), $args);
        return new Phync_Option($argv);
    }
}
