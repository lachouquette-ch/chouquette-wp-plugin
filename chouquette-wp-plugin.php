<?php
/*
Plugin Name: Chouquette Wordpress Plugin
Description: Plugin to connect SPA to a Wordpress instance. The instance must be pre-initialize with proper custom_post fields and taxonomies
Version: 1.0.0
Author: Fabrice Douchant <fabrice.douchant@gmail.com>
License: La Chouquette all right reserved
Copyright: Fabrice Douchant
 */

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// check if class already exists
if( !function_exists('dhz_acf_plugin_column_field') ) :

	include_once('config/acf.php');
	include_once('custom_post/fiche.php');

endif;


