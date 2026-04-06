<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Misc_Modules')) {
    class PAC_DDH_Misc_Modules
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
            add_filter('et_pb_all_fields_unprocessed_et_pb_contact_form', [$this, 'maybe_add_contact_form_fields']);
            if (pac_ddh_is_settins_enabled('ddh_enable_dynamic_counter_module', 'off')) {
                add_filter('et_pb_all_fields_unprocessed_et_pb_counter', [$this, 'maybe_add_et_pb_bar_counter_fields']);
            }
            if (pac_ddh_is_settins_enabled('ddh_enable_dynamic_number_module', 'off')) {
                add_filter('et_pb_all_fields_unprocessed_et_pb_number_counter', [$this, 'maybe_add_et_pb_number_counter_fields']);
            }
            if (pac_ddh_is_settins_enabled('ddh_enable_dynamic_circle_module', 'off')) {
                add_filter('et_pb_all_fields_unprocessed_et_pb_circle_counter', [$this, 'maybe_add_et_pb_circle_counter_fields']);
            }
            add_filter('et_pb_module_shortcode_attributes', [$this, 'maybe_filter_button_shortcode_attributes'], 10, 5);
        }

        /**
         * Get processed fields.
         *
         * @param array $fields_unprocessed The unprocessed fields.
         *
         * @return array Processed fields.
         */
        public function maybe_add_contact_form_fields($fields_unprocessed)
        {
            $custom_fields = [];
            $fields_unprocessed['email']['dynamic_content'] = 'text';

            return wp_parse_args($custom_fields, $fields_unprocessed);
        }

        /**
         * Get processed fields.
         *
         * @param array $fields_unprocessed The unprocessed fields.
         *
         * @return array Processed fields.
         */
        public function maybe_add_et_pb_bar_counter_fields($fields_unprocessed)
        {
            $custom_fields = [];
            $fields_unprocessed['percent']['dynamic_content'] = 'text';

            return wp_parse_args($custom_fields, $fields_unprocessed);
        }

        /**
         * Get processed fields.
         *
         * @param array $fields_unprocessed The unprocessed fields.
         *
         * @return array Processed fields.
         */
        public function maybe_add_et_pb_number_counter_fields($fields_unprocessed)
        {
            $custom_fields = [];
            $fields_unprocessed['number']['dynamic_content'] = 'text';

            return wp_parse_args($custom_fields, $fields_unprocessed);
        }

        /**
         * Get processed fields.
         *
         * @param array $fields_unprocessed The unprocessed fields.
         *
         * @return array Processed fields.
         */
        public function maybe_add_et_pb_circle_counter_fields($fields_unprocessed)
        {
            $custom_fields = [];
            $fields_unprocessed['number']['dynamic_content'] = 'text';

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
        public function maybe_filter_button_shortcode_attributes($props, $attrs, $render_slug, $_address, $content)
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
            if ('et_pb_button' !== $render_slug) {
                return $props;
            }
            // If not dynamic attributes return
            $_dynamic_attributes = isset($props['_dynamic_attributes']) ? $props['_dynamic_attributes'] : '';
            if (empty($_dynamic_attributes) || !preg_match("/button_url/", $_dynamic_attributes)) {
                return $props;
            }
            $email = str_replace(['http://', 'https://'], '', $props['button_url']);
            if (is_email($email)) {
                $props['button_url'] = sprintf('mailto:%s', $email);
            }

            return $props;
        }
    }

    (new PAC_DDH_Misc_Modules())->instance()->init();
}
