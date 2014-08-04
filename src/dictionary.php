<?php
/**
 * Helper-functions, working with dictionary
 */
//===============================================

/**
 * parseDictionary
 *
 * @param string $Filename — path to file
 * @return string
 */
function parseDictionary( $Filename )
{
	profilerStart( __FUNCTION__ );
	$words = file( $Filename, FILE_IGNORE_NEW_LINES );
	profilerStop( __FUNCTION__ );
	return $words;
}