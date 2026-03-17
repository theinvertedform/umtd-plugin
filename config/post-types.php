<?php
return array(

	'umtd_events'		=> array(
        'enabled'		=> true,
        'plural'		=> 'Events',
        'singular'		=> 'Event',
		'slug'			=> 'events',
		//'show_in_menu'	=> 'umtd-events',
    ),

    'umtd_works'		=> array(
        'enabled'   	=> true,
        'plural'		=> 'Works',
        'singular'		=> 'Work',
        'slug'			=> 'works',
		//'show_in_menu'  => 'umtd-works',
    ),

    'umtd_agents'		=> array(
        'enabled'		=> true,
        'plural'		=> 'Agents',
        'singular'		=> 'Agent',
		'slug'			=> 'agents',
		'supports'		=> array( 'title' ),
		//'show_in_menu'  => 'umtd-agents',
    ),
);
