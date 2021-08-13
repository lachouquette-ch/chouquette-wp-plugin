<?php

/**
 * Helpers for fiche.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/lib
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Lib_Fiche
{

	const POST_FICHE_FIELD = 'link_fiche';
	const CHOUQUETTISE_TO = 'chouquettise_to';
	const CHOUQUETTISE_FROM = 'chouquettise_from';

	/**
	 * Get all fiches for given post
	 *
	 * @param int|WP_Post|null $post Post ID or post object of null to get globa $post
	 *
	 * @return array of posts. Empty array if none.
	 */
	public static function get_all_by_post($post)
	{

		$fiches = get_field(self::POST_FICHE_FIELD, $post);

		if (!$fiches) {
			return [];
		} elseif (!is_array($fiches)) {
			return array($fiches);
		} else {
			return $fiches;
		}

	}

	/**
	 * Return if fiche is chouquettise
	 *
	 * @param $fiche_id the id of the fiche
	 *
	 * @return true of false if the fiche is chouquettise
	 *
	 * @throws Exception
	 */
	public static function is_chouquettise(int $fiche_id)
	{
		$field = get_field(self::CHOUQUETTISE_TO, $fiche_id);
		if (!$field) {
			return false;
		}

		$chouquettise_to = DateTime::createFromFormat('d/m/Y', $field);
		return $chouquettise_to >= new DateTime();
	}

}
