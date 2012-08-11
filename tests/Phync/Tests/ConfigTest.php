<?php
require_once 'Phync/Config.php';

class Phync_Tests_ConfigTest extends PHPUnit_Framework_TestCase
{
    private $defaultConfigValues;

    public function setUp()
    {
        $this->defaultConfigValues = array(
            'destinations' => array('localhost'),
        );
    }

    /**
     * @test
     */
    public function isDefaultChecksum_でフォルトではfalse()
    {
        $config = new Phync_Config($this->defaultConfigValues);
        $this->assertFalse($config->isDefaultChecksum());
    }

    /**
     * @test
     */
    public function isDefaultChecksum_default_checksumがtrueならtrue()
    {
        $this->defaultConfigValues['default_checksum'] = true;
        $config = new Phync_Config($this->defaultConfigValues);
        $this->assertTrue($config->isDefaultChecksum());
    }

    /**
     * @test
     */
    public function isDefaultChecksum_default_checksumがfalseならfalse()
    {
        $this->defaultConfigValues['default_checksum'] = false;
        $config = new Phync_Config($this->defaultConfigValues);
        $this->assertFalse($config->isDefaultChecksum());
    }
}
