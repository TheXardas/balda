/**
 * Main game object
 */
balda = {

// jquery-object of game-field
	$el: $('.game-field'),
// One of following strings: letter-input, cell-selection, interaction-disabled
	mode: 'letter-input',
// jquery-object for currently editable cell
	$editableCell: {},
// jquery-object for active letter input
	$letterInput: {},
// array of selected cells
	selectedCells: [],

	init: function ()
	{
		this.enableLetterInputMode();

		$('.cell', this.$el).on('click', this.cellClickHandler);
	},

	cellClickHandler: function()
	{
		if ( balda.mode == 'letter-input' ) {
			balda.cellClickHandlerLetterInputMode.apply(this);
		}
		else if ( balda.mode == 'cell-selection' ) {
			balda.cellClickHandlerSelectionMode.apply(this);
		}
	},

	enableLetterInputMode: function()
	{
		this.mode = 'letter-input';
		this.$el.removeClass('cell-selection-mode interaction-disabled-mode').addClass('letter-input-mode');
		$('.remove-letter-link').remove();
		if ( balda.$editableCell.length ) {
			balda.$editableCell.text('').removeClass('editable');
		}
		balda.$editableCell = {};

		if ( balda.$letterInput.length ) {
			balda.$letterInput.remove();
		}
		balda.$letterInput = {};
		$('.cell.selected', this.$el).removeClass('selected');
		this.selectedCells = [];
		this.updateSelectedWord();

		$('.cell', this.$el).on('click', this.cellClickHandlerLetterInputMode);
	},

	cellClickHandlerLetterInputMode: function ()
	{
		if ( balda.mode != 'letter-input' ) return;
		balda.$el.removeClass('cell-selection-mode interaction-disabled-mode').addClass('letter-input-mode');

		if ( balda.$letterInput.length ) {
			balda.$letterInput.remove();
			balda.$editableCell.text('');
		}
		$('.error').remove();

		var DomElement = this;
		var $el = $(DomElement);
		if ( $el.text().trim().length > 0 ) return;

		var $input = $('<input class="letter-input" type="text" size="1" />');
		$input.on('change, keyup', balda.validateLetterInput);

		$el.addClass('editable');
		$el.html($input);
		$input.focus();
		balda.$editableCell = $el;
		balda.$letterInput = $input;
	},
	validateLetterInput: function ()
	{
		var value = $(this).val();
		value = value.trim();
		value = value.replace(/[^[а-я]]/gi, '');
		value = value.charAt(value.length-1);
		$(this).val(value);
		if ( value.length == 1 && value.match(/[а-я]/) ) {
			balda.enableCellSelectionMode();
		}
	},
	enableCellSelectionMode: function ()
	{
		this.mode = 'cell-selection';
		balda.$el.removeClass('letter-input-mode interaction-disabled-mode').addClass('cell-selection-mode');
		balda.$editableCell.text(balda.$letterInput.val()).addClass('custom');
		$('<a href="#" class="remove-letter-link" title="Убирает выбранную букву">Отменить</a>').on('click', function(){
			balda.enableLetterInputMode();
		}).prependTo($('.inputs'));
	},
	cellClickHandlerSelectionMode: function()
	{
		if ( balda.mode != 'cell-selection' ) return;
		var DomElement = this;
		var $el = $(DomElement);
		var lastSelectedCell = false;
		if ( balda.selectedCells.length ) {
			lastSelectedCell = balda.selectedCells[balda.selectedCells.length - 1];
		}

		var x = $el.data('x');
		var y = $el.data('y');
		var letter = $el.text().trim();
		var isNew = $el.hasClass('custom');

		if ( $el.hasClass('selected') )
		{
			if ( lastSelectedCell.x === x && lastSelectedCell.y === y )
			{
				$el.removeClass('selected');
				balda.selectedCells.pop();
			}
		}
		else
		{
			if ( ! letter.trim().length ) return;
			if ( lastSelectedCell &&
				( ! (x - 1 === lastSelectedCell.x && y === lastSelectedCell.y) &&
					! (x + 1 === lastSelectedCell.x && y === lastSelectedCell.y) &&
					! (y - 1 === lastSelectedCell.y && x === lastSelectedCell.x) &&
					! (y + 1 === lastSelectedCell.y && x === lastSelectedCell.x)
				)
			) return;

			balda.selectedCells.push({
				x: x,
				y: y,
				letter: letter,
				isNew: isNew
			});
			$el.addClass('selected');
		}
		balda.updateSelectedWord();
	},

	updateSelectedWord: function()
	{
		var $selectedWord = $('.selected-word');
		var word = '';
		for ( var i = 0; i < this.selectedCells.length; i++) {
			word += this.selectedCells[i].letter
		}

		var wordLength = word.length;
		word = wordLength ? word + ' ('+word.length+')' : '';
		$selectedWord.text(word);

		console.log( word );
		if ( wordLength > 1 ) {
			balda.enableWordForm();
		}
		else {
			balda.disableWordForm();
		}
	},
	enableWordForm: function()
	{
		var $mainInput = $('.word-input');
		if ( ! $('.accept-word-form').length )
		{
			var $form = $('<form class="accept-word-form" action="/acceptWord.php" method="POST"></form>');
			$mainInput = $('<input class="word-input" name="word" type="hidden" value="" />');
			$mainInput.appendTo($form);
			var $confirm = $('<button class="accept-word" title="Проверяет выбранное слово как окончательный ответ">Отправить</button>')
			$confirm.appendTo($form);
			$form.appendTo($('.inputs'));
		}
		$mainInput.val(JSON.stringify(balda.selectedCells));
	},
	disableWordForm: function() {
		$('.accept-word-form').remove();
	}
};
balda.init();