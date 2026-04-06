<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Code_Module')) {
    class PAC_DDH_Code_Module
    {

        private static $_instance;

        private static $dynamic_key = '';

        private static $content = '';

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
            add_filter('et_pb_all_fields_unprocessed_et_pb_code', [$this, 'get_fields']);
            add_filter('et_pb_module_shortcode_attributes', [$this, 'maybe_filter_shortcode_attributes'], 10, 5);
            add_filter('et_module_shortcode_output', [$this, 'maybe_filter_map_shortcode_output'], 10, 3);
        }

        /**
         * Get processed fields.
         *
         * @param array $fields_unprocessed The unprocessed fields.
         *
         * @return array Processed fields.
         */
        public function get_fields($fields_unprocessed)
        {
            $custom_fields = [];
            $fields_unprocessed['raw_content']['dynamic_content'] = 'text';

            return wp_parse_args($custom_fields, $fields_unprocessed);
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
            if ('et_pb_code' !== $render_slug) {
                return $props;
            }
            // If not dynamic field return
            $_dynamic_attributes = isset($props['_dynamic_attributes']) ? $props['_dynamic_attributes'] : '';
            if (empty($_dynamic_attributes) || !preg_match("/raw_content/", $_dynamic_attributes)) {
                return $props;
            }
            self::$dynamic_key = $content;
            $dynamic_content = et_builder_parse_dynamic_content($content);
            if (!empty($dynamic_content)) {
                $code_raw_content = '';
                $key = $dynamic_content->get_content();
                if (et_()->starts_with($key, PAC_DDH_DYNAMIC_META_PREFIX)) {
                    $key = str_replace(PAC_DDH_DYNAMIC_META_PREFIX, '', $key);
                    if (class_exists('ACF') && get_field_object($key)) {
                        $code_raw_content = get_field($key);
                    } else {
                        global $post;
                        if (is_a($post, 'WP_POST')) {
                            $code_raw_content = $dynamic_content->resolve($post->ID);
                        }
                    }
                } elseif (et_()->starts_with($key, 'post_meta_key')) {
                    global $post;
                    if (is_a($post, 'WP_POST')) {
                        $code_raw_content = $dynamic_content->resolve($post->ID);
                    }
                }
                if (!empty($code_raw_content)) {
                    self::$content = do_shortcode($code_raw_content);
                }
            }

            return $props;
        }

        /**
         * Maybe filter code shortcode output.
         *
         * This function checks various conditions and filters the shortcode output accordingly.
         *
         * @param string $output The output of the shortcode.
         * @param string $render_slug The slug used for rendering.
         * @param object $module The module object.
         *
         * @return string The filtered output of the shortcode.
         */
        public function maybe_filter_map_shortcode_output($output, $render_slug, $module)
        {
            // Check if the Divi Frontend Builder is enabled; if yes, return props
            if (function_exists('et_fb_is_enabled') && et_fb_is_enabled()) {
                return $output;
            }
            // Check if the Divi Backend Builder is enabled; if yes, return props
            if (function_exists('et_builder_bfb_enabled') && et_builder_bfb_enabled()) {
                return $output;
            }
            // Check if the current request is from admin or an AJAX call; if yes, return props
            if (is_admin() || wp_doing_ajax() || is_array($output)) {
                return $output;
            }
            // Check if the rendered slug is not 'et_pb_audio'; if yes, return props
            if ('et_pb_code' !== $render_slug) {
                return $output;
            }
            // Check if $_dynamic_attributes is empty or does not contain the word 'audio' using a regular expression.
            $_dynamic_attributes = isset($module->props['_dynamic_attributes']) ? $module->props['_dynamic_attributes'] : '';
            if (empty($_dynamic_attributes) || !preg_match('/raw_content/', $_dynamic_attributes)) {
                return $output;
            }
            if (!empty(self::$dynamic_key) && !empty(self::$content)) {
                $output = str_replace(self::$dynamic_key, self::$content, $output);
            } elseif (!empty(self::$dynamic_key) && empty(self::$content)) {
                $output = str_replace(self::$dynamic_key, '', $output);
            }

            return $output;
        }

    }

    (new PAC_DDH_Code_Module())->instance()->init();
}
