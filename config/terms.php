<?php
/**
 * Base controlled vocabulary for umtd-plugin.
 *
 * Consumed by umtd_seed_terms() on plugin activation. Terms are inserted into
 * their respective taxonomies with the AAT numeric ID stored as 'aat_id' term meta.
 *
 * Term identity is the NAME (array value), not the AAT ID (array key). Renaming
 * a value breaks existing term assignments. AAT IDs are reference metadata only
 * and can be corrected without consequence.
 *
 * Child plugins define a whitelist as a subset of this vocabulary by name.
 * On child plugin activation, terms absent from the whitelist are deleted.
 *
 * Full AAT vocabulary: https://www.getty.edu/research/tools/vocabularies/aat/
 *
 * @package umtd-plugin
 * @see umtd-plugin.php umtd_seed_terms()
 * @see umtd-plugin-{client}/config/terms.php
 */

return array(
	'umtd_work_type'	=> array(
		'300041273' =>	'Prints',
		'300123016' =>	'Artist Books',
		'300033618' =>	'Paintings',
		'300046300' =>	'Photographs',
		'300047090' =>	'Sculptures',
		'300136900' =>	'Films',
		'300033973' =>	'Drawings',
		'300047896' =>	'Installations',
		'300069200' =>	'Performances',
		'300028682' =>	'Videos',
		'300028051' =>	'Books',
		'300060417' =>	'Monographs',
		'300048715'	=>	'Articles',
		'local'		=>	'Listings',
	),

	'umtd_event_type'	=> array(
		'300054766'	=>	'Exhibitions',
    	'300266327'	=>	'Openings',
    	'300069765'	=>	'Workshops',
    	'300121445'	=>	'Performances',
    	'300069101'	=>	'Premieres',
		'300054776'	=>	'Fairs',
		'300112347'	=>	'Markets',
		'300266712'	=>	'Retrospectives',
),

	'umtd_medium'	=> array(
		'300041338'	=>	'Intaglio',
		'300041391'	=>	'Relief',
		'300178376'	=>	'Planographic',
		'300263816'	=>	'35mm',
		'300015050'	=>	'Oil',
		'300015058'	=>	'Acrylic',
	),
);

