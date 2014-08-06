<?php
	session_start();

	require_once('../src/config.php');

	$gameField = getGameField();

if ( $_POST && ! empty( $_POST['word'] ) )
{
	$wordCells = json_decode( $_POST['word'], true );

	if ( ! is_null( $wordCells ) )
	{
		profilerStart( 'accepting user answer' );
		try {
			acceptCells( $wordCells );
		}
		catch ( LogicException $e ){
			$_SESSION['error'] = $e->getMessage();
		}
		profilerStop( 'accepting user answer' );
	}
}
header( 'Location: /' );
die();