<?php
/**
 * Helper-functions, working with dictionary
 */
//===============================================

/**
 * @return Memcached
 */
function cacheConnect()
{
	$connection = new Memcached();
	$connection->addServer( 'localhost', 11211 );
	return $connection;
}

/**
 *
 * @return string
 */
function cacheSet( Memcached $Connection, $Key, $Value )
{
	return $Connection->set( $Key, $Value );
}

/**
 * @param $Key
 */
function cacheGet( Memcached $Connection, $Key )
{
	return $Connection->get( $Key );
}