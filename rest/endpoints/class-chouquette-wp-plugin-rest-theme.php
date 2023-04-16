<?php
/*
 * Copyright (c) 2023 by Fabrice Douchant <fabrice.douchant@gmail.com>.
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

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
            'systemText' => get_theme_mod('la_chouquette_system_text')
        );

		return new WP_REST_Response($result);
	}

}
