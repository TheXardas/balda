<?php
/**
 * Функции, работающие с деревьями
 */

/**
 * getDictionaryTree
 *
 * Строит и возвращает словарное дерево.
 */
function getDictionaryTree()
{
	profilerStart( __FUNCTION__ );

// Пробуем достать дерево из кэша (сессии)
	$tree = cacheGet( __FUNCTION__ );
	if ( $tree && is_array( $tree ) && ! empty( $tree ) )
	{
		profilerStop( __FUNCTION__ );
		return $tree;
	}

	$tree = array(
	// строка, содержащая все возможные буквы, идущие после текущей
		'letters' => '',
	// массив указателей на следующие узлы дерева. Ключи — порядковый номер буквы из строки letters
		'next' => array(),
	// Строка, содержащее слово, которому соответствует данный узел с учетом всех предыдущих
		'word' => NULL,
	);

	$count = 0;
	// TODO вынести блок с генерацией дерева в отдельную функцию
	$dictionary = getWholeDictionary();
	foreach ( $dictionary as $word )
	{
		$count++;
		$currentNode = &$tree;
		$wordLength = mb_strlen( $word, 'utf8' );
		for ( $i = 0; $i < $wordLength; $i++ )
		{
			$letter = mb_substr( $word, $i, 1, 'utf8' );
			$nextIndex = mb_strpos( $currentNode['letters'], $letter, NULL, 'utf8' );
			if ( $nextIndex === false )
			{
			// такого узла еще нет, создаем:
				$nextIndex = mb_strlen( $currentNode['letters'], 'utf8' );
				$currentNode['next'][$nextIndex] = array(
					'letters' => '',
					'next' => array(),
					'word' => NULL,
				);

			// Добавляем новую букву к возможным
				$currentNode['letters'] .= $letter;

			// Если это последняя буква в слове — записываем слово в узел
				if ( $i == $wordLength - 1 ) {
					$currentNode['next'][$nextIndex]['word'] = $word;
				}
			}
			$currentNode = &$currentNode['next'][$nextIndex];
		}
	}

// Сначала в сессию
	cacheSet( __FUNCTION__, $tree );

	profilerStop( __FUNCTION__ );
	return $tree;
}

/**
 * getInvertedTree
 *
 * Строит и возвращает инвертированное дерево.
 */
function getInvertedTree()
{
	profilerStart( __FUNCTION__ );

// Пробуем достать дерево из кэша (сессии)
	$tree = cacheGet( __FUNCTION__ );
	if ( $tree && is_array( $tree ) && ! empty( $tree ) )
	{
		profilerStop( __FUNCTION__ );
		return $tree;
	}

	$tree = array(
		// строка, содержащая все возможные буквы, идущие после текущей
		'letters' => '',
		// массив указателей на следующие узлы дерева. Ключи — порядковый номер буквы из строки letters
		'next' => array(),
		// Строка, содержащее слово, которому соответствует данный узел с учетом всех предыдущих
		'word' => NULL,
	);

	// TODO вынести блок с генерацией дерева в отдельную функцию
	$dictionary = getWholeDictionary();
	$count = 0;
	foreach ( $dictionary as $word )
	{
		$wordLength = mb_strlen( $word, 'utf8' );

	// Массив узлов, которые относятся к текущему слову (к ним будем добавлять каждую новую букву)
		$previousNodes = array(&$tree);
	// Сохраняем префикс, соответствующей узлу инвертированного дерева
		$prefix = '';
	// Берем буквы в слове в обратном порядке
		for ( $i = $wordLength - 1; $i >= 0; $i-- )
		{
			$letter = mb_substr( $word, $i, 1, 'utf8' );
			$prefix = $letter.$prefix;
			$currentNodes = array(&$tree);
			foreach ( $previousNodes as $letterCount => &$node )
			{
				$count++;
				$nextIndex = mb_strpos( $node['letters'], $letter, NULL, 'utf8' );
				if ( $nextIndex === false )
				{
				// такого узла еще нет, создаем:
					$nextIndex = mb_strlen( $node['letters'], 'utf8' );
					$node['next'][$nextIndex] = array(
						'letters' => '',
						'next' => array(),
						'word' => NULL,
					);

				// Добавляем новую букву к возможным
					$node['letters'] .= $letter;

				// Если это первая буква в слове — отмечаем узел как валидный префикс
					if ( $i == 0 ) {
						$node['next'][$nextIndex]['word'] = mb_substr( $prefix, 0, $letterCount + 1, 'utf8' );
					}
				}
				$currentNodes[] = &$node['next'][$nextIndex];
			}
			$previousNodes = $currentNodes;
		}
	}

	cacheSet( __FUNCTION__, $tree );

	profilerStop( __FUNCTION__ );
	return $tree;
}