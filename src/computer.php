<?php
/**
 * Функции, отвечающие за искусственный интеллект
 */
//===============================================

/**
 * computerMove
 *
 * Делает ход компьютера
 */
function computerMove()
{
	profilerStart( __FUNCTION__ );
	if ( isPlayerMove() ) {
		throw new LogicException( 'Сейчас ход игрока!' );
	}

	$alphabet = array(
		'а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
		'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'
	);

	$gameField = getGameField();

	$tree = getDictionaryTree();
	$invTree = getInvertedTree();

	$count = array();
	$words = array();
	$wordFound = false;

// Проходим по всему игровому полю
	foreach ( $gameField as $y => $row )
	{
		foreach ( $row as $x => $cell )
		{
		// Ищем только пустые ячейки
			if ( $cell === '' )
			{
			// У ячейки должна обязательно быть хотя бы одна соседняя ячейка по горизонтали или по вертикали с какой-нибудь буквой
				if ( (isset( $gameField[$y][$x+1] ) && $gameField[$y][$x+1] !== '') ||
						(isset( $gameField[$y][$x-1] ) && $gameField[$y][$x-1] !== '') ||
						(isset( $gameField[$y+1][$x] ) && $gameField[$y+1][$x] !== '') ||
						(isset( $gameField[$y-1][$x] ) && $gameField[$y-1][$x] !== '') )
				{
				// Подставляем каждую букву алфавита
					foreach ( $alphabet as $insertedLetter )
					{
						$index = mb_strpos( $invTree[LETTERS], $insertedLetter, NULL, 'utf8' );
						if ( $index === false ) continue;
					// Подставляем букву прямо в ячейку
						$gameField[$y][$x] = $insertedLetter;
						$subTree = $invTree[NEXT][$index];
						$count = array();
						$count[$y][$x] = 1;
						$newLetter = array(
							'y' => $y,
							'x' => $x,
							LETTERS => $insertedLetter,
						);

						$moreWords = computerFindWord( $gameField, $subTree, $y, $x, false, $tree, $newLetter, $count );
						$words = array_merge( $words, $moreWords );
					}
				// Не забываем освободить ячейку
					$gameField[$y][$x] = '';
				}
			}
		}
	}

// Если что-то нашлось, отсекаем лишнее, берем самое лучшее
	if ( $words )
	{
	// Ищем самое длинное слово, чтобы по больше очков набрать
		$maxLength = 0;
		$longestWord = NULL;
		$bestLetterCoordinates = NULL;
		foreach ( $words as $word => $newLetterCoordinates )
		{
			if ( isUsedWord( $word ) ) {
				continue;
			}
			$wordLength = mb_strlen( $word, 'utf8' );
			if ( $wordLength > $maxLength )
			{
				$maxLength = $wordLength;
				$longestWord = $word;
				$bestLetterCoordinates = $newLetterCoordinates;
			}
		}

		if ( $longestWord )
		{
		// Компьютер нашел слово. Подставляем его
			$y = $bestLetterCoordinates['y'];
			$x = $bestLetterCoordinates['x'];
			$gameField = getGameField();
			$gameField[$y][$x] = $bestLetterCoordinates[LETTERS];
			setGameField( $gameField );
			addComputerScoredWord( $longestWord );
			setIsPlayerMove( true );
			$wordFound = true;
		}
	}


	if ( ! $wordFound )
	{
		setIsPlayerMove( true );
		$_SESSION['gameOver'] = true;
	// Компьютер проиграл!

	}

	profilerStop( __FUNCTION__ );
}

/**
 * computerFindWord
 *
 * Функция поиска слов на поле по деревьям
 *
 * @param $GameField array Игровое поле
 * @param $Node array Узел, от которого нужно искать слова
 * @param $Y int Клетка, от которой надо искать
 * @param $X int
 * @param $IsDictionaryTree bool Является ли $Node словарным деревом
 * @param $DictionaryTree array словарное дерево
 * @param $NewLetter array Координаты новой буквы
 * @param $Count array Счетчик занятых клеток
 *
 * @return array
 */
function computerFindWord( $GameField, $Node, $Y, $X, $IsDictionaryTree, $DictionaryTree, $NewLetter, &$Count )
{
	$foundWords = array();
	profilerStart('checking word'.$X.$Y);
	if ( ! is_null( $Node[WORD] ) )
	{
		if ( $IsDictionaryTree )
		{
		// Если это словарное дерево, то мы нашли слово
			$foundWords[$Node[WORD]] = $NewLetter;
		}
		else
		{
		// Инвертированное дерево
			$dictNode = $DictionaryTree;
			for ( $i = 0; $i < mb_strlen( $Node[WORD], 'utf8' ); $i++ )
			{
				$letter = mb_substr( $Node[WORD], $i, 1, 'utf8' );
				$j = mb_strpos( $dictNode[LETTERS], $letter, NULL, 'utf8' );

				$dictNode = $dictNode[NEXT][$j];
			}
		// Найден валидный префикс. Ищем по нему слово через словарное дерево
			$subResult = computerFindWord( $GameField, $dictNode, $NewLetter['y'], $NewLetter['x'], true, $DictionaryTree, $NewLetter, $Count );
			$foundWords = array_merge( $subResult, $foundWords );
		}
	}
	profilerStop('checking word'.$X.$Y);

// Итерируем все соседние клетки
	$neighbours = array(
		[$X+1, $Y], [$X-1, $Y], [$X, $Y+1], [$X, $Y-1]
	);
	profilerStart('neihbouring'.$X.$Y);
	foreach ( $neighbours as $coordinates )
	{
		$nx = $coordinates[0];
		$ny = $coordinates[1];
	// Если ячейка вне поля, то ищем другую
		if ( $nx > 4 || $nx < 0 || $ny > 4 || $ny < 0 ) {
			continue;
		}

	// Если ячейка пустая, то ищем другую
		if ( $GameField[$ny][$nx] === '' ) {
			continue;
		}

	// Если буквы нет в "возможных следующих"
		$subIndex = mb_strpos( $Node[LETTERS], $GameField[$ny][$nx], NULL, 'utf8' );
		if ( $subIndex === false ) {
			continue;
		}

	// Если ячейка уже занята
		if ( isset( $Count[$ny][$nx] ) ) {
			continue;
		}

		$nextNode = $Node['n'][$subIndex];
		$Count[$ny][$nx] = 1;
		$subResult = computerFindWord( $GameField, $nextNode, $ny, $nx, $IsDictionaryTree, $DictionaryTree, $NewLetter, $Count );
		$foundWords = array_merge( $subResult, $foundWords );
	}
	profilerStop('neihbouring'.$X.$Y);
	return $foundWords;
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
