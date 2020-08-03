<?php

class Chouquette_WP_Plugin_Rest_Criteria extends WP_REST_Controller
{

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

        $criteria = Chouquette_WP_Plugin_Rest_Taxonomy::fetch_fiche_criteria($request['id']);

        return array_map(function ($criteria) use ($request) {
            $response = $this->prepare_item_for_response($criteria, $request);
            return $response->data;
        }, $criteria);

	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item_for_category($request)
	{

        $criteria = Chouquette_WP_Plugin_Rest_Taxonomy::fetch_category_criteria($request['id']);

        return array_map(function ($criteria) use ($request) {
            $response = $this->prepare_item_for_response($criteria, $request);
            return $response->data;
        }, $criteria);

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
