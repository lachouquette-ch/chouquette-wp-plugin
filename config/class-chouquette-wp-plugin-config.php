<?php

/**
 * The configuration functionality of the plugin.
 *
 * @since      	1.0.0
 * @package    	Chouquette_WP_Plugin
 * @subpackage 	Chouquette_WP_Plugin/config
 * @author		Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Config {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the google map API key to the acf fields plugin.
	 *
	 * @since    1.0.0
	 */
	public function acf_fields_google_map_api($api) {

		$api['key'] = 'AIzaSyCL4mYyxlnp34tnC57WyrU_63BJhuRoeKI';
		return $api;

	}

}
