<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'Phync/Logger/FileDiffLogger.php';

class Phync_Tests_Logger_FileDiffLoggerTest extends Phync_Tests_TestCase
{
    public function setUp()
    {
        $this->logger = new Phync_Logger_FileDiffLogger;
    }

    /**
     * @test
     */
    public function extractFileList_dryrun結果出力からファイル一覧のみを抽出()
    {
        $message = <<<__EOS__
building file list ... done
./
hello.php
phynctest/
phynctest/hello.php
phynctest/test.txt
phynctest/ticket01.gif
phynctest/directory/
phynctest/directory/hello.link.php -> hello.php
phynctest/directory/directory2/
phynctest/directory/test.txt

sent 548 bytes  received 80 bytes  1256.00 bytes/sec
total size is 8461  speedup is 13.47
__EOS__;

        $expect = array(
            'hello.php',
            'phynctest/hello.php',
            'phynctest/test.txt',
            'phynctest/ticket01.gif',
            'phynctest/directory/test.txt',
        );
        $this->assertSame($expect, $this->logger->extractFileList($message));
    }
}
