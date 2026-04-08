<?php
/**
 * Custom table definitions for umt-studio.
 *
 * Consumed by umtd_register_tables() on activation and plugins_loaded.
 * Each entry is a dbDelta()-compatible CREATE TABLE statement. dbDelta() is
 * idempotent — it creates missing tables and adds missing columns; it never
 * drops columns or tables.
 *
 * Child plugins modify the active set via the umtd_schema_tables filter:
 *
 *     add_filter( 'umtd_schema_tables', function( $tables ) {
 *         unset( $tables['umtd_events'] );
 *         return $tables;
 *     } );
 *
 * Table creation order follows the dependency graph — vocabulary tables first,
 * entity tables second, junction tables last. dbDelta() does not enforce foreign
 * key constraints; referential integrity is maintained by the application layer.
 *
 * @package umtd-plugin
 * @see umtd_register_tables()
 */

global $wpdb;
$charset = $wpdb->get_charset_collate();

return array(

	// -------------------------------------------------------------------------
	// Vocabulary tables — no dependencies
	// -------------------------------------------------------------------------

	'umtd_roles' => "CREATE TABLE {$wpdb->prefix}umtd_roles (
		id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		slug        VARCHAR(100)    NOT NULL,
		label_en    VARCHAR(255)    NOT NULL,
		label_fr    VARCHAR(255)    DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY slug (slug)
	) $charset;",

	'umtd_view_types' => "CREATE TABLE {$wpdb->prefix}umtd_view_types (
		id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		slug        VARCHAR(100)    NOT NULL,
		label_en    VARCHAR(255)    NOT NULL,
		label_fr    VARCHAR(255)    DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY slug (slug)
	) $charset;",

	// -------------------------------------------------------------------------
	// Entity tables — keyed to wp_posts.ID via post_id
	// -------------------------------------------------------------------------

	'umtd_works' => "CREATE TABLE {$wpdb->prefix}umtd_works (
		id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		post_id             BIGINT UNSIGNED NOT NULL,
		accession_number    VARCHAR(100)    DEFAULT NULL,
		date_earliest       VARCHAR(8)      DEFAULT NULL,
		date_latest         VARCHAR(8)      DEFAULT NULL,
		date_display        VARCHAR(255)    DEFAULT NULL,
		dimensions_h        DECIMAL(10,2)   DEFAULT NULL,
		dimensions_w        DECIMAL(10,2)   DEFAULT NULL,
		dimensions_unit     VARCHAR(10)     DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY post_id (post_id),
		KEY date_earliest (date_earliest),
		KEY date_latest (date_latest)
	) $charset;",

	'umtd_agents' => "CREATE TABLE {$wpdb->prefix}umtd_agents (
		id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		post_id             BIGINT UNSIGNED NOT NULL,
		agent_type          VARCHAR(20)     NOT NULL DEFAULT 'person',
		name_first          VARCHAR(255)    DEFAULT NULL,
		name_last           VARCHAR(255)    DEFAULT NULL,
		name_display        VARCHAR(255)    DEFAULT NULL,
		birth_date          VARCHAR(8)      DEFAULT NULL,
		death_date          VARCHAR(8)      DEFAULT NULL,
		founding_date       VARCHAR(8)      DEFAULT NULL,
		dissolution_date    VARCHAR(8)      DEFAULT NULL,
		wikidata_id         VARCHAR(50)     DEFAULT NULL,
		ulan_id             VARCHAR(50)     DEFAULT NULL,
		website             VARCHAR(2083)   DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY post_id (post_id),
		KEY agent_type (agent_type),
		KEY name_last (name_last)
	) $charset;",

	'umtd_works_film' => "CREATE TABLE {$wpdb->prefix}umtd_works_film (
		work_id             BIGINT UNSIGNED NOT NULL,
		runtime             INT UNSIGNED    DEFAULT NULL,
		film_format         VARCHAR(100)    DEFAULT NULL,
		language            VARCHAR(10)     DEFAULT NULL,
		isan                VARCHAR(100)    DEFAULT NULL,
		country_of_origin   VARCHAR(100)    DEFAULT NULL,
		PRIMARY KEY  (work_id),
		UNIQUE KEY work_id (work_id)
	) $charset;",

	'umtd_events' => "CREATE TABLE {$wpdb->prefix}umtd_events (
		id                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		post_id                 BIGINT UNSIGNED NOT NULL,
		start_date              VARCHAR(8)      DEFAULT NULL,
		end_date                VARCHAR(8)      DEFAULT NULL,
		organizing_agent_id     BIGINT UNSIGNED DEFAULT NULL,
		venue_id                BIGINT UNSIGNED DEFAULT NULL,
		event_link              VARCHAR(2083)   DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY post_id (post_id),
		KEY start_date (start_date),
		KEY end_date (end_date),
		KEY organizing_agent_id (organizing_agent_id),
		KEY venue_id (venue_id)
	) $charset;",

	// -------------------------------------------------------------------------
	// Junction tables — require entity tables and umtd_roles
	// -------------------------------------------------------------------------

	'umtd_work_agents' => "CREATE TABLE {$wpdb->prefix}umtd_work_agents (
		work_id     BIGINT UNSIGNED NOT NULL,
		agent_id    BIGINT UNSIGNED NOT NULL,
		role_id     BIGINT UNSIGNED NOT NULL,
		sort_order  INT UNSIGNED    NOT NULL DEFAULT 0,
		PRIMARY KEY  (work_id, agent_id, role_id),
		KEY agent_id (agent_id),
		KEY role_id (role_id)
	) $charset;",

	'umtd_event_agents' => "CREATE TABLE {$wpdb->prefix}umtd_event_agents (
		event_id    BIGINT UNSIGNED NOT NULL,
		agent_id    BIGINT UNSIGNED NOT NULL,
		role_id     BIGINT UNSIGNED NOT NULL,
		PRIMARY KEY  (event_id, agent_id, role_id),
		KEY agent_id (agent_id)
	) $charset;",

	'umtd_event_works' => "CREATE TABLE {$wpdb->prefix}umtd_event_works (
		event_id    BIGINT UNSIGNED NOT NULL,
		work_id     BIGINT UNSIGNED NOT NULL,
		PRIMARY KEY  (event_id, work_id)
	) $charset;",

	'umtd_work_media' => "CREATE TABLE {$wpdb->prefix}umtd_work_media (
		work_id         BIGINT UNSIGNED NOT NULL,
		attachment_id   BIGINT UNSIGNED NOT NULL,
		view_type_id    BIGINT UNSIGNED DEFAULT NULL,
		sort_order      INT UNSIGNED    NOT NULL DEFAULT 0,
		PRIMARY KEY  (work_id, attachment_id),
		KEY attachment_id (attachment_id),
		KEY view_type_id (view_type_id)
	) $charset;",

	// -------------------------------------------------------------------------
	// Translation table
	// -------------------------------------------------------------------------

	'umtd_translations' => "CREATE TABLE {$wpdb->prefix}umtd_translations (
		id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		entity_type     VARCHAR(50)     NOT NULL,
		entity_id       BIGINT UNSIGNED NOT NULL,
		field_name      VARCHAR(100)    NOT NULL,
		lang            VARCHAR(10)     NOT NULL,
		value           LONGTEXT        DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY entity_field_lang (entity_type, entity_id, field_name, lang),
		KEY entity_type_id (entity_type, entity_id)
	) $charset;",

);

