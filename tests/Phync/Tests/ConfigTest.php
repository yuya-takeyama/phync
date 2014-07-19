<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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

    /**
     * @test
     */
    public function getSshUserName_でフォルトはfalse()
    {
        $config = new Phync_Config($this->defaultConfigValues);
        $this->assertNull($config->getSshUserName());
    }

    /**
     * @test
     */
    public function getSshUserName_ssh_userが指定されているとき()
    {
        $this->defaultConfigValues['ssh_user'] = 'testuser';
        $config = new Phync_Config($this->defaultConfigValues);
        $this->assertEquals('testuser', $config->getSshUserName());
    }

    /**
     * @test
     */
    public function getRemoteTargetDir_デフォルトはnull()
    {
        $config = new Phync_Config($this->defaultConfigValues);
        $this->assertNull($config->getRemoteTargetDir());
    }

    /**
     * @test
     */
    public function getRemoteTargetDir_remote_target_dirが指定されているとき()
    {
        $this->defaultConfigValues['remote_target_dir'] = '/specific_target_dir';
        $config = new Phync_Config($this->defaultConfigValues);
        $this->assertEquals('/specific_target_dir', $config->getRemoteTargetDir());
    }

    /**
     * @test
     */
    public function isEnabledFileDiff_デフォルトはfalse()
    {
        $config = new Phync_Config($this->defaultConfigValues);
        $this->assertFalse($config->isEnabledFileDiff());
    }

    /**
     * @test
     */
    public function isEnabledFileDiff_file_diffがtrueならtrue()
    {
        $this->defaultConfigValues['file_diff'] = true;
        $config = new Phync_Config($this->defaultConfigValues);
        $this->asserttrue($config->isEnabledFileDiff());
    }
}
