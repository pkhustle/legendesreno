<?php
/*
Plugin Name: Contact Form DB Divi
Plugin URI: https://www.learnhowwp.com/divi-contact-form-db/
Description: The plugin saves all form submission made to Divi forms in the WordPress backend.
Version: 1.2
Author: Learnhowwp.com
Author URI: https://learnhowwp.com
License: GPL2
Update URI: https://www.elegantthemes.com/
@fs_premium_only includes/class-lwp-cfdb-export-form-submission.php
*/

// A constant to store the current version of the plugin
define( 'LWP_CFDB_VERSION', '1.2' );

// A global variable to check if the version of the plugin is the free version
global $is_free_version;

//==================================ET MARKETPLACE======================================
//======================================================================================

if ( ! function_exists( 'lwp_cfdd_fs' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-lwp-cfdb-freemius-alt.php';
    function lwp_cfdd_fs() {
        return new Lwp_Cfdb_Freemius_Alt;
    }
}

//======================================================================================
//======================================================================================

// Initialize the global variable to check the version of plugin being used
$is_free_version = ! lwp_cfdd_fs()->is__premium_only();

//======================================================================================

require_once plugin_dir_path( __FILE__ ) . 'includes/class-lwp-cfdb-form-submission-cpt.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-lwp-cfdb-form-submission-meta-boxes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-lwp-cfdb-form-submission-creator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-lwp-cfdb-modify-module.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-lwp-cfdb-upgrades.php';

//
if ( lwp_cfdd_fs()->is__premium_only() ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-lwp-cfdb-export-form-submission.php';
}

//======================================================================================

new Lwp_Cfdb_Form_Submission_CPT();
new Lwp_Cfdb_Form_Submission_Meta_Boxes();
new Lwp_Cfdb_Form_Submission_Creator();
new Lwp_Cfdb_Modify_Module();

if ( lwp_cfdd_fs()->is__premium_only() ) {
    new Lwp_Cfdb_Export_Form_Submission();
}

//======================================================================================

//
add_action( 'admin_init', 'lwp_cfdb_check_upgrade_callback' );

/**
 * Callback function to check and perform upgrades
 *
 * It checks if the 'lwp_cfdb_plugin_version' option is not set, indicating that an upgrade is required.
 * If an upgrade is required, it performs the necessary upgrade actions.
 * After the upgrade, it stores the current version in the 'lwp_cfdb_plugin_version' option.
 *
 * @since 1.1
 */
function lwp_cfdb_check_upgrade_callback() {

    //
    $stored_version  = get_option( 'lwp_cfdb_plugin_version', '1.0' );
    $current_version = LWP_CFDB_VERSION;

    if ( version_compare( $stored_version, $current_version, '<' ) ) {

        if ( version_compare( $stored_version, '1.1', '<' ) ) {
            Lwp_Cfdb_Upgrades::upgrade_to_1_1();
        }

        if ( version_compare( $stored_version, '1.2', '<' ) ) {
            Lwp_Cfdb_Upgrades::upgrade_to_1_2();
        }

        // Update the stored version
        update_option( 'lwp_cfdb_plugin_version', $current_version );

    }

}

//======================================================================================

/**
 * Activation hook callback function which stores the current version to the database.
 *
 * @since 1.1
 */
function lwp_cfdb_activation_hook() {
    // Perform upgrade tasks on plugin activation
    lwp_cfdb_check_upgrade_callback();

    // Save the current version in the database
    $current_version = LWP_CFDB_VERSION;
    update_option( 'lwp_cfdb_plugin_version', $current_version );
}

// Register the activation hook
register_activation_hook( __FILE__, 'lwp_cfdb_activation_hook' );

//======================================================================================