<?php
/**
 * Cache functions
 */
//===============================================

// TODO избавиться от global. Singleton без ООП?
global $connection;

$connection = new Memcached();
$connection->addServer( 'localhost', 11211 );

/**
 *
 * @return string
 */
function cacheSet( $Key, $Value )
{
	global $connection;
	return $connection->set( $Key, $Value );
}

/**
 * @param $Key
 */
function cacheGet( $Key )
{
	global $connection;
	return $connection->get( $Key );
}

function cacheError()
{
	global $connection;
	echo $connection->getResultCode().' '.$connection->getResultMessage();
}