<?php
/**
 * Cache functions
 */
//===============================================

/**
 *
 * @return string
 */
function cacheSet( $Key, $Value, $FastOnly = false )
{
	$result = apc_store( $Key, bzcompress( serialize( $Value ) ) );
	if ( $FastOnly ) {
		return $result;
	}
	return fileCacheSet( $Key, $Value );
}

/**
 * @param $Key
 */
function cacheGet( $Key )
{
// Сначала быстрый кэш
	if ( $apcCache = apc_fetch( $Key ) ) {
		return unserialize( bzdecompress( $apcCache ) );
	}
// Теперь из файла
	elseif ( $fileCache = fileCacheGet( $Key ) )
	{
		cacheSet( $Key, $fileCache, true );
		return $fileCache;
	}
	return NULL;
}

function fileCacheGet( $Key )
{
	$fileName = SRC_ROOT.'dictionary/'.$Key.'.txt';
	if ( file_exists( $fileName ) ) {
		return unserialize( bzdecompress( file_get_contents( $fileName ) ) );
	}
	return null;
}

function fileCacheSet( $Key, $Value )
{
	return file_put_contents( SRC_ROOT.'dictionary/'.$Key.'.txt', bzcompress( serialize( $Value ) ) );
}