<?php

/**
 * Helpers for Fiche.
 *
 * @since        1.0.0
 * @package       Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/elasticsearch/lib
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Elasticsearch_Fiche
{

    const INDEX = 'wp-fiches';

    /**
     * Build elasticsearch DTO from Fiche
     *
     * @param WP_Post $fiche the fiche
     * @return array DTO as an array key=>value
     */
    public static function build_dto(WP_Post $fiche)
    {
        if (Chouquette_WP_Plugin_Rest_Fiche::get_chouquettise_date($fiche->ID)) {
            $chouquettise_end = Chouquette_WP_Plugin_Rest_Fiche::get_chouquettise_date($fiche->ID)->format('Y-m-d');
        } else {
            $chouquettise_end = null;
        }

        $localisations = Chouquette_WP_Plugin_Rest_Fiche::get_all_localisation($fiche->ID);
        if ($localisations) {
            $localisations_dto = array_map(function ($localisation) {
                return array('id' => $localisation->term_id, 'slug' => $localisation->slug, 'name' => $localisation->name);
            }, $localisations);
        } else {
            $localisations_dto = null;
        }

        $location = get_field(Chouquette_WP_Plugin_Rest_Fiche::LOCATION, $fiche->ID);
        if ($location) {
            $location_dto = array();
            $location_dto['address'] = $location['address'];
            if ($location['lat'] && $location['lng']) {
                $location_dto['position'] = sprintf("%f,%f", $location['lat'], $location['lng']);
            }
        } else {
            $location_dto = null;
        }

        $categories = Chouquette_WP_Plugin_Rest_Category::get_by_post($fiche->ID);
        $categories_dto = array_map(function ($category) {
            return array('id' => $category->term_id, 'slug' => $category->slug, 'name' => $category->name);
        }, $categories);

        $tags = get_the_tags($fiche->ID);
        if ($tags) {
            $tags_dto = array_map(function ($tag) {
                return array('id' => $tag->term_id, 'slug' => $tag->slug, 'name' => $tag->name);
            }, $tags);
        } else {
            $tags_dto = null;
        }

        $criteria_list = Chouquette_WP_Plugin_Rest_Taxonomy::fetch_fiche_criteria($fiche->ID);
        $criteria_dto = array();
        foreach ($criteria_list as $criteria) {
            foreach ($criteria['values'] as $term) {
                $criteria_dto[] = array(
                    'taxonomy' => $criteria['taxonomy'],
                    'criteria_id' => $criteria['ID'],
                    'criteria_label' => $criteria['label'],
                    'term_id' => $term->term_id,
                    'term_name' => $term->name,
                    'term_slug' => $term->slug,
                    'criteria_term' => $criteria['taxonomy'].'_'.$term->slug
                );
            }
        }

        $result = [
            'id' => $fiche->ID,
            'date' => $fiche->post_date,
            'content' => $fiche->post_content,
            'title' => $fiche->post_title,
            'slug' => $fiche->post_name,
            'chouquettise_end' => $chouquettise_end,
            'location' => $location_dto,

            'localisation' => $localisations_dto,
            'categories' => $categories_dto,
            'tags' => $tags_dto,
            'criteria' => $criteria_dto,

            'featured_media' => get_the_post_thumbnail_url($fiche->ID, "medium"),
            'logo' => $categories ? Chouquette_WP_Plugin_Rest_Category::get_logo($categories[0], 'black') : null,
            'marker_icon' => $categories ? Chouquette_WP_Plugin_Rest_Category::get_marker_icon($categories[0], false) : null,
            'marker_icon_chouquettise' => $categories ? Chouquette_WP_Plugin_Rest_Category::get_marker_icon($categories[0], true) : null,

            'yoast_focus_kw' => get_post_meta($fiche->ID, '_yoast_wpseo_focuskw'),
            'yoast_meta_desc' => get_post_meta($fiche->ID, '_yoast_wpseo_metadesc')
        ];

        return $result;
    }

}
