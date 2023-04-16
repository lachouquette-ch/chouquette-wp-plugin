<?php
/*
 * Copyright (c) 2023 by Fabrice Douchant <fabrice.douchant@gmail.com>.
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

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
