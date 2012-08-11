<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'Phync/Config.php';

class Phync_Tests_ConfigTest extends Phync_Tests_TestCase
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
