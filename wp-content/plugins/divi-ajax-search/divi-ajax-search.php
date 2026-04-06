<?php
/*
Plugin Name: Divi Ajax Search
Plugin URI:  https://diviextended.com/product/divi-ajax-search/
Description: An advanced Ajax Live Search plugin to let you display faster & better search results in your Divi theme website.
Version:     1.1.2
Author:      Elicus
Author URI:  https://elicus.com/
Update URI:  https://elegantthemes.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: divi-ajax-search
Domain Path: /languages
*/
defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

define( 'ELICUS_DIVI_AJAX_SEARCH_VERSION', '1.1.2' );
define( 'ELICUS_DIVI_AJAX_SEARCH_OPTION', 'el-divi-ajax-search' );
define( 'ELICUS_DIVI_AJAX_SEARCH_BASENAME', plugin_basename( __FILE__ ) );
define( 'ELICUS_DIVI_AJAX_SEARCH_PATH', plugin_dir_url( __FILE__ ) );

if ( ! function_exists( 'el_divi_ajax_search_initialize_extension' ) ) {
	/**
	 * Creates the extension's main class instance.
	 *
	 * @since 1.0.0
	 */
	function el_divi_ajax_search_initialize_extension() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/DiviAjaxSearch.php';
	}
	add_action( 'divi_extensions_init', 'el_divi_ajax_search_initialize_extension' );
}
