<?php
/**
 * Retrieve the URL to a plugin file or directory, with the path relative to the plugins' directory.
 *
 * @param string $path Optional. Path relative to the plugins' directory.
 *                     Default empty.
 *
 * @return string Plugins URL link with the path relative to the plugins' directory.
 */
if (!function_exists('pac_ddh_get_plugin_url')):
    function pac_ddh_get_plugin_url($path = '')
    {
        return plugins_url($path, PAC_DDH_PLUGIN_FILE);
    }
endif;
/**
 * Retrieve the post-ID associated with a given GUID.
 * This function checks if a post exists in the WordPress database with the provided GUID.
 * If found, it returns the corresponding post-ID; otherwise, it returns null.
 *
 * @param string $guid The GUID of the post to retrieve the ID for.
 *
 * @return int|null The post-ID if found; otherwise, null.
 */
if (!function_exists('pac_ddh_get_post_id_by_guid')):
    function pac_ddh_get_post_id_by_guid($guid)
    {
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid LIKE %s", '%'.$wpdb->esc_like(wp_basename($guid)).'%')); // phpcs:ignore
    }
endif;
/**
 * Retrieves the attachment ID associated with a given URL.
 * This function searches for an attachment ID based on the URL provided.
 * It checks if the URL belongs to the current site
 * and then queries the database for attachments matching the URL's basename.
 * If found, it returns the attachment ID, otherwise, it returns 0.
 *
 * @param string $url The URL of the attachment.
 *
 * @return int The attachment ID if found, otherwise 0.
 */
if (!function_exists('pac_ddh_get_attachment_id_by_url')):
    function pac_ddh_get_attachment_id_by_url($url)
    {
        $attachment_id = 0;
        $parsed_url = wp_parse_url($url);
        if ($parsed_url && isset($parsed_url['host'])) {
            $site_host = wp_parse_url(get_site_url(), PHP_URL_HOST);
            if ($parsed_url['host'] === $site_host) {
                $file_basename = basename($url);
                // phpcs:disable
                $query = new WP_Query([
                    'post_type' => 'attachment',
                    'post_status' => 'inherit',
                    'fields' => 'ids',
                    'meta_query' => [
                        [
                            'value' => $file_basename,
                            'compare' => 'LIKE',
                            'key' => '_wp_attachment_metadata',
                        ],
                    ]
                ]);
                // phpcs:enable
                if ($query->have_posts()) {
                    foreach ($query->posts as $post_id) {
                        $meta = wp_get_attachment_metadata($post_id);
                        $original_file = basename($meta['file']);
                        $cropped_image_files = wp_list_pluck($meta['sizes'], 'file');
                        if ($original_file === $file_basename || in_array($file_basename, $cropped_image_files)) {
                            $attachment_id = $post_id;
                            break;
                        }
                    }
                }
            }
        }

        return $attachment_id;
    }
endif;
/**
 * Checks if a particular setting is enabled.
 * This function checks whether a specific setting is enabled or not.
 * It retrieves the value of the setting using `et_get_option()` function and compares it to the string 'on'.
 *
 * @param string $key The key of the setting to check.
 * @param mixed $default Optional. Default value to return if the setting is not found. Default is an empty string.
 *
 * @return bool True if the setting is enabled (has a value of 'on'), false otherwise.
 */
if (!function_exists('pac_ddh_is_settins_enabled')):
    function pac_ddh_is_settins_enabled($key, $default = '')
    {
        return 'on' === et_get_option($key, $default);
    }
endif;
/**
 * Checks if a given URL is a YouTube video URL.
 *
 * @param string $url The URL to be checked.
 *
 * @return bool Returns true if the URL is a Vimeo video URL, false otherwise.
 */
if (!function_exists('pac_ddh_is_youtube_video')):
    function pac_ddh_is_youtube_video($url)
    {
        return (preg_match('/youtu\.be/i', $url) || preg_match('/youtube\.com\/watch/i', $url));
    }
endif;
/**
 * Checks if a given URL is a Vimeo video URL.
 *
 * @param string $url The URL to be checked.
 *
 * @return bool Returns true if the URL is a Vimeo video URL, false otherwise.
 */
if (!function_exists('pac_ddh_is_vimeo_video')):
    function pac_ddh_is_vimeo_video($url)
    {
        return (preg_match('/vimeo\.com/i', $url));
    }
endif;
/**
 * Retrieves the geometry location (latitude and longitude) for a given address using the Google Maps Geocoding API.
 *
 * @param string $address The address to retrieve the geometry location for.
 *
 * @return array|false An array containing the latitude and longitude of the address, or false if unable to retrieve.
 */
if (!function_exists('pac_ddh_get_geometry_location')):
    function pac_ddh_get_geometry_location($address)
    {
        $google_api_key = et_pb_get_google_api_key();
        if (empty($google_api_key)) {
            return false;
        }
        $transient_key = 'pac_ddh_map_'.md5($address);
        $geometry_location = get_transient($transient_key);
        if (false === $geometry_location) {
            $response = wp_remote_get("https://maps.google.com/maps/api/geocode/json?sensor=false&key=".$google_api_key."&address=".rawurldecode($address));
            if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
                $response = json_decode(wp_remote_retrieve_body($response));
                if (isset($response->results[0]->geometry->location) && !empty($response->results[0]->geometry->location)) {
                    $location = $response->results[0]->geometry->location;
                    if ((isset($location->lat) && '' !== $location->lat) && (isset($location->lng) && '' !== $location->lng)) {
                        $geometry_location = ['lat' => esc_html($location->lat), 'lng' => esc_html($location->lng)];
                        set_transient($transient_key, $geometry_location, DAY_IN_SECONDS); // Save For One Day

                        return $geometry_location;
                    }
                }
            }
        }

        return $geometry_location;
    }
endif;
/**
 * Create a DOMDocument object from HTML content.
 * This function creates a DOMDocument object from the provided HTML content,
 * ensuring proper encoding and handling any errors.
 *
 * @param string $html The HTML content to be parsed into a DOMDocument object.
 *
 * @return DOMDocument|false A DOMDocument object representing the parsed HTML content,
 *                          or false on failure.
 */
if (!function_exists('pac_ddh_create_dom')):
    function pac_ddh_create_dom($html)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        if (function_exists('mb_convert_encoding')) {
            $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $dom->encoding = 'utf-8';
        } else {
            $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".$html, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        }

        return $dom;
    }
endif;
/**
 * Checks if the debug mode is enabled for the PAC_DDH plugin.
 * This function determines whether the debug mode is activated for the PAC_DDH plugin.
 * Debug mode can be set externally via the constant PAC_DDH_PLUGIN_DEV_MOD.
 * If PAC_DDH_PLUGIN_DEV_MOD constant is defined and set to true, debug mode is considered enabled.
 * @return bool Returns true if debug mode is enabled, false otherwise.
 */
if (!function_exists('pac_ddh_is_debug_mod')):
    function pac_ddh_is_debug_mod()
    {
        if (defined('PAC_DDH_PLUGIN_DEV_MOD') && PAC_DDH_PLUGIN_DEV_MOD) {
            return true;
        }

        return false;
    }
endif;
// phpcs:disable
/**
 * Log messages to a specified file.
 *
 * @param mixed $message The message to be logged.
 * @param bool $delete Whether to delete the log file if it exists.
 * @param string $filename The name of the log file. If empty, it defaults to the calling function's name.
 *
 * @return void
 */
if (!function_exists('pac_ddh_log')):
    function pac_ddh_log($message, $delete = false, $filename = '')
    {
        $filename = empty($filename) ? debug_backtrace()[1]['function'] : $filename;
        // Construct the full path for the log file
        $log_filepath = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$filename.'.log';
        // Check if the log file exists and if the deleted flag is set to true, delete it
        if (file_exists($log_filepath) && $delete) {
            wp_delete_file($log_filepath);
        }
        // Set the PHP error log file
        ini_set('error_log', $log_filepath);
        // Serialize complex data types for logging
        $log = is_array($message) || is_object($message) ? print_r($message, true) : $message;
        error_log($log);
    }
endif;
/**
 * Output debug data and optionally halt execution.
 * This function outputs debug data wrapped in <pre> tags for better readability.
 * If $exit is set to true, it halts further execution after output.
 *
 * @param mixed $data The data to be debugged and displayed.
 * @param bool $exit Optional. Whether to halt execution after output. Default is true.
 */
if (!function_exists('pac_ddh_dd')):
    function pac_ddh_dd($data = '', $exit = true)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if ($exit) {
            exit;
        }
    }
endif;
// phpcs:enable