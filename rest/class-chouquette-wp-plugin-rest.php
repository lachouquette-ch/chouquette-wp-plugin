<?php
/*
 * Copyright (c) 2023 by Fabrice Douchant <fabrice.douchant@gmail.com>.
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

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
		require_once plugin_dir_path(dirname(__FILE__)) . 'rest/endpoints/class-chouquette-wp-plugin-rest-contact.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'rest/endpoints/class-chouquette-wp-plugin-rest-theme.php';

		$this->criteria_controller = new Chouquette_WP_Plugin_Rest_Criteria();
		$this->contact_controller = new Chouquette_WP_Plugin_Rest_Contact();
		$this->theme_controller = new Chouquette_WP_Plugin_Rest_Theme();

	}

	/**
	 * Register existing meta fields to show in rest
	 *
	 * @since 1.0.0
	 */
	public function register_meta()
	{

		// link_fiche
		register_meta('post', Chouquette_WP_Plugin_Lib_ACF::FICHE_SELECTOR, array(
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
	 * Register routes for criteria controller
	 *
	 * @since 1.0.0
	 */
	public function register_criteria_routes()
	{

		return $this->criteria_controller->register_routes();

	}

	/**
	 * Register routes for contact controller
	 *
	 * @since 1.0.0
	 */
	public function register_contact_routes()
	{
		return $this->contact_controller->register_routes();
	}

    /**
     * Register routes for theme controller
     *
     * @since 1.0.0
     */
    public function register_theme_routes()
    {
        return $this->theme_controller->register_routes();
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

                $top_categories = array_filter($categories, function($category) {
                    return $category->parent === 0;
                });

                return array_map(function ($category) {
                    return $category->term_id;
                }, array_values($top_categories));
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
     * Register the co-authors fields for posts.
     *
     * @since    1.0.0
     */
    public function post_co_authors()
    {

        register_rest_field('post', 'coauthors', array(
            'get_callback' => function ($post_arr) {
                $result = array();
                $co_authors = new CoAuthorsIterator($post_arr['id']);
                $co_authors->iterate();
                do {
                    $author = array(
                        "id" => $co_authors->current_author->ID,
                        "name" => $co_authors->current_author->display_name,
                        "username" => $co_authors->current_author->user_login,
                        "description" => $co_authors->current_author->description,
                        "avatar" => get_avatar_data( $co_authors->current_author->ID )['url'],
                    );
                    $result[] = $author;
                } while ($co_authors->iterate());

                return $result;
            },
            'schema' => array(
                'description' => __('Get all co authors for post'),
                'type' => 'array',
                'items' => array(
                    'type' => 'Object'
                )
            ),
        ));

    }

	/**
	 * Add logos field to categories
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
     * Add icon link to value with embedded so we can directly fetch icon image
     *
     * @since    1.0.0
     */
    public function category_logo_links($results)
    {
        foreach ($results->data['logos'] as $logoName => $logoId){
            if ($logoId) {
                $results->add_link("{$logoName}", rest_url('/wp/v2/media/' . $logoId), array('embeddable' => true));
            }
        }
        return $results;
    }

    /**
     * Add icon link to value with embedded so we can directly fetch icon image
     *
     * @since    1.0.0
     */
    public function value_icon_link($results)
    {
        $results->add_link('icon', rest_url('/wp/v2/media/' . $results->data['icon']), array('embeddable' => true));

        return $results;
    }

    /**
     * Add icon field to values
     *
     * @since    1.0.0
     */
    public function value_icon()
    {
        register_rest_field('cq_values', 'icon', array(
            'get_callback' => function ($value_arr) {
                $fields = get_field_object('icon', "cq_values_{$value_arr['id']}");
                return $fields['value'];
            },
            'schema' => array(
                'description' => __('Value icon'),
                'type' => 'array',
                'items' => 'number'
            ),
        ));
    }

	/**
	 * Validate the comment with recatpcha v3 token
	 *
	 * @param $prepared_comment array the prepared comment
	 * @param $request WP_REST_Request the request
	 * @return WP_Error if comment is not validated or request is invalid
	 *
	 * @since 1.0.0
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

	/**
	 * Add main category info fiches.
	 *
	 * @since    1.0.0
	 */
	public function fiche_main_category()
	{

		register_rest_field('fiche', 'main_category', array(
			'get_callback' => function ($fiche_arr) {
				$fiche_id = $fiche_arr['id'];

                $category = Chouquette_WP_Plugin_Lib_Category::get_by_post($fiche_id)[0];
				$is_chouquettise = Chouquette_WP_Plugin_Lib_Fiche::is_chouquettise($fiche_id);

				$data = array();
				if ($category) {
                    $data['id'] = $category->term_id;
                    $data['slug'] = $category->slug;
                    $data['name'] = $category->name;
                    $data['marker_icon'] = Chouquette_WP_Plugin_Lib_Category::get_marker_icon($category, $is_chouquettise);
                    $data['logo'] = Chouquette_WP_Plugin_Lib_Category::get_logo($category, 'black');
                }

				return $data;
			},
			'schema' => array(
				'description' => __('Fiche main category info'),
				'type' => 'object'
			),
		));

	}

	/**
	 * Send a report to the site owner
	 *
	 * @since 1.0.0
	 */
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

			if (!$request->has_param('recaptcha') || !$request->has_param('name') || !$request->has_param('email') || !$request->has_param('message')) {
				return new WP_Error(
					'rest_fiche_report_params',
					__("Should contain 'recaptcha', 'name', 'email' and 'message'."),
					array('status' => 400)
				);
			}

            try {
                if (!Chouquette_WP_Plugin_Lib_Recaptcha::validateRecaptchaToken($request->get_param('recaptcha'))) {
                    return new WP_Error(
                        'rest_fiche_recaptcha_invalid',
                        __("Le filtre anti-spam (recaptcha) n'a pas accepté ton message. Merci de re-essayer."),
                        array('status' => 412)
                    );
                }
            } catch (Chouquette_WP_Plugin_Lib_Recaptcha_Exception $e) {
			    return new WP_Error('rest_fiche_recaptcha_error', $e->getMessage(), array('status' => 500));
            }

			$fiche_title = get_the_title($request->get_param('id'));

			$post_type_object = get_post_type_object('fiche');
			$fiche_edit_link = admin_url(sprintf($post_type_object->_edit_link . '&action=edit', $request->get_param('id')));
			$result = Chouquette_WP_Plugin_Lib_Email::send_mail(
				$request->get_param('name'),
				$request->get_param('email'),
				MAIL_FALLBACK,
				'Commentaire sur la fiche ' . $fiche_title,
				$request->get_param('message') . "<br/><a href='${fiche_edit_link}' target='_blank'>Editer la fiche</a>");
			if ($result) {
				return new WP_REST_Response(json_encode(__('Ton message à bien été envoyé à ' . $fiche_title)));
			} else {
				return new WP_Error(
					'rest_fiche_report_send',
					json_encode(__("Ton email n'a pas pu être envoyé. Merci de réessayé plus tard ou de nous contact si l'erreur persiste. On est désolé, snif !")),
					array('status' => 500)
				);
			}
		}

		register_rest_route('wp/v2', '/fiches/(?P<id>\d+)/report', array(
			'methods' => 'POST',
			'callback' => 'send_report',
            'permission_callback' => '__return_true'
		));
	}

	/**
	 * Filter and sorting mechanism for Chouquettisation
	 * Filter : using query param "chouquettise". Values are : only (only Chouquettises) or none (no Chouquettise)
     * Sorting : by default, sorting Chouquettises first. To disable this feature, use the query param "chouquettise_unsort" to "true" (value is not discard)
     *
	 * @since 1.0.0
	 */
	public function fiche_chouquettise_filter_and_sort($args, $request)
	{
	    $sort_by_chouquettisation = true;

        $params = $request->get_params();
        if( isset($params['chouquettise']) ) {
            if (isset ($args['meta_query'])) {
                return new WP_Error(
                    'meta_query_already_set',
                    __("Il existe déjà une meta query pour cette requete"),
                    array('status' => 500)
                );
            }
            switch ($params['chouquettise']) {
                case 'only': // ony chouquettise
                    $args['meta_query'] = array(
                        array(
                            'key' => Chouquette_WP_Plugin_Lib_Fiche::CHOUQUETTISE_TO,
                            'value' => date('Ymd'),
                            'compare' => '>=',
                            'type' => 'DATE'
                        ),
                    );
                    break;
                case 'none': // no chouquettise
                    $args['meta_query'] = array(
                        'relation' => 'OR',
                        array(
                            'key' => Chouquette_WP_Plugin_Lib_Fiche::CHOUQUETTISE_TO,
                            'value' => '',
                            'compare' => '=',
                        ),
                        array(
                            'key' => Chouquette_WP_Plugin_Lib_Fiche::CHOUQUETTISE_TO,
                            'value' => date('Ymd'),
                            'compare' => '<',
                            'type' => 'DATE'
                        ),
                    );
                    $sort_by_chouquettisation = false; // do not sort since won't be queries
                    break;
            }
        }

        // discard sorting if chouquettise_unsort is set
        if ($sort_by_chouquettisation && !isset($params['chouquettise_unsort'])) {
            $args['meta_key'] = Chouquette_WP_Plugin_Lib_Fiche::CHOUQUETTISE_TO;
            $args['meta_type'] = 'DATE';
            $args['orderby'] = 'meta_value date';
            $args['order'] = 'DESC DESC';
        }
//        print_r($args);

		return $args;
	}

    /**
     * Filter all post types using category (with subcategory included)
     *
     * @param $args
     * @param $request
     */
	public function category_and_children_filter($args, $request)
    {
        if ($request['category']) {
            $args['category_name'] = $request['category'];
        }

        return $args;
    }

	/**
	 * Filter fiches using chouquette taxonomies
	 *
	 * @since 1.0.0
	 */
	public function fiche_category_criteria_filter($args, $request)
	{
		$cq_taxonomies = Chouquette_WP_Plugin_Lib_Taxonomy::chouquette_taxonomy_query_filter($request->get_query_params());

        // create new tax_query if needed only
        if ($request['location'] || !empty($cq_taxonomies)) {
            $args['tax_query'] = array('relation' => 'AND');
        }

        // populate with location
        if ($request['location']) {
            $args['tax_query'][] = array(
                'taxonomy' => 'cq_location',
                'field' => 'slug',
                'terms' => explode(',', $request['location'])
            );
        }

        // populate with taxonomies
		if (!empty($cq_taxonomies)) {
			foreach ($cq_taxonomies as $taxonomy => $terms) {
				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'operator' => 'AND',
					'terms' => $terms
				);
			}
		}

		return $args;
	}

	/**
	 * Contact fiche owner
	 *
	 * @since 1.0.0
	 */
	public function fiche_contact()
	{
		function send_contact($request)
		{
			if (!get_post_status($request->get_param('id'))) {
				return new WP_Error(
					'rest_fiche_contact_id',
					__('Fiche does not exist.'),
					array('status' => 404)
				);
			}

			if (!$request->has_param('recaptcha') || !$request->has_param('name') || !$request->has_param('email') || !$request->has_param('message')) {
				return new WP_Error(
					'rest_fiche_contact_params',
					__("Should contain 'recaptcha', 'name', 'email' and 'message'."),
					array('status' => 400)
				);
			}

            try {
                if (!Chouquette_WP_Plugin_Lib_Recaptcha::validateRecaptchaToken($request->get_param('recaptcha'))) {
                    return new WP_Error(
                        'rest_fiche_recaptcha_invalid',
                        __("Le filtre anti-spam (recaptcha) n'a pas accepté ton message. Merci de re-essayer."),
                        array('status' => 412)
                    );
                }
            } catch (Chouquette_WP_Plugin_Lib_Recaptcha_Exception $e) {
                return new WP_Error('rest_fiche_recaptcha_error', $e->getMessage(), array('status' => 500));
            }

            $fiche_email = get_field('mail', $request->get_param('id'));
			if (empty($fiche_email)) {
				return new WP_Error(
					'rest_fiche_contact_mail',
					__('Fiche does not have any contact mail.'),
					array('status' => 400)
				);
			}

			$fiche_title = get_the_title($request->get_param('id'));

			$post_type_object = get_post_type_object('fiche');
			$fiche_edit_link = admin_url(sprintf($post_type_object->_edit_link . '&action=edit', $request->get_param('id')));

			$result = Chouquette_WP_Plugin_Lib_Email::send_mail(
				$request->get_param('name'),
				$request->get_param('email'),
				$fiche_email,
				"Message de {$request->get_param('name')} via lachouquette.ch",
				$request->get_param('message'));
			if ($result) {
				return new WP_REST_Response(__('Email envoyé à ') . $fiche_email);
			} else {
				return new WP_Error(
					'rest_fiche_contact_send',
					__('Ton email n\'a pas pu être envoyé. Merci de réessayé plus tard ou de nous contact si l\'erreur persiste. On est désolé, snif !'),
					array('status' => 500)
				);
			}
		}

		register_rest_route('wp/v2', '/fiches/(?P<id>\d+)/contact', array(
			'methods' => 'POST',
			'callback' => 'send_contact',
            'permission_callback' => '__return_true'
		));
	}

	/**
	 * Contact fiche owner
	 *
	 * @since 1.0.0
	 */
	public function user_members()
	{
		/**
		 * Prepares a single user output for response.
		 *
		 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/rest-api/endpoints/class-wp-rest-users-controller.php
		 */
		function prepare_item_for_response($user, $request)
		{
			$data = array();

			$data['id'] = $user->ID;
			$data['name'] = $user->display_name;
			$data['url'] = $user->user_url;
			$data['description'] = $user->description;
			$data['link'] = get_author_posts_url($user->ID, $user->user_nicename);
			$data['slug'] = $user->user_nicename;
			$data['roles'] = array_values($user->roles);
			$data['registered_date'] = gmdate('c', strtotime($user->user_registered));
			$data['avatar_urls'] = rest_get_avatar_urls($user);
			$data['title'] = get_field(Chouquette_WP_Plugin_Lib_ACF::CQ_USER_ROLE, Chouquette_WP_Plugin_Lib_ACF::generate_post_id($user));

			return $data;
		}


		function get_team_members($request)
		{
			$args = array(
				'role__in' => array('administrator', 'editor', 'author'),
				'orderby' => 'registered'
			);
			$users = get_users($args);

			$data = array();
			foreach ($users as $user) {
				$data[] = prepare_item_for_response($user, $request);
			}

			return $data;
		}

		register_rest_route('wp/v2', '/team', array(
			'methods' => 'GET',
			'callback' => 'get_team_members',
            'permission_callback' => '__return_true'
		));
	}

	/**
	 * Add linked posts for fiche with minimum attributes
	 *
	 * @since    1.0.0
	 */
	public function fiche_linked_posts()
	{

		register_rest_field('fiche', 'linked_posts', array(
			'get_callback' => function ($fiche_arr) {
				$fiche_id = $fiche_arr['id'];

				$exclusif_posts = get_posts(array(
                    'tag__not_in' => array(
                        Chouquette_WP_Plugin_Lib_Tag::get_tag_ID(Chouquette_WP_Plugin_Lib_Tag::$covid),
                        Chouquette_WP_Plugin_Lib_Tag::get_tag_ID(Chouquette_WP_Plugin_Lib_Tag::$tops)
                    ),
					'meta_query' => array(
						array(
							'key' => Chouquette_WP_Plugin_Lib_ACF::FICHE_SELECTOR,
							'value' => '"' . $fiche_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
							'compare' => 'LIKE'
						)
					)
				));

                $top_posts = get_posts(array(
                    'tag__in' => array(
                        Chouquette_WP_Plugin_Lib_Tag::get_tag_ID(Chouquette_WP_Plugin_Lib_Tag::$covid),
                        Chouquette_WP_Plugin_Lib_Tag::get_tag_ID(Chouquette_WP_Plugin_Lib_Tag::$tops)
                    ),
                    'meta_query' => array(
                        array(
                            'key' => Chouquette_WP_Plugin_Lib_ACF::FICHE_SELECTOR,
                            'value' => '"' . $fiche_id . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
                            'compare' => 'LIKE'
                        )
                    )
                ));

                $posts = array_merge($exclusif_posts, $top_posts);

				if (empty($posts))
					return [];

				$result = array();

				foreach ($posts as $post) {
					$data = array();
					$data['id'] = $post->ID;
					$data['slug'] = $post->post_name;
					$data['title'] = $post->post_title;
					$data['date'] = $post->post_date;
					$data['modified'] = $post->post_modified;

					$result[] = $data;
				}

				return $result;
			},

			'schema' => array(
				'description' => __('All linked posts'),
				'type' => 'array',
				'items' => array(
					'type' => 'object'
				)
			),
		));

	}

	/**
	 * Add link to fiche criterias
	 *
	 * @since 1.0.0
	 */
	public function fiche_criteria_link($results)
	{

		$fiche_id = $results->data['id'];
		$results->add_link('criteria', rest_url('/chouquette/v1/criteria/fiche/' . $fiche_id), array('embeddable' => true));

		return $results;

    }

}
