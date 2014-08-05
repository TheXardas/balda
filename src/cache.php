<?php
/**
 * Cache functions
 */
//===============================================

// TODO избавиться от global. Singleton без ООП?
global $connection;

$connection = null;
#$connection = new Memcached();
//$connection->addServer( 'localhost', 11211 );

/**
 *
 * @return string
 */
function cacheSet( $Key, $Value )
{
	$Value = '<?php return '.var_export( $Value, true ).';';
	file_put_contents( SRC_ROOT.'dictionary/dictionaryExport.php', $Value );
}

/**
 * @param $Key
 */
function cacheGet( $Key )
{
	$value = include( SRC_ROOT.'dictionary/dictionaryExport.php');
	return $value;
	//global $connection;
	//return $connection->get( $Key );
}

function cacheError()
{
	//global $connection;
	//echo $connection->getResultCode().' '.$connection->getResultMessage();
}