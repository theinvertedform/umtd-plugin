<?php
/**
 * Schema.org JSON-LD output.
 *
 * @package umt-studio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', 'umtd_output_visual_artwork_schema' );

/**
 * Outputs JSON-LD Schema.org VisualArtwork markup for umtd_works singles.
 */
function umtd_output_visual_artwork_schema() {
	if ( ! is_singular( 'umtd_works' ) ) {
		return;
	}

	$post_id = get_the_ID();

	$schema = [
		'@context'    => 'https://schema.org',
		'@type'       => 'VisualArtwork',
		'name'        => get_the_title( $post_id ),
		'url'         => get_permalink( $post_id ),
		'artMedium'   => get_field( 'medium', $post_id ),
		'dateCreated' => get_field( 'date_display', $post_id ),
	];

	$dimensions = get_field( 'dimensions', $post_id );
	if ( $dimensions ) {
		$schema['size'] = $dimensions;
	}

	$agents = get_field( 'agent', $post_id );
	if ( $agents ) {
		$creators   = [];
		$agent_list = is_array( $agents ) ? $agents : [ $agents ];

		foreach ( $agent_list as $agent ) {
			$agent_id   = is_object( $agent ) ? $agent->ID : $agent;
			$agent_type = get_field( 'agent_type', $agent_id );

			$creator = [
				'@type' => ( 'organization' === $agent_type ) ? 'Organization' : 'Person',
				'name'  => get_the_title( $agent_id ),
			];

			if ( $bio = get_field( 'biography', $agent_id ) ) {
				$creator['description'] = wp_strip_all_tags( $bio );
			}

			if ( $website = get_field( 'website', $agent_id ) ) {
				$creator['url'] = esc_url( $website );
			}

			$same_as = [];
			if ( $wikidata = get_field( 'wikidata_id', $agent_id ) ) {
				$q_id      = ( strpos( $wikidata, 'Q' ) === 0 ) ? $wikidata : 'Q' . $wikidata;
				$same_as[] = 'https://www.wikidata.org/wiki/' . $q_id;
			}
			if ( $ulan = get_field( 'ulan_id', $agent_id ) ) {
				$same_as[] = 'http://vocab.getty.edu/page/ulan/' . $ulan;
			}
			if ( ! empty( $same_as ) ) {
				$creator['sameAs'] = $same_as;
			}

			if ( 'person' === $agent_type ) {
				$creator['birthDate'] = get_field( 'birth_date', $agent_id );
				$creator['deathDate'] = get_field( 'death_date', $agent_id );

				$gender_val = get_field( 'gender', $agent_id );
				if ( $gender_val && 'unknown' !== $gender_val ) {
					$creator['gender'] = ucfirst( $gender_val );
				}

				if ( $pob = get_field( 'place_of_birth', $agent_id ) ) {
					$creator['birthPlace'] = [ '@type' => 'Place', 'name' => $pob ];
				}

				if ( $nationality = get_field( 'country', $agent_id ) ) {
					$creator['nationality'] = [ '@type' => 'Country', 'name' => $nationality ];
				}
			} else {
				$creator['foundingDate']    = get_field( 'founding_date', $agent_id );
				$creator['dissolutionDate'] = get_field( 'dissolution_date', $agent_id );

				if ( $loc = get_field( 'org_location', $agent_id ) ) {
					$creator['location'] = [ '@type' => 'Place', 'name' => $loc ];
				}
			}

			$creators[] = array_filter( $creator );
		}

		$schema['creator'] = ( count( $creators ) === 1 ) ? $creators[0] : $creators;
	}

	$schema = array_filter( $schema );

	echo "\n<script type=\"application/ld+json\">\n";
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	echo "\n</script>\n";
}
