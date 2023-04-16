<?php
/*
 * Copyright (c) 2023 by Fabrice Douchant <fabrice.douchant@gmail.com>.
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * The configuration functionality of the plugin.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/config
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Config
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 * @since    1.0.0
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Get google map API key.
	 *
	 * @since    1.0.0
	 */
	public function acf_fields_google_map_api($api)
	{

		$api['key'] = 'AIzaSyCL4mYyxlnp34tnC57WyrU_63BJhuRoeKI';
		return $api;

	}

	/**
	 * Hack preview post link to website
	 *
	 * @since 1.0.0
	 */
	public function preview_post_link($preview_link, $post)
	{

		$nonce = wp_create_nonce('wp_rest');

		return CQ_FRONTEND_DOMAIN . '/preview?type=' . $post->post_type . '&id=' . $post->ID . '&nonce=' . $nonce;

	}

    /**
     * Change the domain in all post links. Using Frontend link instead of wordpress link
     *
     * @param $post_url
     * @param $post
     * @return string|string[]
     */
    public function change_domain_link($post_url, $post) {

        return str_replace(home_url(), CQ_FRONTEND_DOMAIN, $post_url);

    }

    // FIXME workaround script until there's an official solution for https://github.com/WordPress/gutenberg/issues/13998
    public function fix_preview_link_on_draft() {
        echo '<script type="text/javascript">
            jQuery(document).ready(function () {
                const checkPreviewInterval = setInterval(checkPreview, 1000);
                function checkPreview() {
                    const editorPreviewButton = jQuery(".editor-post-preview");
                    const editorPostSaveDraft = jQuery(".editor-post-save-draft");
                    const editorPostHeaderPreview = jQuery(".edit-post-header-preview__button-external");
                    if (editorPostSaveDraft.length && editorPreviewButton.length && editorPostHeaderPreview.length && editorPreviewButton.attr("href") !== "' . get_preview_post_link() . '" ) {
                        editorPreviewButton.attr("href", "' . get_preview_post_link() . '");
                        editorPreviewButton.off();
                        editorPreviewButton.click(false);
                        editorPreviewButton.on("click", function() {
                            editorPostSaveDraft.click();
                            setTimeout(function() { 
                                const win = window.open("' . get_preview_post_link() . '", "_blank");
                                if (win) {
                                    win.focus();
                                }
                            }, 1000);
                        });
                        // same for post header preview
                        editorPostHeaderPreview.attr("href", "' . get_preview_post_link() . '");
                        editorPostHeaderPreview.off();
                        editorPostHeaderPreview.click(false);
                        editorPostHeaderPreview.on("click", function() {
                            editorPostSaveDraft.click();
                            setTimeout(function() { 
                                const win = window.open("' . get_preview_post_link() . '", "_blank");
                                if (win) {
                                    win.focus();
                                }
                            }, 1000);
                        });
                    }
                }
            });
        </script>';
    }

	/**
	 * Add CORS HTTP Headers
	 *
	 * @since 1.0.0
	 */
	public function add_cors_http_headers()
	{

	    $origin = get_http_origin();
        $allowed_origins = [ CQ_FRONTEND_DOMAIN, get_home_url() ];

        if ($origin && in_array($origin, $allowed_origins)) {
            header("Access-Control-Allow-Origin: " . $origin);
            header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
            header("Access-Control-Allow-Credentials: true");
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, X-WP-Nonce');
        }

    }

}
