<?php

/**
 * Helpers for Post.
 *
 * @since        1.0.0
 * @package       Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/elasticsearch/lib
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Elasticsearch_Post
{

    const INDEX = 'wp-posts';

    /**
     * Build elasticsearch DTO from Fiche
     *
     * @param WP_Post $post the fiche
     * @return array DTO as an array key=>value
     */
    public static function build_dto(WP_Post $post)
    {
        $categories = Chouquette_WP_Plugin_Rest_Category::get_by_post($post->ID);

        if ($categories) {
            $categories_dto = array_map(function ($category) {
                return array('id' => $category->term_id, 'slug' => $category->slug, 'name' => $category->name);
            }, $categories);
        } else {
            $categories_dto = null;
        }

        $tags = get_the_tags($post->ID);
        if ($tags) {
            $tags_dto = array_map(function ($tag) {
                return array('id' => $tag->term_id, 'slug' => $tag->slug, 'name' => $tag->name);
            }, $tags);
        } else {
            $tags_dto = null;
        }

        $result = [
            'date' => $post->post_date,
            'content' => $post->post_content,
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'comment_count' => $post->comment_count,

            'categories' => $categories_dto,
            'tags' => $tags_dto,

            'featured_media' => get_the_post_thumbnail_url($post->ID, "medium"),
            'logo' => Chouquette_WP_Plugin_Rest_Category::get_logo($categories[0], 'black'),

            'yoast_focus_kw' => get_post_meta($post->ID, '_yoast_wpseo_focuskw'),
            'yoast_meta_desc' => get_post_meta($post->ID, '_yoast_wpseo_metadesc'),
        ];

        return $result;
    }

}
