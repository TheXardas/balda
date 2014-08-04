
function attachGameEvents()
{
	$('.cell.clickable').on('click', enableEditMode);
}

/**
 *
 * @param DomElement
 */
function enableEditMode()
{
	DomElement = this;
	if ( $('.editable, .select-mode').length ) return;

	$el = $(DomElement);

	if ( $el.hasClass( 'editable' ) ) return;
	if ( $el.text().trim().length > 0 ) return;

	console.log('Enable editable!');
	$input = $('<input class="letter-input" type="text" size="1" />');
	$input.on('change, keyup', validateLetterInput);

	$el.addClass('editable');
	$el.html($input);
	$input.focus();
}

function enableSelectMode()
{
	$('.table').addClass('select-mode');
	$('.editable').text($('.editable .letter-input').val()).addClass('custom');
}

/**
 *
 */
function validateLetterInput()
{
	var value = $(this).val();
	value = value.trim();
	value = value.replace(/[^[а-я]]/gi, '');
	value = value.charAt(value.length-1);
	$(this).val(value);
	if ( value.length == 1 && value.match(/[а-я]/) ) {
		enableSelectMode();
	}
}