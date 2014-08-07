<?php
/** @var $start int Время запуска приложения */
	$start = microtime(true);

	session_start();

	require_once('../src/config.php');

/** Сбрасывает игру, если того захотел пользователь */
	if ( ! empty( $_GET['reset'] ) ) {
		resetGame();
	}

	$gameField = getGameField();
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

	<div class="interface">
		<? // Отображаем ошибку, если есть ?>
		<? if ( ! empty( $_SESSION['error'] ) ) : ?>
			<span class="error"><?= $_SESSION['error'] ?></span>
			<? unset( $_SESSION['error'] ) ?>
		<? endif ?>

		<span class="selected-word"></span><br/>
		<a href="/?reset=1" class="reset-link">Заново!</a><br/>
		<div class="inputs">

		</div>
	</div>

	<? // Таблица с очками ?>
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


	<? if ( empty( $_SESSION['gameOver'] ) ) : ?>
		<script src="/js/game.js"></script>
	<? endif ?>

	<? if ( ! isPlayerMove() ) : ?>
		<script type="text/javascript">
			window.location.href = '/computerMove.php';
		</script>
	<? endif ?>

	<? if ( PROFILER_ENABLED ) : ?>
		<? outputProfileInfo() ?>
	<? endif ?>
</body>
</html>