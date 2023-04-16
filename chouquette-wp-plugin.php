<?php

/*
 * Copyright (c) 2023 by Fabrice Douchant <fabrice.douchant@gmail.com>.
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

// exit if accessed directly
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 */
define('CHOUQUETTE_WP_PLUGIN_VERSION', '2.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_chouquette_wp_plugin()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-chouquette-wp-plugin-activator.php';
	Chouquette_WP_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-chouquette-wp-plugin-deactivator.php
 */
function deactivate_chouquette_wp_plugin()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-chouquette-wp-plugin-deactivator.php';
	Chouquette_WP_Plugin_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_chouquette_wp_plugin');
register_deactivation_hook(__FILE__, 'deactivate_chouquette_wp_plugin');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-chouquette-wp-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_chouquette_wp_plugin()
{

	$plugin = new Chouquette_WP_Plugin();
	$plugin->run();

}

run_chouquette_wp_plugin();
