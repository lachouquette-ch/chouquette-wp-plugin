<?php

/**
 * Helpers for tags.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/lib
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Lib_Tag
{

    public static $covid = 'covid-19';
    public static $tops = 'tops';

    /**
     * Get the tag id from its slug
     *
     * @param $tag_slug the slug
     * @return int the tag ID or 0 if not found
     */
    public static function get_tag_ID($tag_slug)
    {
        $tag = get_term_by('name', $tag_slug, 'post_tag');
        if ($tag) {
            return $tag->term_id;
        } else {
            return 0;
        }
    }

}
