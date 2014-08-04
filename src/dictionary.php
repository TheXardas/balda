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
	$words = file( $Filename, FILE_IGNORE_NEW_LINES );
	return $words;
}