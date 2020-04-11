<?php

/**
 * Helpers for category.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/lib
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Lib_Category
{

	const CQ_CATEGORY_BAR_RETOS = 'bar-et-restaurant';
	const CQ_CATEGORY_LOISIRS = 'loisirs';
	const CQ_CATEGORY_CULTURE = 'culture-future';
	const CQ_CATEGORY_SHOPPING = 'shopping';
	const CQ_CATEGORY_SERVICES = 'services';

	public static $yoast_primary_category_meta = '_yoast_wpseo_primary_category';

	/**
	 * Gets all categories for given post or related fiches. First is primary (if exists).
	 *
	 * First try with fiches then fallback to post (if given as parameter).
	 *
	 * @param int $id the post/fiche id
	 * @param int $parent_id the parent id to limit the search. Default false : does not filter by parent
	 *
	 * @return array a unique array of categories
	 */
	public static function get_all_by_post(int $id, int $parent_id = null)
	{
		// get fiche
		$linkFiches = Chouquette_WP_Plugin_Lib_Fiche::get_all_by_post($id);
		if (!empty($linkFiches)) {
			$taxonomy_ids = array_column($linkFiches, 'ID');
		} else {
			$taxonomy_ids = array($id); // fallback to article if no fiche (ex : tops)
		}

		$categories = get_categories(array(
			'object_ids' => $taxonomy_ids,
			'parent' => $parent_id ?: ''
		));

		// get principal category if any
		foreach ($taxonomy_ids as $taxonomy_id) {
			$principal_category_id = get_post_meta($taxonomy_id, self::$yoast_primary_category_meta, true);
			if (!$principal_category_id) continue;

			// reorder list (array)
			$new_categories = array();
			foreach ($categories as $category) {
				if ($category->term_id == $principal_category_id) {
					array_unshift($new_categories, $category);
				} else {
					array_push($new_categories, $category);
				}
			}
			$categories = $new_categories;
		}

		return $categories;
	}

}
