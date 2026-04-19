<?php
/**
 * View types vocabulary for work media attachments.
 *
 * Consumed by umtd_seed_view_types() on activation. Skips existing slugs —
 * safe to re-run. Child plugins extend via umtd_view_types filter.
 *
 * View types classify multiple images of the same work (recto/verso, detail,
 * installation view). Stored in umtd_view_types table, referenced from
 * umtd_work_media junction table via view_type_id.
 *
 * @package umtd-plugin
 * @see umtd_seed_view_types()
 */
return array(
    'recto'             => array( 'en' => 'Recto',             'fr' => 'Recto' ),
    'verso'             => array( 'en' => 'Verso',             'fr' => 'Verso' ),
    'detail'            => array( 'en' => 'Detail',            'fr' => 'Détail' ),
    'installation-view' => array( 'en' => 'Installation View', 'fr' => 'Vue d\'installation' ),
    'exhibition-view'   => array( 'en' => 'Exhibition View',   'fr' => 'Vue d\'exposition' ),
    'before-treatment'  => array( 'en' => 'Before Treatment',  'fr' => 'Avant traitement' ),
    'after-treatment'   => array( 'en' => 'After Treatment',   'fr' => 'Après traitement' ),
);

