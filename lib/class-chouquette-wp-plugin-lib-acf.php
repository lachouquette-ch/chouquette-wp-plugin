<?php

/**
 * Helpers for ACF.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/lib
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Lib_ACF
{

	const ACF_FIELD_GROUP_TYPE = 'group';
	const ACF_FIELD_TAXONOMY_TYPE = 'taxonomy';

	const FICHE_SELECTOR = 'link_fiche';

	/**
	 * Get values for each acf fields. Also convert fields to proper numeric values
	 *
	 * @param array $fields the fields to retrieve
	 * @param $post_id the post_id (or other object id)
	 * @return array field name as key and field value as value
	 */
	public static function get_values(array $fields, $post_id)
	{
		$result = array();

		foreach ($fields as $field) {
			$value = get_field($field, $post_id);

			if (!empty($value))
				$result[$field] = $value;
		}

		// set proper numberic types
		array_walk_recursive($result, function (&$item, $key) {
			if (is_numeric($item))
				if (ctype_digit($item))
					$item = intval($item);
				else
					$item = floatval($item);
		});

		return $result;
	}

	/**
	 * Get ACF field object by field name without using post id.
	 * Works also with sub-groups
	 *
	 * @param string $name the field name (can be category name, ...)
	 * @return the field object (using get_field_object method)
	 */
	public static function get_field_object(string $name)
	{
		global $wpdb;
		$field_keys = $wpdb->get_col($wpdb->prepare("
            SELECT  p.post_name
            FROM    $wpdb->posts p
            WHERE   p.post_type = 'acf-field'
            AND     p.post_excerpt = %s;
        ", $name));

		return array_map(function ($field_key) {
			return get_field_object($field_key);
		}, $field_keys);
	}

	/**
	 * Get all taxonomy fields for a given field name
	 *
	 * @param $field the field name
	 * @return array all taxonomy fields
	 */
	public static function get_taxonomy_fields($field)
	{
		switch ($field['type']) {
			case self::ACF_FIELD_TAXONOMY_TYPE:
				return array($field);
			case self::ACF_FIELD_GROUP_TYPE:
				return array_filter($field['sub_fields'], function ($sub_field) {
					return $sub_field['type'] === self::ACF_FIELD_TAXONOMY_TYPE;
				});
			default:
				return [];
		}
	}

}
