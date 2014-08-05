<?php
session_start();

require_once('../src/config.php');
/** @var string[] $dictionary */

profilerStart( 'generating computer move' );
try {
	computerMove( $dictionary );
}
catch ( LogicException $e ){
	$_SESSION['error'] = $e->getMessage();
}
profilerStop( 'generating computer move' );

header( 'Location: /' );
die();