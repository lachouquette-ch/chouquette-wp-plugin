<?php
if (!function_exists('chouquette_fiche_post_type')) :
	function chouquette_fiche_post_type()
	{
		$labels = array(
			'name' => _x('Fiches', 'Post Type General Name', 'chouquette'),
			'singular_name' => _x('Fiche', 'Post Type Singular Name', 'chouquette'),
			'menu_name' => __('Fiches', 'chouquette'),
			'name_admin_bar' => __('Fiche', 'chouquette'),
			'parent_item_colon' => __('Fiche parente', 'chouquette'),
			'all_items' => __('Toutes les fiches', 'chouquette'),
			'add_new_item' => __('Ajouter une nouvelle fiche', 'chouquette'),
		);
		$args = array(
			'label' => __('Fiche', 'chouquette'),
			'description' => __('Fiche Chouquette', 'chouquette'),
			'labels' => $labels,
			'supports' => array('title', 'thumbnail', 'editor', 'revisions', 'custom_post-fields'),
			'taxonomies' => array('category', 'post_tag'),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-location',
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'can_export' => true,
			'has_archive' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => true,
			'query_var' => true,
			'capability_type' => 'post',
			'public' => true,
			'show_in_rest' => true,
			'rest_base' => 'fiches'
		);
		register_post_type('fiche', $args);

		register_taxonomy(
			'icon-info',
			'fiche',
			array(
				'label' => __('Icone info'),
				'public' => false,
				'show_ui' => true,
				'show_admin_column' => true,
				'rewrite' => false,
				'hierarchical' => true
			)
		);
	}

	add_action('init', 'chouquette_fiche_post_type', 0);
endif;

if (!function_exists('chouquette_filter_by_fiche')):
	function chouquette_filter_by_fiche($query)
	{
		if (!is_admin() && $query->is_main_query() && isset($_GET['lf'])) {
			$my_meta = array(
				'key' => 'link_fiche',
				'value' => '"' . $_GET['lf'] . '"',
				'compare' => 'LIKE'
			);
			$meta_query = $query->get('meta_query');
			if (!empty($meta_query)) {
				$meta_query[] = $my_meta;
				$query->set('meta_query', $meta_query);
			} else {
				$meta_query = array($my_meta);
				$query->set('meta_query', $meta_query);
			}
		}
	}
endif;
