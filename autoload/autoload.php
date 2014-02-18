<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
set_include_path(dirname(__FILE__) . '/../vendor/pear' . PATH_SEPARATOR . get_include_path());

if (version_compare(PHP_VERSION, '5.3', '>=')) {
    include __DIR__ . '/../vendor/autoload.php';
} else {
    include dirname(__FILE__) . '/../vendor/splclassloader/SplClassLoader.php';

    $loader = new SplClassLoader('Phync', dirname(__FILE__) . '/../src');
    $loader->setNamespaceSeparator('_');
    $loader->register();
}
