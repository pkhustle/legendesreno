<?php
/*
  * Plugin Name: Divi Dynamic Helper
  * Description: Enable the missing Divi dynamic content features for adding data from custom fields in the Video, Audio, Gallery, Map, Code, Number Counter, Circle Counter, and Bar Counter modules, add dynamic content to color pickers, and add Divi Library layouts to any textarea with dynamic content.
  * Author: Pee-Aye Creative
  * Author URI: https://www.peeayecreative.com/
  * Update URI: https://elegantthemes.com/
  * Text Domain: divi-dynamic-helper
  * Domain Path: /languages/
  * Version: 1.4.4
  * Requires at least: 5.x
  * Requires PHP: 7.0
  * License: GPL2
  * License URI: https://www.gnu.org/licenses/gpl-2.0.html
  *
  * Divi Dynamic Helper is free software: you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation, either version 2 of the License, or  any later version.
  *
  * Divi Dynamic Helper is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  * GNU General Public License for more details.
  * @since 1.0.0
  * @author Pee-Aye Creative
  * @license GPL-2.0+
  * @copyright Copyright (c) 2022, Divi Dynamic Helper
*/
if (!defined('ABSPATH')) exit;
/**
 * Defines the file path of the plugin.
 */
if (!defined('PAC_DDH_PLUGIN_FILE')):
    define('PAC_DDH_PLUGIN_FILE', __FILE__);
endif;
/**
 * Defines the version of the plugin.
 */
if (!defined('PAC_DDH_PLUGIN_VERSION')):
    define('PAC_DDH_PLUGIN_VERSION', '1.4.4');
endif;
/**
 * Defines the directory path of the plugin.
 */
if (!defined('PAC_DDH_PLUGIN_DIR')):
    define('PAC_DDH_PLUGIN_DIR', __DIR__);
endif;
/**
 * Defines the URL of the plugin.
 */
if (!defined('PAC_DDH_PLUGIN_URL')):
    define('PAC_DDH_PLUGIN_URL', plugins_url('', __FILE__));
endif;
/**
 * Defines the base name of the plugin.
 */
if (!defined('PAC_DDH_PLUGIN_BASENAME')):
    define('PAC_DDH_PLUGIN_BASENAME', plugin_basename(__FILE__));
endif;
/**
 * Defines whether the plugin is in development mode.
 */
if (!defined('PAC_DDH_PLUGIN_DEV_MOD')):
    define('PAC_DDH_PLUGIN_DEV_MOD', false);
endif;
/**
 * Defines Divi Meta Key Prefix.
 */
if (!defined('PAC_DDH_DYNAMIC_META_PREFIX')):
    define('PAC_DDH_DYNAMIC_META_PREFIX', 'custom_meta_');
endif;
/**
 * Require once the general helpers file.
 *
 * This file contains various helper functions used throughout the plugin.
 *
 * Functions:
 *
 * {@see pac_ddh_get_plugin_url()} Get the URL of the plugin directory.
 * {@see pac_ddh_get_post_id_by_guid()} Get post-ID by its GUID.
 * {@see pac_ddh_get_attachment_id_by_url()} Get attachment ID by its URL.
 * {@see pac_ddh_is_youtube_video()} Check if the given URL is a YouTube video.
 * {@see pac_ddh_is_vimeo_video()} Check if the given URL is a Vimeo video.
 * {@see pac_ddh_get_geometry_location()} Get the geometry location data.
 * {@see pac_ddh_create_dom()} Create a DOMDocument object from a string.
 * {@see pac_ddh_is_debug_mod()} Check if debug mode is enabled.
 * {@see pac_ddh_log()} Log messages to the error log.
 * {@see pac_ddh_dd()} Debug and die function.
 */
require_once PAC_DDH_PLUGIN_DIR.'/inc/helpers/general-helpers.php';
/**
 * Plugin Setup
 *
 * This file sets up the PAC_DDH plugin by including the necessary class files and initializes the main plugin class.
 *
 * @package PAC_DDH
 * @subpackage Plugin_Setup
 * @since 1.0.0
 *
 * @see PAC_DDH_Plugin_Setup
 */
require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-plugin-setup.php';
/**
 * Fires when the {@see DiviExtension} base class is available.
 */
if (!function_exists('pac_ddh_init_extension')):
    function pac_ddh_init_extension()
    {
        /**
         * Requires the PAC_DDH_Epanel_Settings class file and initializes it for accessing Divi Epanel Settings.
         *
         * This file is responsible for loading the necessary class for handling Divi Epanel Settings.
         **
         * @see PAC_DDH_Epanel_Settings
         */
        require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-epanel-settings.php';
        /**
         * Check if the dynamic audio module settings are enabled and load the audio module class if enabled.
         *
         * @see PAC_DDH_Audio_Module
         */
        if (pac_ddh_is_settins_enabled('ddh_enable_dynamic_audio_module', 'on')) {
            require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-audio-module.php';
        }
        /**
         * Require the Video Module class file if not already included.
         *
         * This function includes the PHP file containing the definition for the Video Module class.
         * It ensures that the file is included only once to prevent redeclaration issues.
         *
         * @see PAC_DDH_Video_Module
         */
        if (pac_ddh_is_settins_enabled('ddh_enable_dynamic_video_module', 'on')) {
            require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-video-module.php';
        }
        /**
         * Require the Gallery Module class file.
         *
         * This function includes the class file for the Gallery Module
         * to enable its functionalities within the plugin.
         *
         * @see PAC_DDH_Gallery_Module
         */
        if (pac_ddh_is_settins_enabled('ddh_enable_dynamic_gallery_module', 'on')) {
            require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-gallery-module.php';
        }
        /**
         * Require the Image Module class file.
         *
         * This function includes the class file for the Image Module
         * to enable its functionalities within the plugin.
         *
         * @see PAC_DDH_Image_Module
         */
        require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-image-module.php';
        /**
         * Require the Map Module class file.
         *
         * This function includes the class file for the Map Module
         * to enable its functionalities within the plugin.
         *
         * @see PAC_DDH_Map_Module
         */
        if (pac_ddh_is_settins_enabled('ddh_enable_dynamic_map_module', 'on')) {
            require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-map-module.php';
        }
        /**
         * Require the Code Module class file.
         *
         * This function includes the class file for the Code Module
         * to enable its functionalities within the plugin.
         *
         * @see PAC_DDH_Code_Module
         */
        if (pac_ddh_is_settins_enabled('ddh_enable_dynamic_code_module', 'off')) {
            require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-code-module.php';
        }
        /**
         * Require the Dynamic Library class file.
         *
         * This function includes the class file for the Dynamic Library
         * to enable its functionalities within the plugin.
         *
         * @see PAC_DDH_Dynamic_Library
         */
        if (pac_ddh_is_settins_enabled('dh_enable_dynamic_lib_layouts', 'on')) {
            require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-dynamic-library.php';
        }
        /**
         * Require the Dynamic Colors class file.
         *
         * This function includes the class file for the Dynamic Colors
         * to enable its functionalities within the plugin.
         *
         * @see PAC_DDH_Dynamic_Colors
         */
        if (pac_ddh_is_settins_enabled('ddh_enable_dynamic_colors', 'off')) {
            require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-dynamic-colors.php';
        }
        /**
         * Require the Postmodified Timestamp class file.
         *
         * This function includes the class file for the Postmodified Timestamp
         * to enable its functionalities within the plugin.
         *
         * @see PAC_DDH_Post_Modified_TimeStamp
         */
        if (pac_ddh_is_settins_enabled('ddh_enable_dynamic_modified_timestamp', 'on')) {
            require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-post-modified-timestamp.php';
        }
        /**
         * Require the Misc Modules class file.
         *
         * This function includes the class file for the Misc Modules
         * to enable its functionalities within the plugin.
         *
         * @see PAC_DDH_Misc_Modules
         */
        require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-misc-modules.php';
        /**
         * Require the ACF Options class file.
         *
         * This function includes the class file for the ACF Options
         * to enable its functionalities within the plugin.
         *
         * @see PAC_DDH_ACF_Dynamic_Options
         */
        if (class_exists('ACF')) {
            require_once PAC_DDH_PLUGIN_DIR.'/inc/classes/class-acf-options.php';
        }
    }

    add_action('divi_extensions_init', 'pac_ddh_init_extension');
endif;