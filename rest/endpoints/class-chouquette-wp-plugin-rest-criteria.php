<?php

class Chouquette_WP_Plugin_Rest_Criteria extends WP_REST_Controller
{

	const TAXONOMY_LOCATION = 'cq_location';
	const TAXONOMY_CRITERIA = 'cq_criteria';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes()
	{
		$version = '1';
		$namespace = 'chouquette/v' . $version;
		$base = 'criteria';
		register_rest_route($namespace, '/' . $base, array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'get_items'),
				'args' => array(),
			)
		));
		register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'get_item'),
				'args' => array(
					'context' => array(
						'default' => 'view',
					),
				),
			)
		));
		register_rest_route($namespace, '/' . $base . '/fiche' . '/(?P<id>[\d]+)', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'get_item_for_fiche'),
				'args' => array(
					'context' => array(
						'default' => 'view',
					),
				),
			)
		));
		register_rest_route($namespace, '/' . $base . '/category' . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __('Unique identifier for the object.'),
					'type' => 'integer',
				),
			),
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'get_item_for_category'),
				'args' => array(
					'context' => array(
						'default' => 'view',
					),
				),
			)
		));
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items($request)
	{
		return new WP_Error(
			'invalid-method',
			/* translators: %s: Method name. */
			sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
			array( 'status' => 405 )
		);
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item($request)
	{
		return new WP_Error(
			'invalid-method',
			/* translators: %s: Method name. */
			sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
			array( 'status' => 405 )
		);
	}

	private function compute_criteria_list(array $categories)
	{

		$acf_fields = array();
		$top_categories = array();

		// get fields for categories
		foreach ($categories as $category) {
			if ($category->parent === 0) {
				$top_categories[] = $category;
			}
			$acf_fields = array_merge($acf_fields, Chouquette_WP_Plugin_Lib_ACF::get_field_object($category->slug));
		}

		// get overall fields except for services
		$candidate_categories = array_filter($top_categories,
			function ($category) {
				return $category->slug != Chouquette_WP_Plugin_Lib_Category::SERVICES;
			}
		);
		if (!empty($candidate_categories)) {
			$acf_fields = array_merge($acf_fields, Chouquette_WP_Plugin_Lib_ACF::get_field_object(self::TAXONOMY_CRITERIA));
		}

		// compute all criteria for gathered fields

		$criteria_list = array();

		foreach ($acf_fields as $acf_field) {
			$criteria = Chouquette_WP_Plugin_Lib_ACF::get_taxonomy_fields($acf_field);
			$criteria_list = array_merge($criteria_list, $criteria);
		}

		return $criteria_list;

	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item_for_fiche($request)
	{

		$categories = Chouquette_WP_Plugin_Lib_Category::get_all_by_post($request['id']);

		$criteria_list = $this->compute_criteria_list($categories);

		$data = array();

		// get field objects terms
		foreach ($criteria_list as &$criteria) {
			$criteria_terms = get_the_terms($request['id'], $criteria['taxonomy']);

			// do not add criteria with no term selected
			if (empty($criteria_terms))
				continue;

			$criteria['values'] = $criteria_terms;
			$itemdata = $this->prepare_item_for_response($criteria, $request);
			// TODO add links to leverage this call
			$data[] = $this->prepare_response_for_collection($itemdata);
		}

		return $data;

	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item_for_category($request)
	{

		// add ancestors to list of categories
		$category_ids = array_merge(array($request['id']), get_ancestors($request['id'], 'category'));

		$categories = array_map(function ($category_id) {
			return get_category($category_id);
		}, $category_ids);

		if (empty($categories)) {
			return new WP_Error(
				'chouquette_critieria_category_invalid_id',
				__('Invalid category ID.'),
				array('status' => 404)
			);
		}

		$criteria_list = $this->compute_criteria_list($categories);

		$data = array();

		foreach ($criteria_list as &$criteria) {
			$criteria['values'] = get_terms($criteria['taxonomy']);

			$itemdata = $this->prepare_item_for_response($criteria, $request);
			// TODO add links to leverage this call
			$data[] = $this->prepare_response_for_collection($itemdata);
		}

		return $data;

	}

	public function prepare_item_for_response($criteria, $request)
	{
		$GLOBALS['criteria'] = $criteria;

		// Base fields for every post.
		$data = array();

		$data['id'] = $criteria['ID'];
		$data['taxonomy'] = $criteria['name'];
		$data['name'] = wp_specialchars_decode($criteria['label']);

		$data['values'] = array_map(function ($term) {
			$data_term = array();

			$data_term['id'] = $term->term_id;
			$data_term['slug'] = $term->slug;
			$data_term['name'] = wp_specialchars_decode($term->name);
			$data_term['description'] = wp_specialchars_decode($term->description);

			return $data_term;
		}, $criteria['values']);

		// Wrap the data in a response object.
		return rest_ensure_response($data);
	}

}