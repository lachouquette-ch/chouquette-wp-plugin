<?php

/**
 * The rest functionality of the plugin.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/rest
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Rest
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

		require_once plugin_dir_path(dirname(__FILE__)) . 'rest/lib/category.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'rest/lib/fiche.php';

	}

	/**
	 * Register the google map API key to the acf fields plugin.
	 *
	 * @since    1.0.0
	 */
	public function post_top_categories()
	{

		register_rest_field('post', 'topCategories', array(
			'get_callback' => function ($post_arr) {
				$categories = Chouquette_WP_Plugin_Rest_Category::get_all_by_post($post_arr['id']);
				return $categories;
			},

			'schema' => array(
				'description' => __('Get top categories.'),
				'type' => 'Object'
			),
		));

	}

}
