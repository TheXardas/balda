<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL);
define('SRC_ROOT', '../src/');
require_once(SRC_ROOT.'dictionary.php');
require_once(SRC_ROOT.'cache.php');
require_once(SRC_ROOT.'game.php');

$dictionary = parseDictionary('../src/dictionary.txt');

