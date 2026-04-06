<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Image_Module')) {
    class PAC_DDH_Image_Module
    {
        private static $_instance;

        /**
         * Returns an instance of the class.
         *
         * @return self The instance of the class.
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Class Initialization
         *
         * Handles the initialization of the class, including adding/removing necessary actions and filters.
         */
        public function init()
        {
            add_filter('et_pb_module_shortcode_attributes', [$this, 'maybe_filter_shortcode_attributes'], 10, 5);
        }

        /**
         * Maybe filter shortcode attributes.
         *
         * @param array $props The shortcode properties.
         * @param array $attrs The shortcode attributes.
         * @param string $render_slug The render slug.
         * @param string $_address The address.
         * @param string $content The content.
         *
         * @return array
         */
        public function maybe_filter_shortcode_attributes($props, $attrs, $render_slug, $_address, $content)
        {
            // Check if the Divi Frontend Builder is enabled; if yes, return props
            if (function_exists('et_fb_is_enabled') && et_fb_is_enabled()) {
                return $props;
            }
            // Check if the Divi Backend Builder is enabled; if yes, return props
            if (function_exists('et_builder_bfb_enabled') && et_builder_bfb_enabled()) {
                return $props;
            }
            // Check if the current request is from admin or an AJAX call; if yes, return props
            if (is_admin() || wp_doing_ajax()) {
                return $props;
            }
            // Check if the rendered slug is not 'et_pb_audio'; if yes, return props
            if ('et_pb_image' !== $render_slug) {
                return $props;
            }
            // Check if $_dynamic_attributes is empty or does not contain the word 'audio' using a regular expression.
            $_dynamic_attributes = isset($props['_dynamic_attributes']) ? $props['_dynamic_attributes'] : '';
            if (empty($_dynamic_attributes) || !preg_match("/src/", $_dynamic_attributes)) {
                return $props;
            }
            if (preg_match('/<img.*?src="(.*?)"/', html_entity_decode($props['src']), $matches)) {
                if (!empty($matches[1])) {
                    $props['src'] = $matches[1];
                }
            }

            return $props;
        }

    }

    (new PAC_DDH_Image_Module())->instance()->init();
}