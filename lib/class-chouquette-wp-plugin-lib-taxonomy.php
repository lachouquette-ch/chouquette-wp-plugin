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

    const TAXONOMY_LOCATION = 'cq_location';
    const TAXONOMY_CRITERIA = 'cq_criteria';

    /**
     * Filter query params for chouquette taxonomy.
     *
     * @param array $queryParams the query params (usually $request->get_query_params())
     * @return array new key/value array with key is taxonomy (slug) and value is array of terms (slug)
     */
    public static function chouquette_taxonomy_query_filter(array $queryParams)
    {

        $result = array();

        if ($queryParams['filter']) {
            foreach ($queryParams['filter'] as $criteria => $terms) {
                $result[$criteria] = explode(',', $terms);
            }
        }

        return $result;

    }

    /**
     * Compute all criteria for given set a categories
     *
     * @param array $categories all categories
     * @return array of array (single value) with key category id and values all criteria for it
     */
    private static function compute_category_criteria(array $categories)
    {

        $result = array();
        $top_categories = array();

        // get fields for categories
        foreach ($categories as $category) {
            if ($category->parent === 0) {
                $top_categories[] = $category;
            }
            $acf_field = Chouquette_WP_Plugin_Lib_ACF::get_field_object($category->slug);
            // no acf field for category ? (can be...)
            if (empty($acf_field)) {
                continue;
            }

            $taxonomy_fields = Chouquette_WP_Plugin_Lib_ACF::get_taxonomy_fields($acf_field[0]);

            $result[] = array($category->term_id => $taxonomy_fields);
        }

        // get overall fields except for services
        $candidate_categories = array_filter($top_categories,
            function ($category) {
                return $category->slug != Chouquette_WP_Plugin_Lib_Category::SERVICES;
            }
        );
        if (!empty($candidate_categories)) {
            $taxonomy_fields = Chouquette_WP_Plugin_Lib_ACF::get_field_object(self::TAXONOMY_CRITERIA);

            $result[] = array(0 => $taxonomy_fields);
        }

        return $result;

    }

    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public static function fetch_fiche_criteria(int $fiche_id)
    {

        $categories = Chouquette_WP_Plugin_Lib_Category::get_all_by_post($fiche_id);

        $category_criteria_list = self::compute_category_criteria($categories);

        $result = array();

        // loop on all categories
        foreach ($category_criteria_list as $category_criteria) {

            $category_id = key($category_criteria);
            $criteria_list = $category_criteria[$category_id];

            foreach ($criteria_list as &$criteria) {

                $criteria_terms = get_the_terms($fiche_id, $criteria['taxonomy']);
                // do not add criteria with no term selected
                if (empty($criteria_terms)) {
                    continue;
                }

                $criteria['values'] = $criteria_terms;

                $result[] = $criteria;

            }

        }

        return $result;

    }

    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public static function fetch_category_criteria($category_id)
    {

        $category = get_category($category_id);

        // ascend to top category
        $categories = array($category);
        while ($category->category_parent) {
            $category = get_category($category->category_parent);
            array_unshift($categories, $category);
        }

        $category_criteria_list = self::compute_category_criteria($categories);
        $criteria_list = array_map(function ($category_criteria) {
            return array_pop($category_criteria);
        }, $category_criteria_list);
        $criteria_list = array_merge(...$criteria_list);

        $result = array();

        $criteria_indexes = array();
        foreach ($criteria_list as &$criteria) {
            if (in_array($criteria['ID'], $criteria_indexes)) {
                continue;
            }

            $criteria_terms = get_terms([
                'taxonomy' => $criteria['taxonomy'],
                'hide_empty' => false,
            ]);
            // do not add criteria with no terms
            if (empty($criteria_terms)) {
                continue;
            }

            $criteria['values'] = $criteria_terms;

            $result[] = $criteria;
            $criteria_indexes[] = $criteria['ID'];

        }

        return $result;

    }

}
