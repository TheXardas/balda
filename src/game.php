<?php
/**
 * Main game-logic file. Contains all functions related to the game-logic
 */


function getStartWord( $Dictionary )
{
	shuffle( $Dictionary );
	foreach ( $Dictionary as $key => $word )
	{
		if ( mb_strlen( $word, 'utf8' ) == 5 ) {
			return $word;
		}
	}
}