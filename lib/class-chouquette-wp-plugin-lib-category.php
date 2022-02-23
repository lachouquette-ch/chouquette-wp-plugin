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

	const CQ_CATEGORY_LOGOS = 'logos';
	const CQ_CATEGORY_LOGO_YELLOW = 'logo_yellow';
	const CQ_CATEGORY_LOGO_WHITE = 'logo_white';
	const CQ_CATEGORY_LOGO_BLACK = 'logo_black';
	const CQ_CATEGORY_LOGO_MARKERS = 'marqueurs';
	const CQ_CATEGORY_LOGO_MARKER_YELLOW = 'marker_yellow';
	const CQ_CATEGORY_LOGO_MARKER_WHITE = 'marker_white';

	const YOAT_PRIMARY_CATEGORY_META_KEY = '_yoast_wpseo_primary_category';

	/**
	 * Gets all categories (including top) for given post (or fiche).
     * If primary category exists, return only this one
	 *
	 * @param int $id the post/fiche id
	 *
	 * @return array a unique array of top categories
	 */
	public static function get_all_by_post(int $id)
	{
        // first try to get post primary category
        $yoast_category_id = get_post_meta($id, self::YOAT_PRIMARY_CATEGORY_META_KEY, true);
        $primary_category = get_category($yoast_category_id);
        $categories = $primary_category ? array($primary_category) : get_categories(array('object_ids' => $id));

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
	 * Get the category marker logo
	 *
	 * @param object $category the category
	 * @param boolean $is_chouquettise if the post is chouquettise
	 *
	 * @return the logo URL
	 */
	public static function get_marker_icon(object $category, bool $is_chouquettise)
	{
        $category_markers = get_field(self::CQ_CATEGORY_LOGO_MARKERS, Chouquette_WP_Plugin_Lib_ACF::generate_post_id($category));

		if ($is_chouquettise) {
			$icon_id = $category_markers[self::CQ_CATEGORY_LOGO_MARKER_YELLOW];
		} else {
            $icon_id = $category_markers[self::CQ_CATEGORY_LOGO_MARKER_WHITE];
		}
		return wp_get_attachment_image_src($icon_id, 'full')[0];
	}

	/**
	 * Get the category logo
	 *
	 * @param object $category the category. Should have a 'logo' attribute (array) with the id of the image
	 * @param string $color the color. Only 'white', 'black' or 'yellow'
	 * @param string $size the WP size. Default is thumbnail
	 *
	 * @throws Exception if no color is defined
	 */
	public static function get_logo(object $category, string $color = 'yellow', string $size = 'thumbnail')
	{
	    $category_logos = get_field(self::CQ_CATEGORY_LOGOS, Chouquette_WP_Plugin_Lib_ACF::generate_post_id($category));

		switch ($color) {
			case 'white':
				$logo_id = $category_logos[self::CQ_CATEGORY_LOGO_WHITE];
				break;
			case 'black':
				$logo_id = $category_logos[self::CQ_CATEGORY_LOGO_BLACK];
				break;
			case 'yellow':
				$logo_id = $category_logos[self::CQ_CATEGORY_LOGO_YELLOW];
				break;
			default:
				throw new Exception("$color is undefined");
		}
		return wp_get_attachment_image_src($logo_id, $size)[0];
	}
}
