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
			'args' => array(
				'id' => array(
					'description' => __('Unique identifier for the object.'),
					'type' => 'integer',
					'required' => true
				)
			),
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
		register_rest_route($namespace, '/' . $base . '/fiche', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'get_item_for_fiches'),
				'args' => array(
					'context' => array(
						'default' => 'view',
					),
					'include' => array(
						'type' => 'array',
						'required' => true,
						'description' => "List of fiches ids to retrieve",
						'items' => array(
							'type' => 'integer',
						)
					),
				)
			)
		));
		register_rest_route($namespace, '/' . $base . '/fiche' . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __('Unique identifier for the object.'),
					'type' => 'integer',
					'required' => true
				)
			),
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
					'required' => true
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
			sprintf(__("Method '%s' not implemented. Must be overridden in subclass."), __METHOD__),
			array('status' => 405)
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
			sprintf(__("Method '%s' not implemented. Must be overridden in subclass."), __METHOD__),
			array('status' => 405)
		);
	}

	/**
	 * Compute all criteria for given set a categories
	 *
	 * @param array $categories all categories
	 * @return array of array (single value) with key category id and values all criteria for it
	 */
	private function compute_criteria_list(array $categories)
	{

		$result = array();
		$top_categories = array();

		// get fields for categories
		foreach ($categories as $category) {
			if ($category->parent === 0) {
				$top_categories[] = $category;
			}
			$acf_field = Chouquette_WP_Plugin_Lib_ACF::get_field_object($category->slug);
			// no acf field for category ? (can be...)
			if (empty($acf_field)) {
				continue;
			}

			$taxonomy_fields = Chouquette_WP_Plugin_Lib_ACF::get_taxonomy_fields($acf_field[0]);

			$result[] = array($category->term_id => $taxonomy_fields);
		}

		// get overall fields except for services
		$candidate_categories = array_filter($top_categories,
			function ($category) {
				return $category->slug != Chouquette_WP_Plugin_Lib_Category::SERVICES;
			}
		);
		if (!empty($candidate_categories)) {
			$taxonomy_fields = Chouquette_WP_Plugin_Lib_ACF::get_field_object(self::TAXONOMY_CRITERIA);

			$result[] = array(0 => $taxonomy_fields);
		}

		return $result;

	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item_for_fiches($request)
	{

		$data = array();

		foreach ($request['include'] as $post_id) {
			$data[$post_id] = $this->get_item_for_fiche(array("id" => $post_id));
		}

		return $data;

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

		$category_criteria_list = $this->compute_criteria_list($categories);

		$data = array();

		// loop on all categories
		foreach ($category_criteria_list as $category_criteria) {

			$category_id = key($category_criteria);
			$criteria_list = $category_criteria[$category_id];

			$category_terms = array();

			foreach ($criteria_list as &$criteria) {

				$criteria_terms = get_the_terms($request['id'], $criteria['taxonomy']);
				// do not add criteria with no term selected
				if (empty($criteria_terms)) {
					continue;
				}

				$criteria['values'] = $criteria_terms;

				$prepared_data = $this->prepare_item_for_response($criteria, $request);

				$category_terms[] = $prepared_data->data;

			}

			if (!empty($category_terms)) {
				$data[] = array('category_id' => $category_id, 'criteria' => $this->prepare_response_for_collection($category_terms));
			}

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

		$category = get_category($request['id']);

		// ascend to top category
		$categories = array($category);
		while ($category->category_parent) {
			$category = get_category($category->category_parent);
			array_unshift($categories, $category);
		}

		$category_criteria_list = $this->compute_criteria_list($categories);
		$criteria_list = array_map(function($category_criteria) {
			return array_pop($category_criteria);
		}, $category_criteria_list);
		$criteria_list = array_merge(...$criteria_list);

		$data = array();

		$criteria_indexes = array();
		foreach($criteria_list as &$criteria) {
			if (in_array($criteria['ID'], $criteria_indexes)) {
				continue;
			}

			$criteria_terms = get_terms([
				'taxonomy' => $criteria['taxonomy'],
				'hide_empty' => false,
			]);
			// do not add criteria with no terms
			if (empty($criteria_terms)) {
				continue;
			}

			$criteria['values'] = $criteria_terms;

			$prepared_data = $this->prepare_item_for_response($criteria, $request);

			$data[] = $prepared_data->data;
			$criteria_indexes[] = $criteria['ID'];

		}

		return $this->prepare_response_for_collection($data);

	}

	public function prepare_item_for_response($criteria, $request)
	{
		$GLOBALS['criteria'] = $criteria;

		// Base fields for every post.
		$data = array();

		$data['id'] = $criteria['ID'];
		$data['taxonomy'] = $criteria['taxonomy'];
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
