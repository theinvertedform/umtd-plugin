<?php
/**
 * ACF save intercepts for umt.studio custom tables.
 *
 * Hooks into acf/save_post to redirect field writes from postmeta into custom
 * tables. Runs at priority 20 — after ACF's own save at priority 10 — so
 * get_field() is available to read the just-saved values.
 *
 * Fields intercepted here are written to custom tables. Fields not yet
 * covered by a custom table remain in postmeta and are read via the
 * get_field() fallback in umtd_get_field().
 *
 * Agent relationships (umtd_work_agents, umtd_event_agents) are not yet
 * intercepted — role assignment requires a custom meta box UI. Agents are
 * read from postmeta via get_field() fallback until that is implemented.
 *
 * @package umtd-plugin
 * @see includes/db.php — umtd_get_field() read layer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Intercept ACF save for umtd_works.
 *
 * Writes scalar work fields to wp_umtd_works. Creates the row if it does
 * not exist (INSERT), updates if it does (UPDATE). Uses post_id as the
 * unique key.
 *
 * Fields written: date_earliest, date_latest, date_display, dimensions_h,
 * dimensions_w, dimensions_unit, accession_number.
 *
 * Fields intentionally omitted:
 * - agents — no role assignment UI yet; stays in postmeta
 * - description — goes to umtd_translations when that write layer is built
 * - per-type extension fields — stay in postmeta until extension tables exist
 *
 * @param int $post_id WordPress post ID.
 */
add_action( 'acf/save_post', function( $post_id ) {
    if ( get_post_type( $post_id ) !== 'umtd_works' ) {
        return;
    }

    // Skip autosaves and revisions.
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'umtd_works';

    $data = array(
        'post_id'          => $post_id,
        'date_earliest'    => get_field( 'date_earliest',    $post_id ) ?: null,
        'date_latest'      => get_field( 'date_latest',      $post_id ) ?: null,
        'date_display'     => get_field( 'date_display',     $post_id ) ?: null,
        'dimensions_h'     => get_field( 'dimensions_h',     $post_id ) ?: null,
        'dimensions_w'     => get_field( 'dimensions_w',     $post_id ) ?: null,
        'dimensions_unit'  => get_field( 'dimensions_unit',  $post_id ) ?: null,
        'accession_number' => get_field( 'accession_number', $post_id ) ?: null,
        'edition_size'     => get_field( 'edition_size',     $post_id ) ?: null,
        'printer_copies'   => get_field( 'printer_copies',   $post_id ) ?: null,
    );

    $formats = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' );

    $exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM $table WHERE post_id = %d",
        $post_id
    ) );

    if ( $exists ) {
        $wpdb->update( $table, $data, array( 'post_id' => $post_id ), $formats, array( '%d' ) );
    } else {
        $wpdb->insert( $table, $data, $formats );
    }
}, 20 );

/**
 * Intercept ACF save for umtd_works_film extension fields.
 *
 * Writes Film/Video-specific fields to wp_umtd_works_film. Only runs when
 * the work has a umtd_work_type term of 'film' or 'video'. Bails silently
 * for all other work types.
 *
 * Runs at priority 25 — after the works scalar intercept at priority 20,
 * which ensures the umtd_works row (and its id) exists before this fires.
 *
 * Fields written: runtime, film_format, language, isan, country_of_origin.
 *
 * @param int $post_id WordPress post ID.
 */
add_action( 'acf/save_post', function( $post_id ) {
    if ( get_post_type( $post_id ) !== 'umtd_works' ) {
        return;
    }

    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    $terms = get_the_terms( $post_id, 'umtd_work_type' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return;
    }

    $type_slugs = wp_list_pluck( $terms, 'slug' );
    if ( ! array_intersect( array( 'film', 'video' ), $type_slugs ) ) {
        return;
    }

    $work_id = umtd_get_work_id( $post_id );
    if ( ! $work_id ) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'umtd_works_film';

    $data = array(
        'work_id'           => $work_id,
        'runtime'           => get_field( 'runtime',           $post_id ) ?: null,
        'film_format'       => get_field( 'film_format',       $post_id ) ?: null,
        'language'          => get_field( 'language',          $post_id ) ?: null,
        'isan'              => get_field( 'isan',              $post_id ) ?: null,
        'country_of_origin' => get_field( 'country_of_origin', $post_id ) ?: null,
    );

    $formats = array( '%d', '%d', '%s', '%s', '%s', '%s' );

    $exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT work_id FROM $table WHERE work_id = %d",
        $work_id
    ) );

    if ( $exists ) {
        $wpdb->update( $table, $data, array( 'work_id' => $work_id ), $formats, array( '%d' ) );
    } else {
        $wpdb->insert( $table, $data, $formats );
    }
}, 25 );

/**
 * Intercept ACF save for umtd_agents.
 *
 * Writes scalar agent fields to wp_umtd_agents. Creates or updates row
 * keyed to post_id.
 *
 * Fields written: agent_type, birth_date, death_date, founding_date,
 * dissolution_date, wikidata_id, ulan_id, website.
 *
 * @param int $post_id WordPress post ID.
 */
add_action( 'acf/save_post', function( $post_id ) {
    if ( get_post_type( $post_id ) !== 'umtd_agents' ) {
        return;
    }

    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'umtd_agents';

    $data = array(
        'post_id'          => $post_id,
        'agent_type'       => get_field( 'agent_type',       $post_id ) ?: 'person',
        'name_first'       => get_field( 'name_first',       $post_id ) ?: null,
        'name_last'        => get_field( 'name_last',        $post_id ) ?: null,
        'name_display'     => get_field( 'name_display',     $post_id ) ?: null,
        'birth_date'       => get_field( 'birth_date',       $post_id ) ?: null,
        'death_date'       => get_field( 'death_date',       $post_id ) ?: null,
        'founding_date'    => get_field( 'founding_date',    $post_id ) ?: null,
        'dissolution_date' => get_field( 'dissolution_date', $post_id ) ?: null,
        'wikidata_id'      => get_field( 'wikidata_id',      $post_id ) ?: null,
        'ulan_id'          => get_field( 'ulan_id',          $post_id ) ?: null,
        'website'          => get_field( 'website',          $post_id ) ?: null,
    );

    $formats = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

    $exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM $table WHERE post_id = %d",
        $post_id
    ) );

    if ( $exists ) {
        $wpdb->update( $table, $data, array( 'post_id' => $post_id ), $formats, array( '%d' ) );
    } else {
        $wpdb->insert( $table, $data, $formats );
    }
}, 20 );

/**
 * Intercept ACF save for umtd_events.
 *
 * Writes scalar event fields to wp_umtd_events. Creates or updates row
 * keyed to post_id.
 *
 * Fields written: start_date, end_date, event_link.
 *
 * Fields intentionally omitted:
 * - organizing_agent_id, venue_id — require umtd_agents.id lookup, deferred
 *   until agent save intercept is confirmed stable
 * - participating_agents — junction table, deferred with agent relationships
 * - related_works — junction table, deferred
 *
 * @param int $post_id WordPress post ID.
 */
add_action( 'acf/save_post', function( $post_id ) {
    if ( get_post_type( $post_id ) !== 'umtd_events' ) {
        return;
    }

    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'umtd_events';

    $data = array(
        'post_id'    => $post_id,
        'start_date' => get_field( 'start_date', $post_id ) ?: null,
        'end_date'   => get_field( 'end_date',   $post_id ) ?: null,
        'event_link' => get_field( 'event_link', $post_id ) ?: null,
    );

    $formats = array( '%d', '%s', '%s', '%s' );

    $exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM $table WHERE post_id = %d",
        $post_id
    ) );

    if ( $exists ) {
        $wpdb->update( $table, $data, array( 'post_id' => $post_id ), $formats, array( '%d' ) );
    } else {
        $wpdb->insert( $table, $data, $formats );
    }
}, 20 );

/**
 * Save event–agent rows to umtd_event_agents.
 *
 * Reads organizing_agents and participating_agents from ACF postmeta,
 * resolves to umtd_agents.id, inserts into umtd_event_agents with
 * appropriate role_id. Deletes existing rows before reinserting.
 *
 * Role mapping:
 * - organizing_agents → role slug 'curator'
 * - participating_agents → role slug 'artist'
 *
 * Runs at priority 30 — after the scalar events intercept at priority 20
 * which ensures the umtd_events row exists.
 *
 * @param int $post_id WordPress post ID.
 */
add_action( 'acf/save_post', function( $post_id ) {
    if ( get_post_type( $post_id ) !== 'umtd_events' ) {
        return;
    }
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    global $wpdb;
    $event_id = umtd_get_event_id( $post_id );
    if ( ! $event_id ) {
        return;
    }

    $table = $wpdb->prefix . 'umtd_event_agents';
    $wpdb->delete( $table, array( 'event_id' => $event_id ), array( '%d' ) );

    $role_map = array(
        'organizing_agents'   => 'curator',
        'participating_agents' => 'artist',
    );

    foreach ( $role_map as $field => $role_slug ) {
        $role_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}umtd_roles WHERE slug = %s",
            $role_slug
        ) );
        if ( ! $role_id ) {
            continue;
        }

        $agents = get_field( $field, $post_id );
        if ( empty( $agents ) ) {
            continue;
        }

        foreach ( $agents as $agent_post ) {
            $agent_id = umtd_get_agent_id( $agent_post->ID );
            if ( ! $agent_id ) {
                continue;
            }
            $wpdb->insert( $table, array(
                'event_id' => $event_id,
                'agent_id' => $agent_id,
                'role_id'  => $role_id,
            ), array( '%d', '%d', '%d' ) );
        }
    }
}, 30 );

/**
 * Save event–work rows to umtd_event_works.
 *
 * Reads related_works from ACF postmeta on the event, resolves to
 * umtd_works.id, inserts into umtd_event_works. Deletes existing rows
 * before reinserting.
 *
 * Runs at priority 30 — after scalar events intercept at priority 20.
 *
 * @param int $post_id WordPress post ID.
 */
add_action( 'acf/save_post', function( $post_id ) {
    if ( get_post_type( $post_id ) !== 'umtd_events' ) {
        return;
    }
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    global $wpdb;
    $event_id = umtd_get_event_id( $post_id );
    if ( ! $event_id ) {
        return;
    }

    $table = $wpdb->prefix . 'umtd_event_works';
    $wpdb->delete( $table, array( 'event_id' => $event_id ), array( '%d' ) );

    $works = get_field( 'related_works', $post_id );
    if ( empty( $works ) ) {
        return;
    }

    foreach ( $works as $work_post ) {
        $work_id = umtd_get_work_id( $work_post->ID );
        if ( ! $work_id ) {
            continue;
        }
        $wpdb->insert( $table, array(
            'event_id' => $event_id,
            'work_id'  => $work_id,
        ), array( '%d', '%d' ) );
    }
}, 30 );
