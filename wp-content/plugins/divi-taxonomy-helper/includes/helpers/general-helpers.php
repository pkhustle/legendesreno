<?php
/**
 * Get Plugin URL
 *
 * @param $path
 *
 * @return string
 */
if (!function_exists('pac_dth_get_plugin_url')):
    function pac_dth_get_plugin_url($path = '')
    {
        return plugins_url($path, PAC_DTH_PLUGIN_FILE);
    }
endif;
/**
 * Get Option Name
 * @return object
 */
if (!function_exists('pac_dth_get_theme_option_name')):
    function pac_dth_get_theme_option_name()
    {
        $theme = is_child_theme() ? wp_get_theme()->parent()->get('Name') : wp_get_theme()->get('Name');

        return 'divi' === strtolower($theme) ? 'et_divi' : 'et_extra';
    }
endif;
/**
 * Check Requirements
 * @return bool
 */
if (!function_exists('pac_dth_check_plugins_requirements')):
    function pac_dth_check_plugins_requirements()
    {
        $divi_themes = ['divi', 'extra'];
        $active_theme = strtolower(wp_get_theme()->get('Name'));
        $parent_theme = strtolower(wp_get_theme()->get('Template'));
        $divi_builder = is_plugin_active('divi-builder/divi-builder.php');
        $divi_ghoster = is_plugin_active('divi-ghoster/divi-ghoster.php');
        if (in_array($active_theme, $divi_themes) || in_array($parent_theme, $divi_themes) || $divi_builder || $divi_ghoster) {
            return true;
        }

        return false;
    }
endif;
/**
 * Limit String
 *
 * @param $string
 * @param $maxlength
 * @param $ellipsis
 *
 * @return mixed|string
 */
if (!function_exists('pac_dth_chars_limit')) :
    function pac_dth_chars_limit($string, $maxlength, $ellipsis = true)
    {
        if (mb_strlen($string) <= $maxlength) {
            return $string;
        }
        if (empty($ellipsis)) {
            $ellipsis = '';
        }
        if ($ellipsis === true) {
            $ellipsis = '…';
        }
        $ellipsis_length = mb_strlen($ellipsis);
        $maxlength = $maxlength - $ellipsis_length;

        return trim(mb_substr($string, 0, $maxlength)).$ellipsis;
    }
endif;
// phpcs:disable
/**
 * Log data.
 *
 * @param $message
 * @param $db
 * @param $delete
 * @param $file_name
 *
 * @return void
 * @since 1.0
 */
if (!function_exists('pac_dth_log')):
    function pac_dth_log($message, $delete = false, $filename = '')
    {
        $filename = empty($filename) ? debug_backtrace()[1]['function'] : $filename;
        $filename = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$filename.'.log';
        if (file_exists($filename) && $delete) {
            wp_delete_file($filename);
        }
        ini_set('error_log', $filename);
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
endif;
/**
 * print_r Data.
 *
 * @param $data
 * @param $exit
 *
 * @return void
 * @since 1.0
 */
if (!function_exists('pac_dth_dd')):
    function pac_dth_dd($data = '', $exit = true)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        echo $exit ? exit : null;
    }
endif;
// phpcs:enable

