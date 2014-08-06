<?php
/**
 * Helper-functions, working with dictionary
 */
//===============================================
define('DICTIONARY_PATH', SRC_ROOT.'dictionary/dictionary.txt');

/**
 * getWholeDictionary
 *
 * Возвращает весь словарь массивом
 *
 * @return string[]
 */
function getWholeDictionary()
{
	profilerStart( __FUNCTION__ );
	if ( $words = cacheGet( __FUNCTION__ ) ) {
		return $words;
	}
	$words = file( DICTIONARY_PATH, FILE_IGNORE_NEW_LINES );
	cacheSet( __FUNCTION__, $words, true );
	profilerStop( __FUNCTION__ );
	return $words;
}

/**
 * wordExists
 *
 * Проверяет, существует ли слово в словаре
 *
 * @param $Word string
 * @return bool
 */
function wordExists( $Word )
{
	profilerStart( __FUNCTION__ );
	$words = getWholeDictionary();
	$result = false;
	if ( in_array( $Word, $words ) ) {
		$result = true;
	}
	profilerStop( __FUNCTION__ );
	return $result;
}

/**
 * getFileHandler
 * @return resource
 */
function getFileHandler()
{
	profilerStart( __FUNCTION__ );
	$handler = fopen( DICTIONARY_PATH, 'r' );
	profilerStop( __FUNCTION__ );
	return $handler;
}