<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/includes
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Chouquette_WP_Plugin_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('CHOUQUETTE_WP_PLUGIN_VERSION')) {
			$this->version = CHOUQUETTE_WP_PLUGIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'chouquette-wp-plugin';

		$this->load_dependencies();
		$this->define_config_hooks();
		$this->define_register_hooks();
		$this->define_rest_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-chouquette-wp-plugin-loader.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'lib/class-chouquette-wp-plugin-lib.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'config/class-chouquette-wp-plugin-config.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'register/class-chouquette-wp-plugin-register.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'rest/class-chouquette-wp-plugin-rest.php';

		$this->loader = new Chouquette_WP_Plugin_Loader();

	}

	/**
	 * Register all of the hooks related to the configuration functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_config_hooks()
	{

		$plugin_config = new Chouquette_WP_Plugin_Config($this->get_plugin_name(), $this->get_version());

		$this->loader->add_filter('acf/fields/google_map/api', $plugin_config, 'acf_fields_google_map_api');

		$this->loader->add_filter('post_link', $plugin_config, 'change_domain_link', 10, 2);

		$this->loader->add_filter('page_link', $plugin_config, 'change_domain_link', 10, 2);

		$this->loader->add_filter('post_type_link', $plugin_config, 'change_domain_link', 10, 2);

		$this->loader->add_filter('preview_post_link', $plugin_config, 'preview_post_link', 10, 2);

		$this->loader->add_action('admin_footer', $plugin_config, 'fix_preview_link_on_draft');

        // remove current filter
		remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

		$this->loader->add_action('rest_pre_serve_request', $plugin_config, 'add_cors_http_headers', 15);

	}

	/**
	 * Register all of the hooks related to the rest functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_rest_hooks()
	{

		$plugin_rest = new Chouquette_WP_Plugin_Rest($this->get_plugin_name(), $this->get_version());

		$plugin_rest->register_meta();

		$this->loader->add_filter('rest_pre_insert_comment', $plugin_rest, 'validate_comment_recaptcha', 10, 2);

		$this->loader->add_filter('rest_fiche_query', $plugin_rest, 'fiche_chouquettise_filter', 10, 2);

		$this->loader->add_filter('rest_fiche_query', $plugin_rest, 'fiche_category_criteria_filter', 10, 2);

		$this->loader->add_action('rest_prepare_fiche', $plugin_rest, 'fiche_criteria_link', 10, 1);

		$this->loader->add_action('rest_api_init', $plugin_rest, 'post_top_categories');

		$this->loader->add_action('rest_api_init', $plugin_rest, 'category_logos');

		$this->loader->add_action('rest_api_init', $plugin_rest, 'fiche_info');

		$this->loader->add_action('rest_api_init', $plugin_rest, 'fiche_main_category');

		$this->loader->add_action('rest_api_init', $plugin_rest, 'fiche_linked_posts');

		$this->loader->add_action('rest_api_init', $plugin_rest, 'fiche_report');

		$this->loader->add_action('rest_api_init', $plugin_rest, 'fiche_contact');

		$this->loader->add_action('rest_api_init', $plugin_rest, 'register_criteria_routes');

		$this->loader->add_action('rest_api_init', $plugin_rest, 'register_contact_routes');

		$this->loader->add_action('rest_api_init', $plugin_rest, 'user_members');

	}

	/**
	 * Register all of the hooks related to the registration functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_register_hooks()
	{

		$plugin_register = new Chouquette_WP_Plugin_Register($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('init', $plugin_register, 'fiche_post_type');

		$this->loader->add_action('init', $plugin_register, 'icon_info_taxonomy');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Chouquette_WP_Plugin_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version()
	{
		return $this->version;
	}

}
