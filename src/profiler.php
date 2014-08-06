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
	if ( PROFILER_ENABLED )
	{
		$_SESSION[PROFILER_PREFIX][$Key]['memory'] = memory_get_usage( true );
		$_SESSION[PROFILER_PREFIX][$Key]['time'] = microtime( true );
	}
}

function profilerStop( $Key )
{
	if ( PROFILER_ENABLED )
	{
		$start = $_SESSION[PROFILER_PREFIX][$Key]['time'];
		$_SESSION[PROFILER_PREFIX_FINISHED][$Key]['time'] = round( (microtime( true ) - $start) * 1000, 0 );

		$start = $_SESSION[PROFILER_PREFIX][$Key]['memory'];
		$_SESSION[PROFILER_PREFIX_FINISHED][$Key]['memory'] = memory_get_usage( true ) - $start;
	}
}

function outputProfileInfo()
{
	if ( PROFILER_ENABLED )
	{
		$info = '';
		foreach ( $_SESSION[PROFILER_PREFIX_FINISHED] as $key => $profile )
		{
			$time = $profile['time'];
			$memory = $profile['memory'];
			$info .= "<b>$key:</b> $time ms. Memory changed: ".round( $memory / 1024 / 10124, 2 ).' MB</br>';
		}
		$info .= '<b>Memory:</b> '.round( memory_get_peak_usage( true ) / 1024 / 10124, 2 ).' MB<br/>';

		$_SESSION[PROFILER_PREFIX_FINISHED] = array();
		echo $info;
	}
}