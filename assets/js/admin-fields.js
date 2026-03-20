
(function ($) {

	// --- Agent display name ---
	// Watches name_first and name_last, auto-populates name_display as "First Last".
	// Once the editor manually edits name_display, stops auto-populating.

	const $first   = $('input[name="acf[field_69bb206cb458b]"]');
	const $last    = $('input[name="acf[field_69bb209ab458d]"]');
	const $display = $('input[name="acf[field_69bb22d2064d2]"]');

	let nameUserEdited = false;

	$display.on( 'input', function () {
		nameUserEdited = true;
	});

	function syncDisplayName() {
		if ( nameUserEdited ) {
			return;
		}
		const first = $.trim( $first.val() );
		const last  = $.trim( $last.val() );
		let display = '';
		if ( first && last ) {
			display = first + ' ' + last;
		} else {
			display = first || last;
		}
		$display.val( display );
	}

	$first.on( 'input', syncDisplayName );
	$last.on( 'input', syncDisplayName );

	// --- Date display (deferred) ---
	// Date entry UI will be replaced with a custom implementation.
	// See DEFERRED.md — Custom date entry UI.

})(jQuery);

