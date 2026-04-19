
/**
 * @file admin-fields.js
 * @package umtd-plugin
 *
 * Admin field behaviour for umtd-plugin edit screens.
 *
 * Enqueued only on post.php and post-new.php for post types listed in
 * $umtd_admin_script_post_types (see includes/admin.php). Currently active
 * on umtd_agents and umtd_works screens, though the agent name logic below
 * only operates when the agent name fields are present in the DOM.
 *
 * If ACF fields are absent on a given screen, the jQuery selectors return
 * empty objects and event listeners attach to nothing — this is harmless.
 */

(function ($) {

	// --- Agent display name ---
	//
	// Watches name_first and name_last, auto-populates name_display as "First Last".
	//
	// Field keys are hardcoded here and must match the ACF field group JSON.
	// If field keys change after an ACF field group rebuild, these selectors
	// will silently stop working. Cross-reference: ARCHITECTURE.md — Agent Metadata Fields.
	//
	// nameUserEdited is intentionally never reset within a page session — once
	// the editor manually changes name_display, auto-population stays off until
	// the page is reloaded. This prevents the auto-population from overwriting
	// a deliberate editorial choice mid-session.

	const $first   = $('input[name="acf[field_69bb206cb458b]"]');
	const $last    = $('input[name="acf[field_69bb209ab458d]"]');
	const $display = $('input[name="acf[field_69bb22d2064d2]"]');

	let nameUserEdited = false;

	$display.on( 'input', function () {
		nameUserEdited = true;
	});

	/**
	 * Sync name_display from name_first and name_last.
	 *
	 * Produces "First Last", or whichever is present if only one is filled.
	 * No-ops if the editor has manually edited name_display this session.
	 *
	 * Note: name_display is the front-end display value. Post title is set
	 * separately on save to "Last, First" by umtd_sync_agent_title() in
	 * includes/admin.php — these are intentionally different formats.
	 */
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
	// Date auto-population has been removed pending a custom date entry UI.
	// See ROADMAP.md — Custom Date Entry UI.

})(jQuery);
