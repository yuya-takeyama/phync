<?php
set_include_path(
    dirname(__FILE__) . '/../src' . PATH_SEPARATOR .
    dirname(__FILE__) . PATH_SEPARATOR .
    dirname(__FILE__) . '/../vendor/yuya-takeyama/Phake/src' . PATH_SEPARATOR .
    dirname(__FILE__) . '/../vendor/pear' . PATH_SEPARATOR .
    get_include_path()
);
ini_set('error_reporting', E_ALL & ~E_STRICT);
require_once 'Phync/Tests/TestCase.php';
require_once 'Phake.php';
