<?php
/**
 * Main game-logic file. Contains all functions related to the game-logic
 */


function resetGame()
{
	cleanGameField();
	cleanUsedWords();
	$_SESSION['computerName'] = NULL;
	$_SESSION['scores'] = NULL;

	header('Location: /');
// Force exit, so no output will be sent
	die();
}


function getGameField( $Dictionary )
{
	if ( ! empty( $_SESSION['gameField'] ) ) {
		return $_SESSION['gameField'];
	}

// Empty game field
	$gameField = [
		['', '', '', '', ''],
		['', '', '', '', ''],
		['', '', '', '', ''],
		['', '', '', '', ''],
		['', '', '', '', ''],
	];

	$startWord = getStartWord( $Dictionary );

	for ( $i = 0; $i < 5; $i++ ) {
		$gameField[2][$i] = mb_substr( $startWord, $i, 1, 'utf8' );
	}

	setGameField( $gameField );

	return $gameField;
}

function setGameField( $GameField )
{
	$_SESSION['gameField'] = $GameField;
}

function cleanGameField()
{
	setGameField( NULL );
}

function getStartWord( $Dictionary )
{
	profilerStart( __FUNCTION__ );
	shuffle( $Dictionary );
	foreach ( $Dictionary as $word )
	{
		if ( mb_strlen( $word, 'utf8' ) == 5 )
		{
			addUsedWord($word);
			profilerStop( __FUNCTION__ );
			return $word;
		}
	}
	throw new Exception('Dictionary is incomplete! No 5-symbol words');
}

function acceptCells( $Dictionary, $Cell )
{
	profilerStart( __FUNCTION__ );
	$word = '';
	$gameField = getGameField( $Dictionary );
	$lastX = null;
	$lastY = null;
	$newLetter = null;

	foreach ( $Cell as $letter )
	{
		$x = $letter['x'];
		$y = $letter['y'];

		if ( $x < 0 || $x > 4 || $y < 0 || $y > 4 ) {
			throw new LogicException( 'Нужно умещать слова в игровое поле 5x5!' );
		}

	// Проверяем, что каждая буква - это сосед предыдущей буквы
		if ( ! is_null( $lastX ) && ! is_null( $lastY ) )
		{
			if ( ! ($lastX + 1 === $x && $lastY === $y ) &&
					! ($lastX - 1 === $x && $lastY === $y ) &&
					! ($lastY + 1 === $y && $lastX === $x ) &&
					! ($lastY - 1 === $y && $lastX === $x ) )
			{
				throw new LogicException( 'Все буквы в слове должны быть в соседних клетках!' );
			}
		}

		if ( $letter['isNew'] )
		{
			if ( ! is_null( $newLetter ) ) {
				throw new LogicException( 'За ход можно добавить только одну букву!' );
			}
			$newLetter = $letter;

			if ( $gameField[$y][$x] !== '' ) {
				throw new LogicException( 'Добавлять буквы можно только в свободные клетки!' );
			}
		}
		elseif ( $gameField[$y][$x] !== $letter['letter'] ) {
			throw new LogicException( 'Или вы жульничаете, или у нас что-то сломалось! Попробуйте поиграть попозже.' );
		}

		if ( ! $letter['letter'] || ! is_string( $letter['letter'] ) ) {
			throw new LogicException( 'Нужно выбирать ячейки с буквами!' );
		}

		if ( mb_strlen( $letter['letter'], 'utf8' ) !== 1 || mb_ereg_match( '/[^а-я]/msi', $letter['letter'] ) ) {
			throw new LogicException( 'В ячейки можно вводить только кириллические буквы!' );
		}

		$word .= $letter['letter'];
	}

	if ( is_null( $newLetter ) ) {
		throw new LogicException( 'Слово обязательно должно включать в себя новую букву!' );
	}

	profilerStop( __FUNCTION__ );
	return acceptWord( $Dictionary, $word, $newLetter );
}

function acceptWord( $Dictionary, $Word, $NewLetter )
{
	profilerStart( __FUNCTION__ );
	$Word = trim( $Word );

	if ( mb_strlen( $Word, 'utf8' ) < 2 ) {
		throw new LogicException( 'Выбрано слишком короткое слово.' );
	}

	if ( ! in_array( $Word, $Dictionary ) ) {
		throw new LogicException( 'Нет такого слова!' );
	}

// Если мы здесь, значит слово корректное
	addUsedWord( $Word );

// Обновляем поле
	$gameField = getGameField( $Dictionary );
	$x = $NewLetter['x'];
	$y = $NewLetter['y'];
	$gameField[$y][$x] = $NewLetter['letter'];

	setGameField( $gameField );

	addPlayerScoredWord( $Word );

	profilerStop( __FUNCTION__ );
	return true;
}

function addUsedWord( $Word )
{
	if ( empty( $_SESSION['usedWords'] ) ) {
		$_SESSION['usedWords'] = array();
	}
	if ( in_array( $Word, $_SESSION['usedWords'] ) ) {
		throw new LogicException( 'Такое слово уже есть на игровом поле!' );
	}
	$_SESSION['usedWords'][] = $Word;
}

function cleanUsedWords()
{
	$_SESSION['usedWords'] = NULL;
}

function addPlayerScoredWord( $Word )
{
	$_SESSION['scores']['player'][] = $Word;
}

function addComputerScoredWord( $Word )
{
	$_SESSION['scores']['ai'][] = $Word;
}

function getScoredWords()
{
	$result = array();
	if ( ! empty( $_SESSION['scores'] ) )
	{
		foreach ( $_SESSION['scores']['player'] as $key => $playerWord )
		{
			$result[] = array(
				'player' => $playerWord,
				'computer' => ! empty( $_SESSION['scores']['computer'] ) ?: ''
			);
		}
	}
	return $result;
}

function getComputerName()
{
	if ( ! empty( $_SESSION['computerName'] ) ) {
		return $_SESSION['computerName'];
	}

	$possibleNames = array(
		'Игорь Долвич', 'Пятачок', 'Макс Пэйн', 'Пришелец', 'Фродо', 'Гарри Поттер', 'Вася Пупкин', 'Дарт Вейдер',
		'Хан Соло', 'Лейтенант Керриган', 'Гордон Фримэн', 'Джек Потрошитель', 'Фредди Крюгер', 'Кот',
	);
	$key = array_rand( $possibleNames );
	$_SESSION['computerName'] = $possibleNames[$key];
	return $_SESSION['computerName'];
}