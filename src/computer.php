<?php
/**
 * Функции, отвечающие за искусственный интеллект
 */
//===============================================

/**
 * computerMove
 *
 * Делает ход компьютера
 *
 * @param $Dictionary
 */
function computerMove( $Dictionary )
{
	profilerStart( __FUNCTION__ );
	if ( isPlayerMove() ) {
	// Выключил, пока тестирую
		//throw new LogicException( 'Сейчас ход игрока!' );
	}

	$gameField = getGameField( $Dictionary );

	$tree = getDictionaryTree( $Dictionary );
	profilerStop( __FUNCTION__ );

// TODO implement
}
