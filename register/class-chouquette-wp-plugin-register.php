<?php

/**
 * The registration functionality of the plugin.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/register
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Register
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
	 * Register the post type 'fiche'.
	 *
	 * @since    1.0.0
	 */
	public function fiche_post_type()
	{

		$labels = array(
			'name' => _x('Fiches', 'Post Type General Name', 'chouquette'),
			'singular_name' => _x('Fiche', 'Post Type Singular Name', 'chouquette'),
			'menu_name' => __('Fiches', 'chouquette'),
			'name_admin_bar' => __('Fiche', 'chouquette'),
			'parent_item_colon' => __('Fiche parente', 'chouquette'),
			'all_items' => __('Toutes les fiches', 'chouquette'),
			'add_new_item' => __('Ajouter une nouvelle fiche', 'chouquette'),
		);
		$args = array(
			'label' => __('Fiche', 'chouquette'),
			'description' => __('Fiche Chouquette', 'chouquette'),
			'labels' => $labels,
			'supports' => array('title', 'thumbnail', 'editor', 'revisions', 'register-fields'),
			'taxonomies' => array('category', 'post_tag'),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-location',
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'can_export' => true,
			'has_archive' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => true,
			'query_var' => true,
			'capability_type' => 'post',
			'public' => true,
			'show_in_rest' => true,
			'rest_base' => 'fiches'
		);
		register_post_type('fiche', $args);

	}

	/**
	 * Register the taxonomy 'icon-info'.
	 *
	 * @since    1.0.0
	 */
	public function icon_info_taxonomy()
	{

		register_taxonomy(
			'icon-info',
			'fiche',
			array(
				'label' => __('Icone info'),
				'public' => false,
				'show_ui' => true,
				'show_admin_column' => true,
				'rewrite' => false,
				'hierarchical' => true
			)
		);

	}

}
