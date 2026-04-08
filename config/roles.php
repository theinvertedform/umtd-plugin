<?php
/**
 * Base agent roles vocabulary.
 *
 * Consumed by umtd_seed_roles() on activation. Skips existing slugs — safe to re-run.
 * Child plugins extend via umtd_roles filter.
 *
 * @package umtd-plugin
 */
return array(
    // General
    'artist'				=> array( 'en' => 'Artist',								'fr' => 'Artiste' ),
    'photographer'      	=> array( 'en' => 'Photographer',                    	'fr' => 'Photographe' ),
    'publisher'         	=> array( 'en' => 'Publisher',                       	'fr' => 'Éditeur' ),
    'curator'           	=> array( 'en' => 'Curator',                         	'fr' => 'Commissaire' ),
    // Film
    'director'          	=> array( 'en' => 'Director',                        	'fr' => 'Réalisateur' ),
    'cinematographer'   	=> array( 'en' => 'Cinematographer',                 	'fr' => 'Directeur de la photographie' ),
    'editor'            	=> array( 'en' => 'Editor',                          	'fr' => 'Monteur' ),
    'cast'              	=> array( 'en' => 'Cast',                            	'fr' => 'Distribution' ),
    'producer'          	=> array( 'en' => 'Producer',                        	'fr' => 'Producteur' ),
    'screenwriter'      	=> array( 'en' => 'Screenwriter',                    	'fr' => 'Scénariste' ),
    'composer'          	=> array( 'en' => 'Composer',                        	'fr' => 'Compositeur' ),
    'distributor'			=> array( 'en' => 'Distributor',						'fr' => 'Distributeur' ),
    'production-company'	=> array( 'en' => 'Production Company',					'fr' => 'Entreprise de production' ),
    // Print
    'printer'           	=> array( 'en' => 'Printer',							'fr' => 'Imprimeur' ),
    // Books / articles
    'author'            	=> array( 'en' => 'Author',                          	'fr' => 'Auteur' ),
    'translator'        	=> array( 'en' => 'Translator',                      	'fr' => 'Traducteur' ),
    'illustrator'       	=> array( 'en' => 'Illustrator',                     	'fr' => 'Illustrateur' ),
);
