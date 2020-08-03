<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()->setHosts(array(Chouquette_WP_Plugin_Elasticsearch::NODE_1))->build();
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
                    '_index' => Chouquette_WP_Plugin_Elasticsearch_Post::INDEX,
                    '_id' => $post->ID
                ]
            ];

            $dto = Chouquette_WP_Plugin_Elasticsearch_Post::build_dto($post);
            $params['body'][] = $dto;
        }
        $responses = $client->bulk($params);
    }
    $stats = $client->indices()->stats(array('index' => Chouquette_WP_Plugin_Elasticsearch_Post::INDEX));
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
                    '_index' => Chouquette_WP_Plugin_Elasticsearch_Fiche::INDEX,
                    '_id' => $post->ID
                ]
            ];

            $dto = Chouquette_WP_Plugin_Elasticsearch_Fiche::build_dto($post);
            $params['body'][] = $dto;
        }
        $responses = $client->bulk($params);
    }
    $stats = $client->indices()->stats(array('index' => Chouquette_WP_Plugin_Elasticsearch_Fiche::INDEX));
    echo '<p><strong>Result for fiches : </strong>' . $stats['_all']['primaries']['docs']['count'] . ' indexed / ' . $fiche_query->found_posts . ' posts</p>';
}
?>

<form method='POST'>
    <p class='submit'>
        <input type='submit' name='export-posts' class='button button-primary' value='Exporter les articles'/>
        <input type='submit' name='export-fiches' class='button button-primary' value='Exporter les fiches'/>
    </p>
</form>
