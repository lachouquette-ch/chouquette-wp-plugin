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
		$items = array(); //do a query, call another class, etc
		$data = array();
		foreach ($items as $item) {
			$itemdata = $this->prepare_item_for_response($item, $request);
			$data[] = $this->prepare_response_for_collection($itemdata);
		}

		return new WP_REST_Response($data, 200);
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item($request)
	{
		//get parameters from request
		$params = $request->get_params();
		$item = array();//do a query, call another class, etc
		$data = $this->prepare_item_for_response($item, $request);

		//return a response or error based on some conditional
		if (1 == 1) {
			return new WP_REST_Response($data, 200);
		} else {
			return new WP_Error('code', __('message', 'text-domain'));
		}
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item_for_fiche($request)
	{
		// get fiche categories
		return get_field('shopping', $request['id']);

		// get all possible criterias

		// get selected criterias values for post

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
	public function get_item_for_category($request)
	{
		$category = get_category($request['id']);

		if (empty($category)) {
			return new WP_Error(
				'chouquette_critieria_category_invalid_id',
				__('Invalid category ID.'),
				array('status' => 404)
			);
		}

		// get upper categories
		$category_ids = array_merge(array($category->term_id), get_ancestors($category->term_id));
		$top_category = get_category(end($categories));

		// get field objects terms
		$criteria_list = array();
		foreach ($category_ids as $category_id) {
			$acf_fields = Chouquette_WP_Plugin_Lib_ACF::get_field_object(get_category($category_id)->slug);
			// might not have any acf field
			if (empty($acf_fields)) {
				continue;
			}
			$the_field = $acf_fields[0]; // get first
			switch ($the_field['type']) {
				case Chouquette_WP_Plugin_Lib_ACF::ACF_FIELD_GROUP_TYPE:
					foreach ($the_field['sub_fields'] as $sub_field) {
						if ($sub_field['type'] == Chouquette_WP_Plugin_Lib_ACF::ACF_FIELD_TAXONOMY_TYPE) {
							$criteria_list[$sub_field['taxonomy']] = $sub_field;
						}
					}
					break;
				case Chouquette_WP_Plugin_Lib_ACF::ACF_FIELD_TAXONOMY_TYPE:
					$criteria_list[$the_field['taxonomy']] = $the_field;
					break;
			}
		}

		// add overall criteria except for services
		if (!empty($top_category) && $top_category->slug != Chouquette_WP_Plugin_Lib_Category::CQ_CATEGORY_SERVICES) {
			$other_field = chouquette_acf_get_field_object(Chouquette_WP_Plugin_Lib_Category::CQ_CATEGORY_SERVICES)[0];
			$criteria_list[Chouquette_WP_Plugin_Lib_Category::CQ_CATEGORY_SERVICES] = $other_field;
		}

		$data = array();

		foreach ($criteria_list as $criteria) {
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
