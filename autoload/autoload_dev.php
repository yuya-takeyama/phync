<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (version_compare(PHP_VERSION, '5.3', '>=')) {
    set_include_path(
        __DIR__ . '/../vendor/phake/phake/src' .
        PATH_SEPARATOR .
        get_include_path()
    );
} else {
    set_include_path(
        dirname(__FILE__) . '/../vendor/yuya-takeyama/Phake/src' .
        PATH_SEPARATOR .
        get_include_path()
    );
}

include dirname(__FILE__) . '/autoload.php';
require_once 'Phake.php';
