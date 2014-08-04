<?php
/** @var $start int Application start time for profiling purposes  */
	$start = microtime(true);

	session_start();

	require_once('../src/config.php');
/** @var string[] $dictionary */

/** Reset game field if user wishes so */
	if ( ! empty( $_GET['reset'] ) ) {
		resetGame();
	}

	$gameField = getGameField( $dictionary );
?>
<html>
<head>
	<meta charset="utf-8">
	<title>Балда</title>
	<link rel="stylesheet" type="text/css" href="/css/game.css" />
	<script src="/js/jquery.min.js"></script>
</head>
<body>
	<h5></h5>
	<div class="wrapper">
		<div class="table game-field">
			<? foreach ( $gameField as $y => $row ) : ?>
				<div class="row">
					<? foreach ( $row as $x => $cell ) : ?>
						<div data-x="<?= $x ?>" data-y="<?= $y ?>" class="cell <?= ! $cell ? 'clickable' : '' ?>" >
							<?= $cell ?>
						</div>
					<? endforeach ?>
				</div>
			<? endforeach ?>
		</div>
	</div>
	<span class="selected-word"></span><br/>
	<div class="inputs"></div>

	<? // Отображаем ошибку, если есть ?>
	<? if ( ! empty( $_SESSION['error'] ) ) : ?>
		<span class="error"><?= $_SESSION['error'] ?></span>
		<? unset( $_SESSION['error'] ) ?>
	<? endif ?>


	<? // Таблица с очками ?>
	<h4>Очки:</h4>
	<table class="scores">
		<thead>
			<tr>
				<th>
					Вы
				</th>
				<th>
					<?= getComputerName() ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<? $playerScores = $computerScores = 0; ?>
			<? foreach ( getScoredWords() as $key => $scores ) : ?>
				<tr>
				<? $score = mb_strlen( $scores['player'], 'utf8' ) ?>
				<? $playerScores += $score; ?>
				<td>
					<?= $scores['player'].' ('.$score.')' ?>
				</td>
				<? if ( $scores['computer'] ) : ?>
					<? $score = mb_strlen( $scores['computer'], 'utf8' ) ?>
					<? $computerScores += $score; ?>
					<td>
						<?= $scores['computer'].' ('.$score.')' ?>
					</td>
				<? else: ?>
					<td></td>
				<? endif ?>
			<? endforeach ?>
		</tbody>
		<tfoot>
			<tr>
				<th>
					<?= $playerScores ?>
				</th>
				<th>
					<?= $computerScores ?>
				</th>
			</tr>
		</tfoot>
	</table>


	<br/>
	<a href="/?reset=1">Заново!</a><br/>
	<br/>
	Script time: <?php echo round( (microtime(true) - $start) * 1000, 0); ?> ms<br/>
	<script src="/js/game.js"></script>
	<? outputProfileInfo() ?>
</body>
</html>