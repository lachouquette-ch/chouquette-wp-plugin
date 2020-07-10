<?php
require 'vendor/autoload.php';

use Elasticsearch\ClientBuilder;

const NODE_1 = '130.223.16.176:9200';
const POST_INDEX = 'wp-posts';

$client = ClientBuilder::create()->setHosts(array(NODE_1))->build();

if (isset($_POST['create'])) {
    $params = [
        'index' => POST_INDEX,
        'body' => [
            'settings' => [
                'analysis' => [
                    'tokenizer' => 'french',
                    'filter' => ['lowercase', 'stop', 'word_delimiter_graph'],
                    'char_filter' => ['html_strip'],
                ]
            ],
            'mappings' => [
                'properties' => [
                    'date' => [
                        'type' => 'date',
                        'format' => 'yyyy-MM-dd HH:mm:ss'
                    ],
                    'content' => [
                        'type' => 'text'
                    ],
                    'title' => [
                        'type' => 'text',
                        'fields' => [
                            'keyword' => [
                                'type' => 'keyword'
                            ]
                        ]
                    ],
                    'slug' => [
                        'type' => 'text',
                        'index' => false
                    ],
                    'comment_count' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ]
    ];
    $client->indices()->create($params);
    echo "<p><strong>Index created</strong></p>";
} elseif (isset($_POST['export'])) {
    /* Posts */

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
            $params['body'][] = [
                'date' => $post->post_date,
                'content' => $post->post_content,
                'title' => $post->post_title,
                'slug' => $post->post_name,
                'comment_count' => $post->comment_count
            ];
        }
        $responses = $client->bulk($params);
    }
    wp_reset_postdata();
    echo "<p>Posts exported</p>";

    /* Fiches */
    // TODO
    echo "<p>Fiches exported</p>";
} elseif (isset($_POST['delete'])) {
    $response = $client->indices()->delete(array('index' => POST_INDEX));

    echo "<p>Delete index</p>";
}
?>

    <h1>Administration Elasticsearch</h1>
    <form method="POST">
        <p class="submit">
            <input type="submit" name="export" class="button button-primary" value="Exorter les documents"></input>
            <input type="submit" name="create" class="button button-primary" value="Créer les indexes"></input>
            <input type="submit" name="delete" class="button button-secondary" value="Supprimer les indexes"></input>
        </p>
    </form>

<?php
?>