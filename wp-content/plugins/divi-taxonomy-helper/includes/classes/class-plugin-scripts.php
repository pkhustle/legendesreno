<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DTH_Plugin_Scripts')) {
    class PAC_DTH_Plugin_Scripts
    {
        private static $_instance;

        /**
         * Get Class Instance
         * @return PAC_DTH_Plugin_Scripts
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Initializer Of The Class
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            add_action('admin_enqueue_scripts', [$this, 'maybe_admin_scripts']);
            add_action('wp_enqueue_scripts', [$this, 'maybe_public_scripts']);
        }

        /**
         * Admin Enqueue Scripts
         * @return void
         */
        public function maybe_admin_scripts()
        {
            // CSS
            $inline_styles = ['.et-fb-no-vb-support-warning {display: none !important;}'];
            wp_register_style('divi-taxonomy-helper', false);
            wp_enqueue_style('divi-taxonomy-helper');
            wp_add_inline_style('divi-taxonomy-helper', implode(' ', array_unique($inline_styles)));
            // JS
            wp_enqueue_script('divi-taxonomy-helper', pac_dth_get_plugin_url("/assets/admin.min.js"), ['jquery', 'thickbox', 'media-upload'], PAC_DTH_PLUGIN_VERSION, true);
            wp_localize_script('divi-taxonomy-helper', 'pac_dth_obj', [
                'modal_title' => __('Select or upload an image for this term', 'divi-taxonomy-helper'),
                'button_text' => __('Attach', 'divi-taxonomy-helper'),
                'taxonomies' => array_keys(pac_dth_get_registered_taxonomies()),
            ]);
            // Media
            wp_enqueue_media();
        }

        /**
         * Public Enqueue Scripts
         * @return void
         */
        public function maybe_public_scripts()
        {
            // Return If Frontend Builder
            if (function_exists('et_fb_is_enabled') && !et_fb_is_enabled()) {
                return;
            }
            // Return If Backend Builder
            if (function_exists('et_builder_bfb_enabled') && !et_builder_bfb_enabled()) {
                return;
            }
            $inline_styles = ['.et-fb-no-vb-support-warning {display: none !important;}'];
            wp_register_style('divi-taxonomy-helper', false);
            wp_enqueue_style('divi-taxonomy-helper');
            wp_add_inline_style('divi-taxonomy-helper', implode(' ', array_unique($inline_styles)));
        }
    }

    (new PAC_DTH_Plugin_Scripts())->instance()->init();
}
