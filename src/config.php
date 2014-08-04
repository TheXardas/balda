<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL);
define('SRC_ROOT', '../src/');
require_once(SRC_ROOT.'dictionary.php');
require_once(SRC_ROOT.'cache.php');
require_once(SRC_ROOT.'game.php');

define('PROFILER_ENABLED', true);
require_once(SRC_ROOT.'profiler.php');
$dictionary = parseDictionary('../src/dictionary.txt');