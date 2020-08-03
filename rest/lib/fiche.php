<?php

/**
 * Helpers for fiche.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/lib
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Rest_Fiche
{

	const POST_FICHE_FIELD = 'link_fiche';
	const CHOUQUETTISE_TO = 'chouquettise_to';
    const LOCATION = 'location';
    const LOCALISATION = 'cq_localisation';

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
     * Return the chouquettise date for a given fiche
     *
     * @param int $fiche_id the fiche id
     * @return DateTime|null the date or null if none
     */
	public static function get_chouquettise_date(int $fiche_id)
    {
	    $field = get_field(self::CHOUQUETTISE_TO, $fiche_id);
		if (!$field) {
			return null;
		}

        return DateTime::createFromFormat('d/m/Y', $field);
    }

	/**
	 * Return if fiche is chouquettise
	 *
	 * @param $fiche_id the id of the fiche
	 *
	 * @return true of false if the fiche is chouquettise
	 */
	public static function is_chouquettise(int $fiche_id)
	{
		$chouquettise_to = self::get_chouquettise_date($fiche_id);
		if ($chouquettise_to) {
            return $chouquettise_to >= new DateTime();
        } else {
		    return false;
        }
	}

    /**
     * Get all localisations including parents
     *
     * @param int $fiche_id
     * @return array list of terms (localisations)
     */
    public static function get_all_localisation(int $fiche_id)
    {
        $term_id = get_field(self::LOCALISATION, $fiche_id);
        if (!$term_id) {
            return null;
        }

        $result = array();
        do {
            $term = get_term($term_id, Chouquette_WP_Plugin_Rest_Taxonomy::TAXONOMY_LOCATION);
            $result[] = $term;
            $term_id = $term->parent;
        } while ($term_id);

        return $result;
    }

}
