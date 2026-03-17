<?php
return array(

	'umtd_events'		=> array(
        'enabled'		=> true,
        'plural'		=> 'Events',
        'singular'		=> 'Event',
		'slug'			=> 'events',
		'supports'		=> array( 'title', 'thumbnail' ),
		//'show_in_menu'	=> 'umtd-events',
    ),

    'umtd_works'		=> array(
        'enabled'   	=> true,
        'plural'		=> 'Works',
        'singular'		=> 'Work',
        'slug'			=> 'works',
		'supports'		=> array( 'title', 'thumbnail' ),
		//'show_in_menu'  => 'umtd-works',
    ),

    'umtd_agents'		=> array(
        'enabled'		=> true,
        'plural'		=> 'Agents',
        'singular'		=> 'Agent',
		'slug'			=> 'agents',
		'supports'		=> array( 'title', 'thumbnail' ),
		//'show_in_menu'  => 'umtd-agents',
    ),
);
