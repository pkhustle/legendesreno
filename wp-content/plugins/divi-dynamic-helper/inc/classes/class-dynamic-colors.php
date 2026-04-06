<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Dynamic_Colors')) {
    class PAC_DDH_Dynamic_Colors
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
            add_filter('et_builder_get_parent_modules', [$this, 'maybe_add_dynamic_colors']);
            add_filter('et_pb_module_shortcode_attributes', [$this, 'maybe_filter_shortcode_attributes'], 10, 5);
        }

        /**
         * Add Dynamic Content Color
         *
         * @param $modules
         *
         * @return mixed
         */
        public function maybe_add_dynamic_colors($modules)
        {
            foreach ($modules as $module_slug => $module) {
                if (isset($module->fields_unprocessed)) {
                    foreach ($module->fields_unprocessed as $field_key => $field_unprocessed) {
                        if (isset($field_unprocessed['type'])) {
                            /*if(isset($field_unprocessed['composite_structure']['border_all']['controls']['border_color_all'])) {
                                 $field_unprocessed['composite_structure']['border_all']['controls']['border_color_all']['dynamic_content'] = 'color-alpha';
                                 pac_ddh_dd($field_unprocessed['composite_structure']['border_all']['controls']['border_color_all'], false, false);
                             }*/
                            if (str_contains($field_unprocessed['type'], 'color-alpha') || str_contains($field_unprocessed['type'], 'background-field')) {
                                $module->fields_unprocessed[$field_key]['dynamic_content'] = 'color-alpha';
                            }
                        }
                    }
                }
            }

            return $modules;
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
            /* $_dynamic_attributes = isset($props['_dynamic_attributes']) ? $props['_dynamic_attributes'] : '';
             if (empty($_dynamic_attributes) && !str_contains($_dynamic_attributes, "background")) {
                 return $props;
             }*/
            if (!empty($props['_dynamic_attributes']) && !preg_match("/background/", $props['_dynamic_attributes'])) {
                return $props;
            }
            // Set Dynamic Color
            if (!empty($attrs['background'])) {
                $queried_object_id = get_queried_object_id();
                $dynamic_content = et_builder_parse_dynamic_content($attrs['background']);
                $value = $dynamic_content->resolve($queried_object_id);
                $props['background_color'] = $value;
                $props['_dynamic_attributes'] = $attrs['_dynamic_attributes'];
            }

            return $props;
        }

    }

    (new PAC_DDH_Dynamic_Colors())->instance()->init();
}