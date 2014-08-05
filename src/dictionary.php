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

/**
 * getDictionaryTree
 *
 * Строит и возвращает словарное дерево.
 *
 * @param $Dictionary
 */
function getDictionaryTree( $Dictionary )
{
	profilerStart( __FUNCTION__ );

// Пробуем достать дерево из кэша (сессии)
	$tree = false;
	$tree = cacheGet( __FUNCTION__ );
	if ( $tree && is_array( $tree ) && ! empty( $tree ) )
	{
		profilerStop( __FUNCTION__ );
		return $tree;
	}

// Пробуем достать из файлового кэша
	$cacheFileName = SRC_ROOT.'dictionary/dictionaryTree.txt';
	if ( file_exists( $cacheFileName ) )
	{
		$encodedTree = file_get_contents( $cacheFileName );
		if ( $encodedTree )
		{
			#$tree = unserialize( gzuncompress( $encodedTree ) );
			if ( is_array( $tree ) && ! empty( $tree ) )
			{
				cacheSet( __FUNCTION__, $tree );
				profilerStop( __FUNCTION__ );
				return $tree;
			}
		}
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
	foreach ( $Dictionary as $word )
	{
		$count++;
		$currentNode = &$tree;
		$wordLength = mb_strlen( $word, 'utf8' );
		for ( $i = 0; $i < $wordLength; $i++ )
		{
			$letter = mb_substr( $word, $i, 1, 'utf8' );
			$nextIndex = mb_strpos( $currentNode['letters'], $letter, null, 'utf8' );
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

// Кэшируем
	$encodedTree = gzcompress( serialize( $tree ) );

// Сначала в сессию
	cacheSet( __FUNCTION__, $tree );

// Теперь и в файл
	file_put_contents( $cacheFileName, $encodedTree );

	profilerStop( __FUNCTION__ );
	return $tree;
}

/**
 * getInvertedTree
 *
 * Строит и возвращает инвертированное дерево.
 *
 * @param $Dictionary
 */
function getInvertedTree( $Dictionary )
{
	// TODO implement
}