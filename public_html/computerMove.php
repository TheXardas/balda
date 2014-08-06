<?php
$start = microtime(true);
session_start();

$end = microtime(true);
echo $end-$start;

require_once('../src/config.php');

profilerStart( 'generating computer move' );
try {
	computerMove();
}
catch ( LogicException $e ){
	$_SESSION['error'] = $e->getMessage();
}
profilerStop( 'generating computer move' );

header( 'Location: /' );
die();