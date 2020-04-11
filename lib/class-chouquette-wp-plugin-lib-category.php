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

	const BAR_RETOS = 'bar-et-restaurant';
	const LOISIRS = 'loisirs';
	const CULTURE = 'culture-future';
	const SHOPPING = 'shopping';
	const SERVICES = 'services';

	const YOAT_PRIMARY_CATEGORY_META_KEY = '_yoast_wpseo_primary_category';

	/**
	 * Gets all categories (including top) for given post (or fiche)
	 *
	 * @param int $id the post/fiche id
	 *
	 * @return array a unique array of top categories
	 */
	public static function get_all_by_post(int $id)
	{
		$categories = self::get_by_post($id);

		$result = array();
		foreach ($categories as $category) {
			$current = array($category->slug => $category);
			while ($category->category_parent) {
				$category = get_category($category->category_parent);
				$current[$category->slug] = $category;
			}
			$current = array_reverse($current);
			$result = array_merge($result, $current);
		}
		return $result;

	}

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
	public static function get_by_post(int $id, int $parent_id = null)
	{
		// get fiche
		$linkFiches = Chouquette_WP_Plugin_Lib_Fiche::get_all_by_post($id);
		if (!empty($linkFiches)) {
			$post_ids = array_column($linkFiches, 'ID');
		} else {
			$post_ids = array($id); // fallback to article if no fiche (ex : tops)
		}

		$categories = get_categories(array(
			'object_ids' => $post_ids,
			'parent' => $parent_id ?: ''
		));

		// get principal category if any
		foreach ($post_ids as $post_id) {
			$principal_category_id = get_post_meta($post_id, self::YOAT_PRIMARY_CATEGORY_META_KEY, true);
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
