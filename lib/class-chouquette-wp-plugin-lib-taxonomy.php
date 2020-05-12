<?php

/**
 * Helpers for taxonomy.
 *
 * @since        1.0.0
 * @package       Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/lib
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Lib_Taxonomy
{

	/**
	 * Filter query params for chouquette taxonomy.
	 *
	 * @param array $queryParams the query params (usually $request->get_query_params())
	 * @return array new key/value array with key is taxonomy and value is array of terms
	 */
	public static function chouquette_taxonomy_query_filter(array $queryParams)
	{

		return array_filter($queryParams, function ($key) {
			return substr_compare($key, 'cq_', 0, 3) == false;
		}, ARRAY_FILTER_USE_KEY);

	}

}
