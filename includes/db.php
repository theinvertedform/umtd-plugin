<?php
/**
 * Data access layer for umt.studio custom tables.
 *
 * Provides umtd_get_field() as the single read interface for all CPT field
 * data. Templates and other plugin code must use this function exclusively —
 * never get_field() or get_post_meta() directly for umtd CPT fields.
 *
 * Read priority:
 *   1. umtd_translations — for translatable fields when $lang is set
 *   2. Custom entity table — umtd_works, umtd_agents, umtd_events
 *   3. get_field() fallback — for fields not yet covered by a custom table
 *
 * The get_field() fallback is a developer safety net during active development.
 * It is not a data migration path. Once a field is intercepted on save, the
 * fallback for that field becomes unreachable.
 *
 * @package umtd-plugin
 * @see includes/save.php — acf/save_post intercept hooks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Map of CPT names to their custom entity table and column definitions.
 *
 * Used by umtd_get_field() to resolve which table owns a given field.
 * Junction table fields (agents, media) are handled separately.
 *
 * @return array
 */
function umtd_get_table_map() {
    global $wpdb;
    return array(
        'umtd_works' => array(
            'table'   => $wpdb->prefix . 'umtd_works',
            'columns' => array(
                'accession_number',
                'date_earliest',
                'date_latest',
                'date_display',
                'dimensions_h',
                'dimensions_w',
                'dimensions_unit',
            ),
        ),
        'umtd_agents' => array(
            'table'   => $wpdb->prefix . 'umtd_agents',
            'columns' => array(
                'agent_type',
                'birth_date',
                'death_date',
                'founding_date',
                'dissolution_date',
                'wikidata_id',
                'ulan_id',
                'website',
            ),
        ),
        'umtd_events' => array(
            'table'   => $wpdb->prefix . 'umtd_events',
            'columns' => array(
                'start_date',
                'end_date',
                'organizing_agent_id',
                'venue_id',
                'event_link',
            ),
        ),
    );
}

/**
 * Retrieve a field value for a post, reading from custom tables first.
 *
 * Checks umtd_translations for translatable fields when $lang is provided or
 * when the current active language differs from the site default. Falls back
 * to the custom entity table, then to get_field() for any field not yet
 * covered by a custom table.
 *
 * For relational fields resolved via junction tables (agents on a work,
 * works on an event), use the dedicated functions:
 *   - umtd_get_work_agents( $post_id )
 *   - umtd_get_event_works( $event_post_id )
 *
 * @param string   $field   ACF field name / custom table column name.
 * @param int      $post_id WordPress post ID.
 * @param string   $lang    Optional. Language code — 'en', 'fr', etc.
 *                          Defaults to current active language.
 * @return mixed            Field value, or null if not found.
 */
function umtd_get_field( $field, $post_id, $lang = null ) {
    global $wpdb;

    $post_type = get_post_type( $post_id );
    $map       = umtd_get_table_map();

    // Translatable fields — check umtd_translations first.
    $translatable = array( 'title', 'description', 'biography' );
    if ( in_array( $field, $translatable, true ) ) {
        $entity_type = str_replace( 'umtd_', '', $post_type ); // 'works' | 'agents' | 'events'
        $lang        = $lang ?? get_query_var( 'lang' ) ?: 'en';
        $value       = $wpdb->get_var( $wpdb->prepare(
            "SELECT value FROM {$wpdb->prefix}umtd_translations
             WHERE entity_type = %s AND entity_id = %d AND field_name = %s AND lang = %s",
            $entity_type, $post_id, $field, $lang
        ) );
        if ( null !== $value ) {
            return $value;
        }
    }

    // Custom entity table — check if field is a column we own.
    if ( isset( $map[ $post_type ] ) ) {
        $def = $map[ $post_type ];
        if ( in_array( $field, $def['columns'], true ) ) {
            $row = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$def['table']} WHERE post_id = %d",
                $post_id
            ) );
            if ( $row && isset( $row->$field ) ) {
                return $row->$field;
            }
            // Row doesn't exist yet — fall through to get_field().
        }
    }

    // Fallback — field not yet covered by a custom table, or no row exists.
    if ( function_exists( 'get_field' ) ) {
        return get_field( $field, $post_id );
    }

    return null;
}

/**
 * Retrieve all agents for a work, with their roles.
 *
 * Reads from umtd_work_agents joined to umtd_agents and umtd_roles.
 * Returns an array of objects with post_id, role_slug, role_label_en,
 * role_label_fr — one row per agent–role pair.
 *
 * Returns an empty array if no rows exist (write intercept not yet run
 * for this post, or no agents assigned).
 *
 * @param int    $work_post_id  WordPress post ID of the work.
 * @param string $lang          Optional. Language for role label. Default 'en'.
 * @return array
 */
function umtd_get_work_agents( $work_post_id, $lang = 'en' ) {
    global $wpdb;

    $label_col = 'label_' . $lang;
    if ( ! in_array( $lang, array( 'en', 'fr' ), true ) ) {
        $label_col = 'label_en';
    }

    return $wpdb->get_results( $wpdb->prepare(
        "SELECT
            wa.sort_order,
            wa.role_id,
            r.slug        AS role_slug,
            r.$label_col  AS role_label,
            a.post_id     AS agent_post_id
         FROM {$wpdb->prefix}umtd_work_agents wa
         JOIN {$wpdb->prefix}umtd_agents a ON a.id = wa.agent_id
         JOIN {$wpdb->prefix}umtd_roles  r ON r.id = wa.role_id
         JOIN {$wpdb->prefix}umtd_works  w ON w.id = wa.work_id
         WHERE w.post_id = %d
         ORDER BY wa.sort_order ASC, r.slug ASC",
        $work_post_id
    ) );
}

/**
 * Retrieve all works for an event.
 *
 * Reads from umtd_event_works joined to umtd_works.
 * Returns an array of objects with work post_id and sort_order.
 *
 * @param int $event_post_id  WordPress post ID of the event.
 * @return array
 */
function umtd_get_event_works( $event_post_id ) {
    global $wpdb;

    return $wpdb->get_results( $wpdb->prepare(
        "SELECT w.post_id AS work_post_id
         FROM {$wpdb->prefix}umtd_event_works ew
         JOIN {$wpdb->prefix}umtd_events e ON e.id = ew.event_id
         JOIN {$wpdb->prefix}umtd_works  w ON w.id = ew.work_id
         WHERE e.post_id = %d",
        $event_post_id
    ) );
}
