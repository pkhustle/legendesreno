<?php
/*
Plugin Name: Local Directory Fortune - ACF Relationship
Description: Display ACF relationship fields using a shortcode on directory listings.
Version: 1.0
Author: Dawn Vu
*/

function acf_relationship_display_shortcode($atts) {
    // Default attributes
    $atts = shortcode_atts(array(
        'field_name' => '', // Replace with your actual ACF field name
        'post_id' => get_the_ID(), // Defaults to the current post
    ), $atts);

    $related_posts = get_field($atts['field_name'], $atts['post_id']);
    $output = ''; // Initialize the output variable

    if ($related_posts) {
        $output .= '<div class="acf-related-posts-container">'; // Flex container

        foreach ($related_posts as $post) {
            // Set up post data
            setup_postdata($post);

            $post_type = get_post_type($post->ID);
            $taxonomy_terms = array();
            $button_text = '';

            if ($post_type == 'professional') {
                // Fetch the professional's projects
                $taxonomy_terms = get_the_terms($post->ID, 'professional-type');
                $button_text = 'View Profile';
            } elseif ($post_type == 'projects') {
                // Fetch the project's professional type
                $taxonomy_terms = get_the_terms($post->ID, 'project-location');
                $button_text = 'View Project';
            }

            // Convert terms array into a string list
            $taxonomy_list = $taxonomy_terms ? implode(', ', wp_list_pluck($taxonomy_terms, 'name')) : 'General';

            $output .= '<div class="acf-related-post-item">'; // Flex item
            $output .= '<a href="' . get_permalink($post->ID) . '">';
            $output .= '<div class="acf-related-post-image">' . get_the_post_thumbnail($post->ID, 'thumbnail', array('class' => 'custom-featured-image')) . '</div>';
            $output .= '<h3 class="acf-related-post-title">' . get_the_title($post->ID) . '</h3>';
            $output .= '<p class="acf-related-post-taxonomy">' . esc_html($taxonomy_list) . '</p>';
            $output .= '</a>';
            $output .= '<a href="' . get_permalink($post->ID) . '" class="acf-related-post-button">' . esc_html($button_text) . '</a>'; // Button with custom text
            $output .= '</div>'; // Close flex item
        }

        wp_reset_postdata(); // Reset post data
        $output .= '</div>'; // Close flex container
    } else {
        // Determine post type to display specific message
        $post_type = get_post_type($atts['post_id']);
        if ($post_type == 'professional') {
            $output = 'This professional hasn\'t submitted any project.';
        } elseif ($post_type == 'projects') {
            $output = 'No contributing professional found.';
        } else {
            $output = 'No Related Content Found'; // Fallback for other or undefined post types
        }
    }

    return $output;
}

add_shortcode('acf_relationship', 'acf_relationship_display_shortcode');


// Hide Divi Project Post Type

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function hide_projects_post_type_from_admin() {
    // Remove 'Projects' post type from the main admin menu.
    remove_menu_page('edit.php?post_type=project');
    
    // Remove 'Projects' post type from the 'Create New' submenu.
    global $submenu;
    if (isset($submenu['edit.php?post_type=project'])) {
        unset($submenu['edit.php?post_type=project']);
    }
}
add_action('admin_menu', 'hide_projects_post_type_from_admin', 999);

function redirect_projects_post_type() {
    // Redirect any direct access attempts to the 'Projects' post type in the admin area.
    global $pagenow;
    $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
    if ($post_type === 'project' && ($pagenow === 'edit.php' || $pagenow === 'post-new.php')) {
        wp_redirect(admin_url());
        exit;
    }
}
add_action('admin_init', 'redirect_projects_post_type');

function remove_projects_from_admin_bar($wp_admin_bar) {
    // Remove 'Projects' post type from the 'New' menu in the admin bar.
    $wp_admin_bar->remove_node('new-project');
}
add_action('admin_bar_menu', 'remove_projects_from_admin_bar', 999);
