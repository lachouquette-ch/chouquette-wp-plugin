<?php

/**
 * The configuration functionality of the plugin.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/config
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Config
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 * @since    1.0.0
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Get google map API key.
	 *
	 * @since    1.0.0
	 */
	public function acf_fields_google_map_api($api)
	{

		$api['key'] = 'AIzaSyCL4mYyxlnp34tnC57WyrU_63BJhuRoeKI';
		return $api;

	}

	/**
	 * Hack preview post link to website
	 *
	 * @since 1.0.0
	 */
	public function preview_post_link($preview_link, $post)
	{

		$nonce = wp_create_nonce('wp_rest');

		return CQ_FRONTEND_DOMAIN . '/preview?type=' . $post->post_type . '&id=' . $post->ID . '&nonce=' . $nonce;

	}

	/**
	 * Add CORS HTTP Header
	 *
	 * @since 1.0.0
	 */
	public function add_cors_http_header()
	{

		header("Access-Control-Allow-Headers: X-WP-Nonce", false);

	}


}
