<?php

define('PROFILER_PREFIX', '_profiler_');
define('PROFILER_PREFIX_FINISHED', '_profiler_finished_');
if ( ! defined( 'PROFILER_ENABLED' ) ) {
	define( 'PROFILER_ENABLED', false );
}

$_SESSION[PROFILER_PREFIX] = array();
if ( empty( $_SESSION[PROFILER_PREFIX_FINISHED] ) ) {
	$_SESSION[PROFILER_PREFIX_FINISHED] = array();
}

function profilerStart( $Key )
{
	if ( PROFILER_ENABLED ) {
		$_SESSION[PROFILER_PREFIX][$Key] = microtime(true);
	}
}

function profilerStop( $Key )
{
	if ( PROFILER_ENABLED )
	{
		$start = $_SESSION[PROFILER_PREFIX][$Key];
		$_SESSION[PROFILER_PREFIX_FINISHED][$Key] = round( (microtime(true) - $start) * 1000, 0 );
	}
}

function outputProfileInfo()
{
	if ( PROFILER_ENABLED )
	{
		$info = '';
		foreach ( $_SESSION[PROFILER_PREFIX_FINISHED] as $key => $time ) {
			$info .= "<b>$key:</b> $time ms<br/>";
		}
		$info .= '<b>Memory:</b> '.round( memory_get_peak_usage( true ) / 1024 / 10124, 2 ).' MB<br/>';

		$_SESSION[PROFILER_PREFIX_FINISHED] = array();
		echo $info;
	}
}