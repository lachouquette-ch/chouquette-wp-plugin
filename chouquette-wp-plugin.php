<?php

/**
 * Chouquette Wordpress Plugin
 *
 * The Plugin provides all necessary custom post types, hooks and rest endpoints for La Chouquette plateform
 * Project structure inspired by https://github.com/DevinVinson/WordPress-Plugin-Boilerplate
 *
 * @since    1.0.0
 * @package Chouquette_WP_Plugin
 *
 * @wordpress-plugin
 * Plugin Name: Chouquette Wordpress REST
 * Description: Plugin to expose Chouquette data using REST. The instance must be pre-initialize with proper register fields and taxonomies
 * Version: 1.0.0
 * Author: Fabrice Douchant <fabrice.douchant@gmail.com>
 * License: La Chouquette all right reserved
 * Copyright: Fabrice Douchant
 * Text Domain: chouquette
 */

// exit if accessed directly
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 */
define('CHOUQUETTE_WP_PLUGIN_VERSION', '1.0.0');

/**
 * Website URL (SPA)
 */
define('CHOUQUETTE_WP_PLUGIN_WEBSITE_URL', 'https://lachouquette.ch');

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
