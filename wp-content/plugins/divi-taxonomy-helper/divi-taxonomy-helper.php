<?php
/*
Plugin Name: Divi Taxonomy Helper
Description: Add images to taxonomy terms like categories and tags, enable taxonomy featured image support in the Divi Theme Builder templates, display taxonomies in a fully customizable grid similar to the blog module, and show posts and products by current hierarchy level in the Blog and Woo products modules.
Version:     1.6
Author:      Pee Aye Creative
Plugin URI:  https://www.elegantthemes.com/marketplace/divi-taxonomy-helper/
Author URI:  https://www.peeayecreative.com/
Update URI:  https://elegantthemes.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: divi-taxonomy-helper
Domain Path: /languages

Divi Taxonomy Helper is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Divi Taxonomy Helper is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Divi Taxonomy Helper. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/
if (!defined('ABSPATH')) exit;
// Plugin File
if (!defined('PAC_DTH_PLUGIN_FILE')):
    define('PAC_DTH_PLUGIN_FILE', __FILE__);
endif;
// Plugin DIR
if (!defined('PAC_DTH_PLUGIN_DIR')):
    define('PAC_DTH_PLUGIN_DIR', __DIR__);
endif;
// Plugin Version
if (!defined('PAC_DTH_PLUGIN_VERSION')):
    define('PAC_DTH_PLUGIN_VERSION', '1.6');
endif;
// Plugin BASENAME
if (!defined('PAC_DTH_PLUGIN_BASENAME')):
    define('PAC_DTH_PLUGIN_BASENAME', plugin_basename(PAC_DTH_PLUGIN_FILE));
endif;
/**
 * General Helpers
 * {@see pac_dth_get_plugin_url function.
 * {@see pac_dth_get_theme_option_name function.
 * {@see pac_dth_check_plugins_requirements function.
 * {@see pac_dth_chars_limit function.
 * {@see pac_dth_log function.
 * {@see pac_dth_dd function.
 */
require_once PAC_DTH_PLUGIN_DIR.'/includes/helpers/general-helpers.php';
/**
 * Plugin Setip
 * {@see PAC_DTH_Plugin_Setup} class.
 */
require_once PAC_DTH_PLUGIN_DIR.'/includes/classes/class-plugin-setup.php';
/**
 * Fires when the {@see DiviExtension} base class is available.
 */
if (!function_exists('pac_dth_initialize_extension')):
    function pac_dth_initialize_extension()
    {
        /**
         * Post Helpers
         * {@see pac_dth_get_registered_post_types function.
         * {@see pac_dth_get_registered_taxonomies function.
         * {@see pac_dth_get_taxonomies_by_object function.
         * {@see pac_dth_get_terms_by_taxonomy function.
         * {@see pac_dth_get_child_terms function.
         */
        require_once PAC_DTH_PLUGIN_DIR.'/includes/helpers/post-helpers.php';
        /**
         * Module Helpers
         * {@see pac_dth_process_multiple_checkboxes function.
         * {@see pac_dth_get_mpb_props function.
         * {@see pac_dth_get_flex_alignment function.
         */
        require_once PAC_DTH_PLUGIN_DIR.'/includes/helpers/module-helpers.php';
        /**
         * Plugin Scripts
         * {@see PAC_DTH_Plugin_Scripts}
         */
        require_once PAC_DTH_PLUGIN_DIR.'/includes/classes/class-plugin-scripts.php';
        /**
         * Theme Options Class
         * {@see PAC_DTH_Divi_Theme_Options}
         */
        require_once PAC_DTH_PLUGIN_DIR.'/includes/classes/class-divi-theme-options.php';
        /**
         * Term Image Class
         * {@see PAC_DTH_Term_Image}
         */
        require_once PAC_DTH_PLUGIN_DIR.'/includes/classes/class-term-image.php';
        /**
         * Filter Divi Shop Module
         * {@see PAC_DTH_Divi_Shop_Module}
         */
        require_once PAC_DTH_PLUGIN_DIR.'/includes/classes/class-divi-shop-module.php';
        /**
         * Filter Divi Blog Module
         * {@see PAC_DTH_Divi_Blog_Module}
         */
        require_once PAC_DTH_PLUGIN_DIR.'/includes/classes/class-divi-blog-module.php';
        /**
         * Filter Divi Dynamic Content
         * {@see PAC_DTH_Divi_Dynamic_Content}
         */
        require_once PAC_DTH_PLUGIN_DIR.'/includes/classes/class-divi-dynamic-content.php';
        /**
         * Divi Extension Class
         * {@see PACDTH_DiviExtension}
         */
        require_once PAC_DTH_PLUGIN_DIR.'/includes/PACDTH_DiviExtension.php';
    }

    add_action('divi_extensions_init', 'pac_dth_initialize_extension');
endif;