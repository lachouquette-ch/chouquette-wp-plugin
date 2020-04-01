<?php

if (!function_exists('chouquette_google_map_api')):
	function chouquette_google_map_api($api)
	{
		$api['key'] = 'AIzaSyCL4mYyxlnp34tnC57WyrU_63BJhuRoeKI';
		return $api;
	}

	add_filter('acf/fields/google_map/api', 'chouquette_google_map_api');
endif;
