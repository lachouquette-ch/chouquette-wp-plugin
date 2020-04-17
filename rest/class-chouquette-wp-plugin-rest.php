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

		// allow public comments
		add_filter('rest_allow_anonymous_comments', '__return_true');

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

		require_once plugin_dir_path(dirname(__FILE__)) . 'rest/endpoints/class-chouquette-wp-plugin-rest-criteria.php';

		$this->criteria_controller = new Chouquette_WP_Plugin_Rest_Criteria();

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
	 * Create new instance of criteria controller
	 */
	public function criteria_controller()
	{

		return $this->criteria_controller->register_routes();

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
				$categories = Chouquette_WP_Plugin_Lib_Category::get_by_post($post_arr['id']);

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
				$fields_openings = ['opening_sunday', 'opening_monday', 'opening_tuesday', 'opening_wednesday', 'opening_thursday', 'opening_friday', 'opening_saturday'];

				$data = Chouquette_WP_Plugin_Lib_ACF::get_values($fields_basic, $fiche_id);

				$data['chouquettise'] = Chouquette_WP_Plugin_Lib_Fiche::is_chouquettise($fiche_id);

				if (Chouquette_WP_Plugin_Lib_Fiche::is_chouquettise($fiche_id)) {
					$data = array_merge($data, Chouquette_WP_Plugin_Lib_ACF::get_values($fields_social_networks, $fiche_id));
					// group openings in same attribute and use array index instead of field (starting from 0 : sunday as day week)
					$openings = array_values(Chouquette_WP_Plugin_Lib_ACF::get_values($fields_openings, $fiche_id));
					$data['openings'] = empty($openings) ? null : $openings;
				}

				return $data;
			},
			'schema' => array(
				'description' => __('Fiche info (some attributes are only show if fiche is \'chouquettisée\''),
				'type' => 'object'
			),
		));

	}

	public function fiche_report()
	{
		function send_report($request)
		{
			if (!get_post_status($request->get_param('id'))) {
				return new WP_Error(
					'rest_fiche_report_id',
					__('Fiche does not exist.'),
					array('status' => 404)
				);
			}

			if (!$request->has_param('name') || !$request->has_param('email') || !$request->has_param('message')) {
				return new WP_Error(
					'rest_fiche_report_params',
					__('Should contain name, email and message params.'),
					array('status' => 400)
				);
			}

			$fiche_email = get_field('mail', $request->get_param('id'));
			if (empty($fiche_email)) {
				return new WP_Error(
					'rest_fiche_report_mail',
					__('Fiche does not have any contact mail.'),
					array('status' => 400)
				);
			}

			$fiche_title = get_the_title($request->get_param('id'));

			$post_type_object = get_post_type_object( 'fiche' );
			$fiche_edit_link = admin_url( sprintf( $post_type_object->_edit_link . '&action=edit', $request->get_param('id') ) );
			$result = Chouquette_WP_Plugin_Lib_Email::send_mail(
				$request->get_param('name'),
				$request->get_param('email'),
				MAIL_FALLBACK,
				'Commentaire sur la fiche ' . $fiche_title,
				$request->get_param('message') . "<br/><a href='${fiche_edit_link}' target='_blank'>Editer la fiche</a>");
			if ($result) {
				return new WP_REST_Response(__('Ton message à bien été envoyé à ' . $fiche_title));
			} else {
				return new WP_Error(
					'rest_fiche_report_send',
					__('Ton email n\'a pas pu être envoyé. Merci de réessayé plus tard ou de nous contact si l\'erreur persiste. On ets désolé, snif !'),
					array('status' => 500)
				);
			}
		}

		register_rest_route('wp/v2', '/fiches/(?P<id>\d+)/report', array(
			'methods' => 'POST',
			'callback' => 'send_report'
		));
	}

	/**
	 * Add info fields to fiches (though logos attribute).
	 *
	 * @since    1.0.0
	 */
	public function fiche_latest_post()
	{

		register_rest_field('fiche', 'latest_post', array(
			'get_callback' => function ($fiche_arr) {
				$fiche_id = $fiche_arr['id'];

				$posts = get_posts(array(
					'meta_query' => array(
						array(
							'key' => Chouquette_WP_Plugin_Lib_ACF::FICHE_SELECTOR,
							'value' => '"' . $fiche_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
							'compare' => 'LIKE'
						)
					)
				));

				if (empty($posts))
					return null;

				$lastest_post = array_shift($posts);

				$data = array();
				$data['id'] = $lastest_post->ID;
				$data['slug'] = $lastest_post->post_name;
				$data['title'] = $lastest_post->post_title;
				$data['date'] = $lastest_post->post_date;
				$data['modified'] = $lastest_post->post_modified;

				return $data;
			},
			'schema' => array(
				'description' => __('Latest post'),
				'type' => 'object'
			),
		));

	}

	public function fiche_criteria_link($results)
	{

		$fiche_id = $results->data['id'];
		$results->add_link('criteria', rest_url('/chouquette/v1/criteria/fiche/' . $fiche_id), array('embeddable' => true));

		return $results;

	}

}
