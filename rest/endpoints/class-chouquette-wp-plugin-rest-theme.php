<?php

class Chouquette_WP_Plugin_Rest_Theme extends WP_REST_Controller
{

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes()
	{
		$version = '1';
		$namespace = 'chouquette/v' . $version;
		$base = 'theme';
		register_rest_route($namespace, '/' . $base, array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'mods'),
                'permission_callback' => '__return_true'
			)
		));
	}

	/**
	 * Get extra mods from Chouquette theme
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function mods($request)
	{
        $result = array(
            'system_text' => get_theme_mod('la_chouquette_system_text')
        );

		return new WP_REST_Response($result);
	}

}
