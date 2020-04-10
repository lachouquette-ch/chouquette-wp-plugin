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

	/**
	 * Get values for each acf fields. Also convert fields to proper numeric values
	 *
	 * @param array $fields the fields to retrieve
	 * @param $post_id the post_id (or other object id)
	 * @return array field name as key and field value as value
	 */
	public static function getValues(array $fields, $post_id)
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

}
