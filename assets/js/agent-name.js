(function ($) {
	var $first   = $('input[name="acf[field_69bb206cb458b]"]');
	var $last    = $('input[name="acf[field_69bb209ab458d]"]');
	var $display = $('input[name="acf[field_69bb22d2064d2]"]');

	var userEdited = false;

	// Once the editor manually changes display name, stop overwriting
	$display.on( 'input', function () {
		userEdited = true;
	});

	function syncDisplayName() {
		if ( userEdited ) {
			return;
		}
		var first   = $.trim( $first.val() );
		var last    = $.trim( $last.val() );
		var display = '';

		if ( first && last ) {
			display = first + ' ' + last;
		} else {
			display = first || last;
		}

		$display.val( display );
	}

	$first.on( 'input', syncDisplayName );
	$last.on( 'input', syncDisplayName );

})(jQuery);

