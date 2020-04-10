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

		// allow public comments
		add_filter('rest_allow_anonymous_comments', '__return_true');

	}

	/**
	 * Register existing meta fields to show in rest
	 */
	public function register_meta()
	{

		// link_fiche
		register_meta('post', 'link_fiche', array(
			'single' => true,
			'show_in_rest' => array(
				'schema' => array(
					'type' => 'array',
					'items' => array(
						'type' => 'number',
					),
				),
			),
		));

	}

	/**
	 * Register the google map API key to the acf fields plugin.
	 *
	 * @since    1.0.0
	 */
	public function post_top_categories()
	{

		register_rest_field('post', 'top_categories', array(
			'get_callback' => function ($post_arr) {
				$categories = Chouquette_WP_Plugin_Lib_Category::get_all_by_post($post_arr['id']);

				return array_map(function ($category) {
					return $category->term_id;
				}, $categories);
			},
			'schema' => array(
				'description' => __('Gets all categories from related posts (or from post if none). Primary category (yoast) firsts if any.'),
				'type' => 'array',
				'items' => array(
					'type' => 'integer'
				)
			),
		));

	}

	/**
	 * Add logo fields to categories (though logos attribute).
	 *
	 * @since    1.0.0
	 */
	public function category_logos()
	{

		register_rest_field('category', 'logos', array(
			'get_callback' => function ($comment_arr) {
				$fields = get_field_object('logos', "category_{$comment_arr['id']}");

				return $fields['value'];
			},
			'schema' => array(
				'description' => __('Category logos per color'),
				'type' => 'array',
				'items' => array(
					'type' => 'number'
				)
			),
		));

	}

	/**
	 * Validate the comment with recatpcha v3 token
	 *
	 * @param $prepared_comment array the prepared comment
	 * @param $request WP_REST_Request the request
	 * @return WP_Error if comment is not validated or request is invalid
	 */
	public function validate_comment_recaptcha($prepared_comment, WP_REST_Request $request)
	{
		// validate recaptcha
		if (empty($request['recaptcha'])) {
			return new WP_Error(
				'rest_comment_recaptcha_required',
				__("La presence d'un token recaptcha est nécessaire pour la création d'un commentaire."),
				array('status' => 400)
			);
		}
		try {
			if (!Chouquette_WP_Plugin_Lib_Recaptcha::validateRecaptchaToken($request['recaptcha'])) {
				return new WP_Error(
					'rest_comment_recaptcha_invalid',
					__("Le filtre anti-spam (recaptcha) n'a pas accepté ton commentaire. Merci de re-essayer."),
					array('status' => 412)
				);
			}
		} catch (Chouquette_WP_Plugin_Lib_Recaptcha_Exception $e) {
			return new WP_Error(
				'rest_comment_recaptcha_error',
				$e->getMessage(),
				array('status' => 412)
			);
		}

		return $prepared_comment;
	}

	/**
	 * Add info fields to fiches (though logos attribute).
	 *
	 * @since    1.0.0
	 */
	public function fiche_info()
	{

		register_rest_field('fiche', 'info', array(
			'get_callback' => function ($fiche_arr) {
				$fiche_id = $fiche_arr['id'];

				$fields_basic = ['telephone', 'mail', 'website', 'location', 'cost'];
				$fields_social_networks = ['sn_twitter', 'sn_facebook', 'sn_instagram', 'sn_printerest', 'sn_linkedin'];
				$fields_openings = ['opening_monday', 'opening_tuesday', 'opening_wednesday', 'opening_thursday', 'opening_friday', 'opening_saturday', 'opening_sunday'];

				$fields = $fields_basic;

				if (Chouquette_WP_Plugin_Lib_Fiche::is_chouquettise($fiche_id)) {
					$fields = array_merge($fields, $fields_social_networks, $fields_openings);
				}

				$data = Chouquette_WP_Plugin_Lib_ACF::getValues($fields, $fiche_id);
				// add chouquettise value
				$data['chouquettise'] = Chouquette_WP_Plugin_Lib_Fiche::is_chouquettise($fiche_id);

				return $data;
			},
			'schema' => array(
				'description' => __('Fiche info (some attributes are only show if fiche is \'chouquettisée\''),
				'type' => 'object'
			),
		));

	}

}
