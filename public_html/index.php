<?php
	session_start();
if ( $_GET['reset'] ) {
	$_SESSION['gameField'] = NULL;
	$_SESSION['startWord'] = NULL;
}

	$start = microtime(true);
?>
<html>
<head>
	<meta charset="utf-8">
	<title>test</title>
	<style type="text/css">
		.table
		{
			display: table;
			width: auto;
			background-color: #eee;
			border: 1px solid #666666;
			/* border-collapse: separate;*/
		}

		.row
		{
			display: table-row;
			width: auto;
		}

		.cell
		{
			float: left;
			display: table-column;
			width: 20px;
			height: 20px;
			background-color: #ccc;
			border: dotted 1px gray;
			text-align: center;
			cursor: default;
		}
			.select-mode .cell {
				cursor: pointer;
			}

			.cell.clickable {
				cursor: pointer;
			}

			.cell.custom {
				color: blue;
			}

			.cell.selected {
				background: orange;
			}

		.letter-input {
			height: 100%;
			width: 100%;
			padding: 0;
			border: none;
			text-align: center;
		}
	</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="/js/game.js"></script>
</head>

<?php
require_once('../src/config.php');
/** @var string[] $dictionary
 */

if ( empty( $_SESSION['gameField'] ) )
{
// Empty game field
	$gameField = [
		['', '', '', '', ''],
		['', '', '', '', ''],
		['', '', '', '', ''],
		['', '', '', '', ''],
		['', '', '', '', ''],
	];

	$startWord = getStartWord( $dictionary );
	for ( $i = 0; $i < 5; $i++ ) {
		$gameField[2][$i] = mb_substr( $startWord, $i, 1, 'utf8' );
	}

	$_SESSION['gameField'] = $gameField;
}

$gameField = $_SESSION['gameField'];

?>

<body>
	<h5></h5>
	<div class="wrapper">
		<div class="table">
			<? foreach ( $gameField as $row ) : ?>
				<div class="row">
					<? foreach ( $row as $cell ) : ?>
						<div class="cell <?= ! $cell ? 'clickable' : '' ?>" >
							<?= $cell ?>
						</div>
					<? endforeach ?>
				</div>
			<? endforeach ?>
		</div>
	</div>

	<script type="text/javascript">
		attachGameEvents();
	</script>

	Time: <?php echo (microtime(true) - $start) * 1000; ?><br/>
	<a href="/?reset=1">Reset</a>

</body>
</html>