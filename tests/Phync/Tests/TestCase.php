<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Phync_Tests_TestCase extends PHPUnit_Framework_TestCase
{
    protected function createOption()
    {
        $args = func_get_args();
        $argv = array_merge(array('phync'), $args);
        return new Phync_Option($argv);
    }
}
