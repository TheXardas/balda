<?php
	session_start();

	require_once('../src/config.php');
/** @var string[] $dictionary */

	$gameField = getGameField( $dictionary );

if ( $_POST && ! empty( $_POST['word'] ) )
{
	$wordCells = json_decode( $_POST['word'], true );

	if ( ! is_null( $wordCells ) )
	{
		profilerStart( 'accepting user answer' );
		try {
			acceptCells( $dictionary, $wordCells );
		}
		catch ( LogicException $e ){
			$_SESSION['error'] = $e->getMessage();
		}
		profilerStop( 'accepting user answer' );
	}
}
header( 'Location: /' );
die();