<?php

/**
 * The elasticsearch functionality of the plugin.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/rest
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Elasticsearch
{

    const NODE_1 = '130.223.16.176:9200';

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
	 * The criteria controller instance
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Chouquette_WP_Plugin_Rest_Criteria $criteria_controller REST Controller for criteria.
	 */
	protected $criteria_controller;

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

        $this->load_dependencies();

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

        require_once plugin_dir_path(dirname(__FILE__)) . 'elasticsearch/lib/_import_.php';

    }

	/**
	 * Create menu to manage elasticsearch export
	 *
	 * @since    1.0.0
	 */
	public function create_menu()
	{

	    function menu_page_callback() {
	        include __DIR__ . '/pages/export-to-elasticsearch.php';
        }

	    add_menu_page(
	        'La Chouquette',
            'La Chouquette',
            'export',
            'lachouquette',
            'menu_page_callback',
            'dashicons-admin-generic'
        );

	}

}
