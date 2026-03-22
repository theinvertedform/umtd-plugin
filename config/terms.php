<?php
/**
 * Base controlled vocabulary for umt-studio.
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
 * @package umt-studio
 * @see umt-studio.php umtd_seed_terms()
 * @see umt-studio-{client}/config/terms.php
 */

return array(
	'umtd_work_type' => array(
		'300041273' => 'Print',
		'300028051' => 'Artist Book',
		'300033618' => 'Painting',
		'300046300' => 'Photograph',
		'300047090' => 'Sculpture',
		'300136900' => 'Film',
		'300033973' => 'Drawing',
		'300047896' => 'Installation',
		'300069200' => 'Performance',
		'300028682' => 'Video',
	),
);

