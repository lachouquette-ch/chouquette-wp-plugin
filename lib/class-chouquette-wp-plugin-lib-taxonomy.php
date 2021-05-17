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
	 * @return array new key/value array with key is taxonomy (slug) and value is array of terms (slug)
	 */
	public static function chouquette_taxonomy_query_filter(array $queryParams)
	{

		$result = array();

		if (isset($queryParams['filter'])) {
			foreach ($queryParams['filter'] as $criteria => $terms) {
				$result[$criteria] = explode(',', $terms);
			}
		}

		return $result;

	}

}
