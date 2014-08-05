<?php
/**
 * Файл с игровой логикой. Всё что относится к игре непосредственно — здесь
 */
//===============================================


/**
 * resetGame
 *
 * Сбрасывает текущую сессию с игрой
 */
function resetGame()
{
	cleanGameField();
	cleanUsedWords();
	$_SESSION['computerName'] = NULL;
	$_SESSION['scores'] = NULL;
	setIsPlayerMove( true );

	header('Location: /');
// Force exit, so no output will be sent
	die();
}


/**
 * getGameField
 *
 * Возвращает массив с массивами строк, 5x5.
 * Ключи первого массива соответствуют координате y. Ключи вложенных массивов - x.
 *
 * Если игрового поля еще нет (или оно было сброшено), то сгенерируется новое.
 *
 * @param $Dictionary
 * @return array
 */
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

/**
 * setGameField
 *
 * Кэширует игровое поле
 *
 * @param $GameField
 */
function setGameField( $GameField )
{
	$_SESSION['gameField'] = $GameField;
}

/**
 * cleanGameField
 *
 * Очищает кэш с игровым полем
 */
function cleanGameField()
{
	setGameField( NULL );
}

/**
 * getStartWord
 *
 * Возвращает случайное слово для начала игры
 *
 * @param $Dictionary
 * @return string
 * @throws Exception
 */
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

/**
 * acceptCells
 *
 * Парсит переданный массив ячеек.
 * Каждый элемент массива - массив вида:
 * array(
 * // координаты клетки
 * 	x => 0,
 * 	y => 2,
 * // Является ли буква в этой ячейке новой
 * 	isNew => 1,
 * // Буква, которая находится в этой ячейке
 * 	letter => 'я',
 * )
 *
 * @param $Dictionary
 * @param $Cell
 * @return bool
 * @throws LogicException
 */
function acceptCells( $Dictionary, $Cells )
{
	profilerStart( __FUNCTION__ );
	if ( ! isPlayerMove() ) {
		throw new LogicException( 'Сейчас ходит компьютер!' );
	}

	$word = '';
	$gameField = getGameField( $Dictionary );
	$lastX = null;
	$lastY = null;
	$newCell = null;

	foreach ( $Cells as $cell )
	{
		$x = $cell['x'];
		$y = $cell['y'];
		$letter = $cell['letter'];

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

		if ( $cell['isNew'] )
		{
			if ( ! is_null( $newLetter ) ) {
				throw new LogicException( 'За ход можно добавить только одну букву!' );
			}
			$newCell = $cell;

			if ( $gameField[$y][$x] !== '' ) {
				throw new LogicException( 'Добавлять буквы можно только в свободные клетки!' );
			}
		}
		elseif ( $gameField[$y][$x] !== $letter ) {
			throw new LogicException( 'Или вы жульничаете, или у нас что-то сломалось! Попробуйте поиграть попозже.' );
		}

		if ( ! $letter || ! is_string( $letter ) ) {
			throw new LogicException( 'Нужно выбирать ячейки с буквами!' );
		}

		if ( mb_strlen( $letter, 'utf8' ) !== 1 || mb_ereg_match( '/[^а-я]/msi', $letter ) ) {
			throw new LogicException( 'В ячейки можно вводить только кириллические буквы!' );
		}

		$word .= $letter;
	}

	if ( is_null( $newCell ) ) {
		throw new LogicException( 'Слово обязательно должно включать в себя новую букву!' );
	}

	profilerStop( __FUNCTION__ );
	return acceptWord( $Dictionary, $word, $newCell );
}

/**
 * acceptWord
 *
 * Проверяет соответствие слова логике игры.
 * Если всё ок - засчитывает слово в пользу игрока.
 *
 * @param $Dictionary
 * @param $Word
 * @param $NewLetter
 * @return bool
 * @throws LogicException
 */
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

	setIsPlayerMove( false );

	profilerStop( __FUNCTION__ );
	return true;
}

/**
 * addUsedWord
 *
 * Добавляет слово в список использованных, чтобы его нельзя было использовать повторно
 *
 *
 * @param $Word
 * @throws LogicException
 */
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

/**
 * cleanUsedWords
 *
 * Очищает список использованных слов
 */
function cleanUsedWords()
{
	$_SESSION['usedWords'] = NULL;
}

/**
 * addPlayerScoreWord
 *
 * Засчитывает слово в пользу игрока
 *
 * @param $Word
 */
function addPlayerScoredWord( $Word )
{
	$_SESSION['scores']['player'][] = $Word;
}


/**
 * addComputerScoredWord
 *
 * Засчитыват слово в пользу искусственного интеллекта
 *
 * @param $Word
 */
function addComputerScoredWord( $Word )
{
	$_SESSION['scores']['ai'][] = $Word;
}

/**
 * getScoredWords
 *
 * Возвращает массив, содержащий засчитанные каждым игроком слова для вывода таблицы очков.
 *
 * @return array
 */
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

/**
 * getComputerName
 *
 * Возвращает имя компьютерного игрока.
 *
 * @return string
 */
function getComputerName()
{
	if ( ! empty( $_SESSION['computerName'] ) ) {
		return $_SESSION['computerName'];
	}

	$possibleNames = array(
		'Игорь Долвич', 'Пятачок', 'Макс Пэйн', 'Пришелец', 'Фродо', 'Гарри Поттер', 'Вася Пупкин', 'Дарт Вейдер',
		'Хан Соло', 'Лейтенант Керриган', 'Джим Рэйнор', 'Гордон Фримэн', 'Джек Потрошитель', 'Фредди Крюгер', 'Кот',
	);
	$key = array_rand( $possibleNames );
	$_SESSION['computerName'] = $possibleNames[$key];
	return $_SESSION['computerName'];
}

/**
 * isPlayerMove
 *
 * Возвращает true, если сейчас - ход игрока.
 * False - компьютера
 *
 * @return bool
 */
function isPlayerMove()
{
	if ( empty( $_SESSION['isPlayerMove'] ) ) {
		$_SESSION['isPlayerMove'] = true;
	}
	return $_SESSION['isPlayerMove'];
}

/**
 * setIsPlayerMove
 *
 * @param $IsPlayerMove
 */
function setIsPlayerMove( $IsPlayerMove )
{
	$_SESSION['isPlayerMove'] = $IsPlayerMove;
}