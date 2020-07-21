<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Elasticsearch\ClientBuilder;

const NODE_1 = '130.223.16.176:9200';
const POST_INDEX = 'wp-posts';

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
            $categories_dto = array_map(function ($category) {
                return array('id' => $category->term_id, 'slug' => $category->slug, 'name' => $category->name);
            }, $categories);

            $tags = get_the_tags($post->ID);
            $tags_dto = array_map(function ($tag) {
                return array('id' => $tag->term_id, 'slug' => $tag->slug, 'name' => $tag->name);
            }, $tags);

            $yoast_focus_kw = get_post_meta($post->ID, '_yoast_wpseo_focuskw');
            $yoast_meta_desc = get_post_meta($post->ID, '_yoast_wpseo_metadesc');


            $logo = Chouquette_WP_Plugin_Lib_Category::get_logo($categories[0], 'black');
            $featured_media = get_the_post_thumbnail_url($post->ID, "medium");

            $params['body'][] = [
                'date' => $post->post_date,
                'content' => $post->post_content,
                'title' => $post->post_title,
                'slug' => $post->post_name,
                'comment_count' => $post->comment_count,
                'featured_media' => $featured_media,
                'logo' => $logo,
                'categories' => $categories_dto,
                'tags' => $tags_dto,
                'yoast_focus_kw' => $yoast_focus_kw,
                'yoast_meta_desc' => $yoast_meta_desc
            ];
        }
        $responses = $client->bulk($params);
    }
    $stats = $client->indices()->stats(array('index' => POST_INDEX));
    echo '<p><strong>Result for posts : </strong>' . $stats['_all']['primaries']['docs']['count'] . ' indexed / ' . $post_query->found_posts . ' posts</p>';
} elseif (isset($_POST['export-posts'])) {
    $response = $client->indices()->delete(array('index' => POST_INDEX));

    echo '<p>Delete index</p>';
}
?>

<form method='POST'>
    <p class='submit'>
        <input type='submit' name='export-posts' class='button button-primary'
               value='Exporter les articles'></input>
    </p>
</form>
