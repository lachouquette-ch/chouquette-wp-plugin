<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Elasticsearch\ClientBuilder;

const NODE_1 = '130.223.16.176:9200';
const POST_INDEX = 'wp-posts';
const FICHE_INDEX = 'wp-fiches';

$client = ClientBuilder::create()->setHosts(array(NODE_1))->build();
?>

<h1>Administration Elasticsearch</h1>

<?php
if (isset($_POST['export-posts'])) {
    $post_query = new WP_Query(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'nopaging' => 'true'
    ));
    if ($post_query->have_posts()) {
        global $post;
        while ($post_query->have_posts()) {
            $post_query->the_post();

            $params['body'][] = [
                'index' => [
                    '_index' => POST_INDEX,
                    '_id' => $post->ID
                ]
            ];

            /* Build DTO */

            $categories = Chouquette_WP_Plugin_Lib_Category::get_by_post($post->ID);
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

            $params['body'][] = [
                'date' => $post->post_date,
                'content' => $post->post_content,
                'title' => $post->post_title,
                'slug' => $post->post_name,
                'comment_count' => $post->comment_count,

                'categories' => $categories_dto,
                'tags' => $tags_dto,

                'featured_media' => get_the_post_thumbnail_url($post->ID, "medium"),
                'logo' => Chouquette_WP_Plugin_Lib_Category::get_logo($categories[0], 'black'),

                'yoast_focus_kw' => get_post_meta($post->ID, '_yoast_wpseo_focuskw'),
                'yoast_meta_desc' => get_post_meta($post->ID, '_yoast_wpseo_metadesc'),
            ];
        }
        $responses = $client->bulk($params);
    }
    $stats = $client->indices()->stats(array('index' => POST_INDEX));
    echo '<p><strong>Result for posts : </strong>' . $stats['_all']['primaries']['docs']['count'] . ' indexed / ' . $post_query->found_posts . ' posts</p>';
} elseif (isset($_POST['export-fiches'])) {
    $fiche_query = new WP_Query(array(
        'post_type' => 'fiche',
        'post_status' => 'publish',
        'nopaging' => 'true',
    ));
    if ($fiche_query->have_posts()) {
        global $post;
        while ($fiche_query->have_posts()) {
            $fiche_query->the_post();

            $params['body'][] = [
                'index' => [
                    '_index' => FICHE_INDEX,
                    '_id' => $post->ID
                ]
            ];

            /* Build DTO */

            if (Chouquette_WP_Plugin_Lib_Fiche::get_chouquettise_date($post->ID)) {
                $chouquettise_end = Chouquette_WP_Plugin_Lib_Fiche::get_chouquettise_date($post->ID)->format('Y-m-d');
            } else {
                $chouquettise_end = null;
            }

            $localisations = Chouquette_WP_Plugin_Lib_Fiche::get_all_localisation($post->ID);
            if ($localisations) {
                $localisations_dto = array_map(function ($localisation) {
                    return array('id' => $localisation->term_id, 'slug' => $localisation->slug, 'name' => $localisation->name);
                }, $localisations);
            } else {
                $localisations_dto = null;
            }

            $location = get_field(Chouquette_WP_Plugin_Lib_Fiche::LOCATION, $post->ID);
            if ($location) {
                $location_dto = array();
                $location_dto['address'] = $location['address'];
                if ($location['lat'] && $location['lng']) {
                    $location_dto['position'] = sprintf("%f,%f", $location['lat'], $location['lng']);
                }
            } else {
                $location_dto = null;
            }

            $categories = Chouquette_WP_Plugin_Lib_Category::get_by_post($post->ID);
            $categories_dto = array_map(function ($category) {
                return array('id' => $category->term_id, 'slug' => $category->slug, 'name' => $category->name);
            }, $categories);

            $tags = get_the_tags($post->ID);
            if ($tags) {
                $tags_dto = array_map(function ($tag) {
                    return array('id' => $tag->term_id, 'slug' => $tag->slug, 'name' => $tag->name);
                }, $tags);
            } else {
                $tags_dto = null;
            }

            $criteria_list = Chouquette_WP_Plugin_Lib_Taxonomy::fetch_fiche_criteria($post->ID);
            $criteria_dto = array();
            foreach ($criteria_list as $criteria) {
                foreach ($criteria['values'] as $term) {
                    $criteria_dto[] = array(
                        'taxonomy' => $criteria['taxonomy'],
                        'criteria_id' => $criteria['ID'],
                        'criteria_label' => $criteria['label'],
                        'term_id' => $term->term_id,
                        'term_name' => $term->name,
                        'term_slug' => $term->slug
                    );
                }
            }

            $params['body'][] = [
                'date' => $post->post_date,
                'content' => $post->post_content,
                'title' => $post->post_title,
                'slug' => $post->post_name,
                'chouquettise_end' => $chouquettise_end,
                'location' => $location_dto,

                'localisation' => $localisations_dto,
                'categories' => $categories_dto,
                'tags' => $tags_dto,
                'criteria' => $criteria_dto,

                'featured_media' => get_the_post_thumbnail_url($post->ID, "medium"),
                'logo' => Chouquette_WP_Plugin_Lib_Category::get_logo($categories[0], 'black'),
                'marker_icon' => Chouquette_WP_Plugin_Lib_Category::get_marker_icon($categories[0], false),
                'marker_icon_chouquettise' => Chouquette_WP_Plugin_Lib_Category::get_marker_icon($categories[0], true),

                'yoast_focus_kw' => get_post_meta($post->ID, '_yoast_wpseo_focuskw'),
                'yoast_meta_desc' => get_post_meta($post->ID, '_yoast_wpseo_metadesc')
            ];
        }
        $responses = $client->bulk($params);
    }
    $stats = $client->indices()->stats(array('index' => FICHE_INDEX));
    echo '<p><strong>Result for fiches : </strong>' . $stats['_all']['primaries']['docs']['count'] . ' indexed / ' . $fiche_query->found_posts . ' posts</p>';
}
?>

<form method='POST'>
    <p class='submit'>
        <input type='submit' name='export-posts' class='button button-primary' value='Exporter les articles'/>
        <input type='submit' name='export-fiches' class='button button-primary' value='Exporter les fiches'/>
    </p>
</form>
