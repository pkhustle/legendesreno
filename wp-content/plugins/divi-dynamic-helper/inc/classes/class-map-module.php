<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Map_Module')) {
    class PAC_DDH_Map_Module
    {

        private static $_instance;

        private $center_address;

        private $pin_address;

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
            // Parent
            add_filter('et_pb_all_fields_unprocessed_et_pb_map', [$this, 'get_map_fields']);
            add_filter('pre_do_shortcode_tag', [$this, 'maybe_filter_pre_shortcode'], 10, 4);
            add_filter('et_module_shortcode_output', [$this, 'maybe_filter_map_shortcode_output'], 10, 3);
            // Child
            add_filter('et_pb_all_fields_unprocessed_et_pb_map_pin', [$this, 'get_pin_fields']);
            add_filter('pre_do_shortcode_tag', [$this, 'maybe_filter_pre_pin_shortcode'], 10, 4);
            add_filter('et_module_shortcode_output', [$this, 'maybe_filter_pin_shortcode_output'], 10, 3);
        }

        /**
         * Get processed fields.
         *
         * @param array $fields_unprocessed The unprocessed fields.
         *
         * @return array Processed fields.
         */
        public function get_map_fields($fields_unprocessed)
        {
            $custom_fields = [];
            $fields_unprocessed['address']['dynamic_content'] = 'text';

            return wp_parse_args($custom_fields, $fields_unprocessed);
        }

        /**
         * Filter Pin Pre Shortcode
         *
         * @param $bool
         * @param $tag
         * @param $attr
         * @param $m
         *
         * @return mixed
         */
        public function maybe_filter_pre_shortcode($bool, $tag, $attr, $m)
        {
            // Check if the current request is from admin or an AJAX call; if yes, return props
            if (is_admin() || wp_doing_ajax()) {
                return $bool;
            }
            // Check if the rendered slug is not 'et_pb_audio'; if yes, return props
            if ('et_pb_map' !== $tag) {
                return $bool;
            }
            // Check If Dynamic Regrex.
            if (strpos($attr['address'], '@ET-DC@') !== false) {
                $this->center_address = $attr['address'];
            }

            return $bool;

        }

        /**
         * Maybe filter map shortcode output.
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
            // If Divi Theme Builder is enabled, return unchanged output
            if (function_exists('et_fb_is_enabled') && et_fb_is_enabled()) {
                return $output;
            }
            // If Divi Builder Backend is enabled, return unchanged output
            if (function_exists('et_builder_bfb_enabled') && et_builder_bfb_enabled()) {
                return $output;
            }
            // If in admin, ajax request, or the output is an array, return unchanged output
            if (is_admin() || wp_doing_ajax() || is_array($output)) {
                return $output;
            }
            // If the shortcode render slug is not 'et_pb_map', return unchanged output
            if ('et_pb_map' !== $render_slug) {
                return $output;
            }
            // If dynamic attributes are not set or address is not present in dynamic attributes, return unchanged output
            $_dynamic_attributes = isset($module->props['_dynamic_attributes']) ? $module->props['_dynamic_attributes'] : '';
            if (empty($_dynamic_attributes) || !preg_match("/address/", $_dynamic_attributes)) {
                return $output;
            }
            // Retrieve center address
            $center_address = $this->center_address;
            if (!empty($center_address)) {
                $dynamic_content = et_builder_parse_dynamic_content($center_address);
                // If Advanced Custom Fields (ACF) plugin is active
                if (class_exists('ACF')) {
                    $key = str_replace(PAC_DDH_DYNAMIC_META_PREFIX, '', $dynamic_content->get_content());
                    if (et_()->starts_with($key, PAC_DDH_DYNAMIC_META_PREFIX)) {
                        $field_data = get_field($key);
                        if (!empty($field_data)) {
                            $field_object = get_field_object($key);
                            $dom_doc = pac_ddh_create_dom($output);
                            $dom_xpath = new DOMXPath($dom_doc);
                            $et_pb_map = $dom_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' et_pb_map ')]");
                            if (isset($et_pb_map->length) && $et_pb_map->length > 0) {
                                $et_pb_map_item = $et_pb_map->item(0);
                                $default_class = $et_pb_map_item->getAttribute('class');
                                if ('google_map' === $field_object['type'] && !empty($field_data['lat']) && !empty($field_data['lng'])) {
                                    $et_pb_map_item->setAttribute('data-center-lat', esc_attr($field_data['lat']));
                                    $et_pb_map_item->setAttribute('data-center-lng', esc_attr($field_data['lng']));
                                } else {
                                    $et_pb_map_item->setAttribute('class', esc_attr($default_class)." googlemap_center_address");
                                    $et_pb_map_item->setAttribute('data-center-address', esc_attr($field_data));
                                }
                                $output = $dom_doc->saveHTML();
                            }
                        }
                    }
                } else {
                    // If ACF plugin is not active
                    $key = $dynamic_content->get_settings('meta_key');
                    global $post;
                    if (is_a($post, 'WP_POST') && !empty($key)) {
                        $_post_id = isset($post->ID) ? $post->ID : '';
                        if (!empty($_post_id)) {
                            $address = get_post_meta($_post_id, $key, true);
                            $dom_doc = pac_ddh_create_dom($output);
                            $dom_xpath = new DOMXPath($dom_doc);
                            $et_pb_map = $dom_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' et_pb_map ')]");
                            if (isset($et_pb_map->length) && $et_pb_map->length > 0) {
                                $et_pb_map_item = $et_pb_map->item(0);
                                $default_class = $et_pb_map_item->getAttribute('class');
                                $et_pb_map_item->setAttribute('class', esc_attr($default_class)." googlemap_center_address");
                                $et_pb_map_item->setAttribute('data-center-address', esc_attr($address));
                                $output = $dom_doc->saveHTML();
                            }
                        }
                    }
                }
            }

            return $output;
        }

        /**
         * Get processed fields.
         *
         * @param array $fields_unprocessed The unprocessed fields.
         *
         * @return array Processed fields.
         */
        public function get_pin_fields($fields_unprocessed)
        {
            $custom_fields = [];
            $fields_unprocessed['pin_address']['dynamic_content'] = 'text';

            return wp_parse_args($custom_fields, $fields_unprocessed);
        }

        /**
         * Filter Pin Pre Shortcode
         *
         * @param $bool
         * @param $tag
         * @param $attr
         * @param $m
         *
         * @return mixed
         */
        public function maybe_filter_pre_pin_shortcode($bool, $tag, $attr, $m)
        {
            // Check if the current request is from admin or an AJAX call; if yes, return props
            if (is_admin() || wp_doing_ajax()) {
                return $bool;
            }
            // Check if the rendered slug is not 'et_pb_audio'; if yes, return props
            if ('et_pb_map_pin' !== $tag) {
                return $bool;
            }
            // Check If Dynamic Regrex.
            if (strpos($attr['pin_address'], '@ET-DC@') !== false) {
                $this->pin_address = $attr['pin_address'];
            }

            return $bool;

        }

        /**
         * Maybe filter map shortcode output.
         *
         * This function checks various conditions and filters the shortcode output accordingly.
         *
         * @param string $output The output of the shortcode.
         * @param string $render_slug The slug used for rendering.
         * @param object $module The module object.
         *
         * @return string The filtered output of the shortcode.
         */
        public function maybe_filter_pin_shortcode_output($output, $render_slug, $module)
        {
            // Check if the Divi Frontend Builder is enabled; if yes, return props
            if (function_exists('et_fb_is_enabled') && et_fb_is_enabled()) {
                return $output;
            }
            // Check if the Divi Backend Builder is enabled; if yes, return props
            if (function_exists('et_builder_bfb_enabled') && et_builder_bfb_enabled()) {
                return $output;
            }
            // If admin,ajax or array return
            if (is_admin() || wp_doing_ajax() || is_array($output)) {
                return $output;
            }
            // Check if the rendered slug is not 'et_pb_audio'; if yes, return props
            if ('et_pb_map_pin' !== $render_slug) {
                return $output;
            }
            // Check if $_dynamic_attributes is empty or does not contain the word 'audio' using a regular expression.
            $_dynamic_attributes = isset($module->props['_dynamic_attributes']) ? $module->props['_dynamic_attributes'] : '';
            if (empty($_dynamic_attributes) || !preg_match("/pin_address/", $_dynamic_attributes)) {
                return $output;
            }
            $pin_address = $this->pin_address;
            if (!empty($pin_address)) {
                $dynamic_content = et_builder_parse_dynamic_content($pin_address);
                if (class_exists('ACF')) {
                    $key = $dynamic_content->get_content();
                    if (et_()->starts_with($key, PAC_DDH_DYNAMIC_META_PREFIX)) {
                        $key = str_replace(PAC_DDH_DYNAMIC_META_PREFIX, '', $key);
                        $field_data = get_field($key);
                        if (!empty($field_data)) {
                            $field_object = get_field_object($key);
                            $dom_doc = pac_ddh_create_dom($output);
                            $dom_xpath = new DOMXPath($dom_doc);
                            $et_pb_map = $dom_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' et_pb_map_pin ')]");
                            if (isset($et_pb_map->length) && 0 !== $et_pb_map->length) {
                                $et_pb_map_item = $et_pb_map->item(0);
                                $default_class = $et_pb_map_item->getAttribute('class');
                                if ('google_map' === $field_object['type'] && !empty($field_data['lat']) && !empty($field_data['lng'])) {
                                    $et_pb_map_item->setAttribute('data-lat', esc_attr($field_data['lat']));
                                    $et_pb_map_item->setAttribute('data-lng', esc_attr($field_data['lng']));
                                } else {
                                    $et_pb_map_item->setAttribute('class', esc_attr($default_class)." googlemap_pin_address");
                                    $et_pb_map_item->setAttribute('data-address', esc_attr($field_data));
                                }
                                $output = $dom_doc->saveHTML();
                            }
                        }
                    }

                } else {
                    $dynamic_content = et_builder_parse_dynamic_content($pin_address);
                    $key = $dynamic_content->get_settings('meta_key');
                    global $post;
                    if (is_a($post, 'WP_POST') && !empty($key)) {
                        $_post_id = isset($post->ID) ? $post->ID : '';
                        if (!empty($_post_id)) {
                            $address = get_post_meta($_post_id, $key, true);
                            $dom_doc = pac_ddh_create_dom($output);
                            $dom_xpath = new DOMXPath($dom_doc);
                            $et_pb_map = $dom_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' et_pb_map_pin ')]");
                            if (isset($et_pb_map->length) && 0 !== $et_pb_map->length) {
                                $et_pb_map_item = $et_pb_map->item(0);
                                $default_class = $et_pb_map_item->getAttribute('class');
                                $et_pb_map_item->setAttribute('class', esc_attr($default_class)." googlemap_pin_address");
                                $et_pb_map_item->setAttribute('data-address', esc_attr($address));
                                $output = $dom_doc->saveHTML();
                            }
                        }
                    }
                }
            }

            return $output;
        }
    }

    (new PAC_DDH_Map_Module())->instance()->init();
}
