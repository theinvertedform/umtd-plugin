<?php
/**
 * Data access layer for umt.studio custom tables.
 *
 * Two levels of API:
 *
 * Low-level — single field reads:
 *   umtd_get_field()       — reads custom tables first, falls back to get_field()
 *   umtd_get_work_agents() — junction table read for work–agent–role rows
 *   umtd_get_event_works() — junction table read for event–work rows
 *   umtd_get_agent_id()    — FK lookup: wp post ID → umtd_agents.id
 *   umtd_get_work_id()     — FK lookup: wp post ID → umtd_works.id
 *   umtd_get_event_id()    — FK lookup: wp post ID → umtd_events.id
 *   umtd_format_date()     — Ymd → display string
 *
 * High-level — entity data arrays for templates:
 *   umtd_get_work()        — all scalar work fields as keyed array
 *   umtd_get_agent()       — all scalar agent fields as keyed array
 *   umtd_get_event()       — all scalar event fields as keyed array
 *   umtd_get_agent_works() — works list for an agent (call separately, not embedded)
 *
 * Templates use the high-level functions. Save intercepts and the meta box
 * use the low-level FK lookup helpers. umtd_get_field() is the fallback for
 * any field not yet covered by a custom table.
 *
 * get_field() fallback is a developer safety net during active development.
 * It is not a data migration path. Once a field is intercepted on save, the
 * fallback for that field becomes unreachable.
 *
 * @package umtd-plugin
 * @see includes/save.php    — acf/save_post intercept hooks
 * @see includes/metabox.php — agent+role meta box, writes to umtd_work_agents
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
                'name_first',
                'name_last',
                'name_display',
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

/**
 * Get the umtd_agents.id for a given WordPress post ID.
 *
 * Used by save intercepts to resolve the FK before inserting into junction
 * tables. Returns null if no row exists — agent must be saved before any
 * work that references it.
 *
 * @param int $post_id WordPress post ID of the agent.
 * @return int|null
 */
function umtd_get_agent_id( $post_id ) {
    global $wpdb;
    return $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}umtd_agents WHERE post_id = %d",
        $post_id
    ) );
}

/**
 * Get the umtd_works.id for a given WordPress post ID.
 *
 * @param int $post_id WordPress post ID of the work.
 * @return int|null
 */
function umtd_get_work_id( $post_id ) {
    global $wpdb;
    return $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}umtd_works WHERE post_id = %d",
        $post_id
    ) );
}

/**
 * Get the umtd_events.id for a given WordPress post ID.
 *
 * @param int $post_id WordPress post ID of the event.
 * @return int|null
 */
function umtd_get_event_id( $post_id ) {
    global $wpdb;
    return $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}umtd_events WHERE post_id = %d",
        $post_id
    ) );
}

/**
 * Format a Ymd date string for display.
 *
 * Converts the stored Ymd format (e.g. 20250815) to a human-readable string.
 * Passes through any value that cannot be parsed as Ymd — e.g. a plain year
 * string like '2015' will be returned as-is, which is correct for date_display
 * values entered manually.
 *
 * @param string $ymd    Date string in Ymd format.
 * @param string $format PHP date format string. Default 'F j, Y'.
 * @return string
 */
function umtd_format_date( $ymd, $format = 'j F Y' ) {
    if ( ! $ymd ) {
        return '';
    }
    $dt = DateTime::createFromFormat( 'Ymd', $ymd );
    return ( $dt && $dt->format( 'Ymd' ) === $ymd ) ? $dt->format( $format ) : $ymd;
}

/**
 * Retrieve all scalar fields for a work as a keyed array.
 *
 * Intended for use in single and card templates. Centralises all field reads
 * so templates never call get_field() or umtd_get_field() directly.
 *
 * Relational fields (agents, related works) are not included — fetch them
 * separately to avoid N+1 queries in archive contexts:
 *   umtd_get_work_agents( $post_id ) — agent+role rows
 *   get_field( 'related_works', $post_id ) — related works (postmeta)
 *
 * Per-type extension fields for film/video are read from umtd_works_film via
 * a LEFT JOIN. All other per-type extension fields (print, bibliographic,
 * listing, visual object) live in postmeta until v0.3.0 extension tables
 * are implemented.
 *
 * @param int $post_id WordPress post ID of the work.
 * @return array
 */
function umtd_get_work( $post_id ) {
    global $wpdb;

    $work_type_terms = get_the_terms( $post_id, 'umtd_work_type' );
    $work_type_slug  = ( $work_type_terms && ! is_wp_error( $work_type_terms ) )
        ? $work_type_terms[0]->slug
        : null;

    $medium_terms = get_the_terms( $post_id, 'umtd_medium' );

    $date_earliest = umtd_get_field( 'date_earliest', $post_id );
    $date_latest   = umtd_get_field( 'date_latest',   $post_id );

    // Film extension fields — LEFT JOIN so non-film works return nulls cleanly.
    $film = $wpdb->get_row( $wpdb->prepare(
        "SELECT wf.runtime, wf.film_format, wf.language, wf.isan, wf.country_of_origin
         FROM {$wpdb->prefix}umtd_works w
         LEFT JOIN {$wpdb->prefix}umtd_works_film wf ON wf.work_id = w.id
         WHERE w.post_id = %d",
        $post_id
    ) );

    return array(

        // WordPress core
        'title'         => get_the_title( $post_id ),
        'permalink'     => get_permalink( $post_id ),
        'thumbnail_id'  => get_post_thumbnail_id( $post_id ),

        // Taxonomy
        'work_type'      => $work_type_terms ?: array(),
        'work_type_slug' => $work_type_slug,
        'medium'         => ( $medium_terms && ! is_wp_error( $medium_terms ) ) ? $medium_terms : array(),

        // Dates
        'date_display'            => umtd_get_field( 'date_display',  $post_id ),
        'date_earliest'           => $date_earliest,
        'date_earliest_formatted' => umtd_format_date( $date_earliest ),
        'date_latest'             => $date_latest,
        'date_latest_formatted'   => umtd_format_date( $date_latest ),

        // Physical description
        'accession_number' => umtd_get_field( 'accession_number', $post_id ),
        'dimensions_h'     => umtd_get_field( 'dimensions_h',     $post_id ),
        'dimensions_w'     => umtd_get_field( 'dimensions_w',     $post_id ),
        'dimensions_d'     => get_field( 'dimensions_d',          $post_id ),
        'dimensions_unit'  => umtd_get_field( 'dimensions_unit',  $post_id ),

        // Description
        'description' => umtd_get_field( 'description', $post_id ),

        // Per-type — visual object (Painting, Drawing, Sculpture, Photograph, Installation)
        'support'           => get_field( 'support',           $post_id ),
        'inscription'       => get_field( 'inscription',       $post_id ),
        'style_period'      => get_field( 'style_period',      $post_id ),
        'catalogue_raisonne'=> get_field( 'catalogue_raisonne',$post_id ),
        'current_location'  => get_field( 'current_location',  $post_id ),

        // Per-type — print (Print, Photograph)
        'edition_size'   => get_field( 'edition_size',   $post_id ),
        'printer_copies' => get_field( 'printer_copies', $post_id ),
        'print_state'    => get_field( 'print_state',    $post_id ),

        // Per-type — film (Film, Video) — from umtd_works_film
        'runtime'           => $film->runtime           ?? null,
        'film_format'       => $film->film_format       ?? null,
        'language'          => $film->language          ?? null,
        'isan'              => $film->isan              ?? null,
        'country_of_origin' => $film->country_of_origin ?? null,

        // Per-type — bibliographic (Books, Monographs, Articles, Artist Book)
        'isbn'               => get_field( 'isbn',               $post_id ),
        'issn'               => get_field( 'issn',               $post_id ),
        'doi'                => get_field( 'doi',                $post_id ),
        'place_of_publication'=> get_field( 'place_of_publication',$post_id ),
        'edition_number'     => get_field( 'edition_number',     $post_id ),
        'page_count'         => get_field( 'page_count',         $post_id ),
        'journal_title'      => get_field( 'journal_title',      $post_id ),
        'volume'             => get_field( 'volume',             $post_id ),
        'issue'              => get_field( 'issue',              $post_id ),
        'page_range'         => get_field( 'page_range',         $post_id ),

        // Per-type — listing
        'listing_address'  => get_field( 'listing_address',  $post_id ),
        'tenure_type'      => get_field( 'tenure_type',      $post_id ),
        'listing_status'   => get_field( 'listing_status',   $post_id ),
        'floor_area'       => get_field( 'floor_area',       $post_id ),
        'floor_area_unit'  => get_field( 'floor_area_unit',  $post_id ),
        'rooms'            => get_field( 'rooms',            $post_id ),
        'bathrooms'        => get_field( 'bathrooms',        $post_id ),
    );
}

/**
 * Retrieve all scalar fields for an agent as a keyed array.
 *
 * Does not include the agent's works list — fetch separately to avoid N+1
 * queries in archive contexts:
 *   umtd_get_agent_works( $post_id )
 *
 * @param int $post_id WordPress post ID of the agent.
 * @return array
 */
function umtd_get_agent( $post_id ) {
    $birth_date      = umtd_get_field( 'birth_date',      $post_id );
    $death_date      = umtd_get_field( 'death_date',      $post_id );
    $founding_date   = umtd_get_field( 'founding_date',   $post_id );
    $dissolution_date= umtd_get_field( 'dissolution_date',$post_id );

    return array(

        // WordPress core
        'title'        => get_the_title( $post_id ),
        'permalink'    => get_permalink( $post_id ),
        'thumbnail_id' => get_post_thumbnail_id( $post_id ),

        // Display name — always use this for output, never title
        'name_display' => umtd_get_field( 'name_display', $post_id ),
        'name_first'   => umtd_get_field( 'name_first',   $post_id ),
        'name_last'    => umtd_get_field( 'name_last',    $post_id ),

        // Type
        'agent_type' => umtd_get_field( 'agent_type', $post_id ),

        // Person dates
        'birth_date'            => $birth_date,
        'birth_date_formatted'  => umtd_format_date( $birth_date ),
        'death_date'            => $death_date,
        'death_date_formatted'  => umtd_format_date( $death_date ),

        // Organization dates
        'founding_date'             => $founding_date,
        'founding_date_formatted'   => umtd_format_date( $founding_date ),
        'dissolution_date'          => $dissolution_date,
        'dissolution_date_formatted'=> umtd_format_date( $dissolution_date ),

        // Identifiers and contact
        'wikidata_id' => umtd_get_field( 'wikidata_id', $post_id ),
        'ulan_id'     => umtd_get_field( 'ulan_id',     $post_id ),
        'website'     => umtd_get_field( 'website',     $post_id ),

        // Still in postmeta
        'biography'    => get_field( 'biography',    $post_id ),
        'country'      => get_field( 'country',      $post_id ),
        'gender'       => get_field( 'gender',       $post_id ),
        'org_location' => get_field( 'org_location', $post_id ),
        'parent_org'   => get_field( 'parent_org',   $post_id ),
    );
}

/**
 * Retrieve all works for an agent as an array of post IDs.
 *
 * Separate from umtd_get_agent() to avoid N+1 queries on archive pages.
 * Call only in single-agent context or when the works list is explicitly needed.
 *
 * @param int $post_id WordPress post ID of the agent.
 * @return int[] Array of work post IDs.
 */
function umtd_get_agent_works( $post_id ) {
    global $wpdb;

    $agent_id = umtd_get_agent_id( $post_id );
    if ( ! $agent_id ) {
        return array();
    }

    return $wpdb->get_col( $wpdb->prepare(
        "SELECT w.post_id
         FROM {$wpdb->prefix}umtd_work_agents wa
         JOIN {$wpdb->prefix}umtd_works w ON w.id = wa.work_id
         WHERE wa.agent_id = %d
         ORDER BY wa.sort_order ASC",
        $agent_id
    ) );
}

/**
 * Retrieve all scalar fields for an event as a keyed array.
 *
 * Does not include organizing agents, participating agents, or related works —
 * fetch separately to avoid N+1 queries in archive contexts:
 *   get_field( 'organizing_agents',   $post_id ) — returns WP_Post objects
 *   get_field( 'participating_agents',$post_id ) — returns WP_Post objects
 *   umtd_get_event_works( $post_id )              — junction table
 *
 * @param int $post_id WordPress post ID of the event.
 * @return array
 */
function umtd_get_event( $post_id ) {
    $start_date = umtd_get_field( 'start_date', $post_id );
    $end_date   = umtd_get_field( 'end_date',   $post_id );

    $event_type_term = get_field( 'event_type', $post_id );

    return array(

        // WordPress core
        'title'        => get_the_title( $post_id ),
        'permalink'    => get_permalink( $post_id ),
        'thumbnail_id' => get_post_thumbnail_id( $post_id ),

        // Dates
        'start_date'           => $start_date,
        'start_date_formatted' => umtd_format_date( $start_date ),
        'end_date'             => $end_date,
        'end_date_formatted'   => umtd_format_date( $end_date ),

        // Type — WP_Term object; use ['event_type']->name for display
        'event_type'      => $event_type_term,
        'event_type_name' => $event_type_term ? $event_type_term->name : null,

        // Scalar fields
        'event_link'  => umtd_get_field( 'event_link',  $post_id ),
        'description' => umtd_get_field( 'description', $post_id ),

        // Location — WP_Post object (venue agent); still in postmeta
        'location' => get_field( 'location', $post_id ),
    );
}

/**
 * Retrieve agent post IDs associated with works of a given work type.
 *
 * Queries umtd_work_agents joined to umtd_works and WordPress taxonomy tables.
 * Returns distinct agent post IDs, optionally filtered by role slug.
 *
 * Used by type-specific archive templates to build agent lists without looping
 * through individual works and their agent arrays.
 *
 * @param string      $type_slug Work type term slug — e.g. 'print', 'books'.
 * @param string|null $role_slug Optional. Role slug — e.g. 'author', 'artist'.
 * @return int[]                 Array of agent WordPress post IDs.
 */
function umtd_get_agents_by_work_type( $type_slug, $role_slug = null ) {
    global $wpdb;

    $role_join  = '';
    $role_where = '';

    if ( $role_slug ) {
        $role_join  = "JOIN {$wpdb->prefix}umtd_roles r ON r.id = wa.role_id";
        $role_where = $wpdb->prepare( "AND r.slug = %s", $role_slug );
    }

    return $wpdb->get_col( $wpdb->prepare(
        "SELECT DISTINCT a.post_id
         FROM {$wpdb->prefix}umtd_work_agents wa
         JOIN {$wpdb->prefix}umtd_agents a ON a.id = wa.agent_id
         JOIN {$wpdb->prefix}umtd_works  w ON w.id = wa.work_id
         $role_join
         JOIN {$wpdb->term_relationships} tr ON tr.object_id       = w.post_id
         JOIN {$wpdb->term_taxonomy}      tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
         JOIN {$wpdb->terms}              t  ON t.term_id           = tt.term_id
         WHERE t.slug = %s
           AND tt.taxonomy = 'umtd_work_type'
           $role_where",
        $type_slug
    ) );
}

