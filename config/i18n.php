<?php
/**
 * Internationalisation config for umtd-plugin.
 *
 * Defines all supported languages and their slug translations for CPTs and
 * taxonomies. Consumed by umtd_register_post_types() and
 * umtd_register_taxonomies() via the umtd_i18n filter.
 *
 * default_lang sets the slug used in register_post_type() rewrite. Additional
 * languages generate supplementary rewrite rules with a lang query var set.
 *
 * languages is the base default — child plugins override via umtd_i18n filter
 * to declare which languages are active on their install. Languages absent from
 * the active list generate no rewrite rules and produce no routes.
 *
 * Slug translations live here only — not in post-types.php or taxonomies.php.
 * Adding a new CPT: add its slugs here. Adding a new language: add a column
 * here. Child plugins never modify this file.
 *
 * @package umtd-plugin
 * @see umtd-plugin.php umtd_register_post_types()
 * @see umtd-plugin.php umtd_register_taxonomies()
 */

return array(

	'default_lang' => 'en',
	'languages'    => array( 'en' ), // Child plugin overrides via umtd_i18n filter.

	'slugs' => array(

		// CPTs
		'umtd_works'  => array(
			'en' => 'works',
			'fr' => 'oeuvres',
			'es' => 'obras',
			'de' => 'werke',
		),
		'umtd_agents' => array(
			'en' => 'artists',
			'fr' => 'artistes',
			'es' => 'artistas',
			'de' => 'kunstler',
		),
		'umtd_events' => array(
			'en' => 'events',
			'fr' => 'evenements',
			'es' => 'eventos',
			'de' => 'veranstaltungen',
		),

		// Taxonomies
		'umtd_work_type' => array(
			'en' => 'work-type',
			'fr' => 'type-oeuvre',
			'es' => 'tipo-obra',
			'de' => 'werktyp',
		),

		'umtd_event_type' => array(
			'en' => 'event-type',
			'fr' => 'type-evenement',
			'es' => 'tipo-evento',
			'de' => 'veranstaltungstyp',
		),

		'umtd_medium' => array(
			'en' => 'medium',
			'fr' => 'technique',
			'es' => 'tecnica',
			'de' => 'technik',
		),
	),
);
