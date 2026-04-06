<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Epanel_Settings')) {
    class PAC_DDH_Epanel_Settings
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
            add_filter('et_epanel_tab_names', [$this, 'maybe_add_tab']);
            add_filter('et_epanel_layout_data', [$this, 'maybe_add_tab_data']);
        }

        /**
         * Add Custom Tab To Panel
         *
         * @param $tabs
         *
         * @return array
         */
        public function maybe_add_tab($tabs)
        {
            return wp_parse_args(['divi-dynamic-helper' => __('Divi Dynamic Helper', 'divi-dynamic-helper')], $tabs);
        }

        /**
         * Add Options To Custom Tab
         *
         * @param $options
         *
         * @return array
         */
        public function maybe_add_tab_data($options)
        {

            $custom_options[] = [
                'name' => 'wrap-divi-dynamic-helper',
                'type' => 'contenttab-wrapstart',
            ];
            $custom_options[] = [
                'type' => 'subnavtab-start',
            ];
            $custom_options[] = [
                'name' => 'tab-dynamic-content',
                'type' => 'subnav-tab',
                'desc' => __('Dynamic Content', 'divi-dynamic-helper')
            ];
            $custom_options[] = [
                'type' => 'subnavtab-end',
            ];
            $custom_options[] = [
                'name' => 'tab-dynamic-content',
                'type' => 'subcontent-start',
            ];
            $custom_options[] = [
                'name' => __('Enable Dynamic Content In Video Module', 'divi-dynamic-helper'),
                'id' => 'ddh_enable_dynamic_video_module',
                'type' => 'checkbox',
                'desc' => __('Enable this feature to activate the dynamic content feature in the Divi Video module.', 'divi-dynamic-helper'),
                'std' => 'on',
            ];
            $custom_options[] = [
                'name' => __('Enable Dynamic Content In Audio Module', 'divi-dynamic-helper'),
                'id' => 'ddh_enable_dynamic_audio_module',
                'type' => 'checkbox',
                'desc' => __('Enable this feature to activate the dynamic content feature in the Divi Audio module.', 'divi-dynamic-helper'),
                'std' => 'on',
            ];
            $custom_options[] = [
                'name' => __('Enable Dynamic Content In Gallery Module', 'divi-dynamic-helper'),
                'id' => 'ddh_enable_dynamic_gallery_module',
                'type' => 'checkbox',
                'desc' => __('Enable this feature to activate the dynamic content feature in the Divi Gallery module.', 'divi-dynamic-helper'),
                'std' => 'on',
            ];
            $custom_options[] = [
                'name' => __('Enable Dynamic Content In Map Module', 'divi-dynamic-helper'),
                'id' => 'ddh_enable_dynamic_map_module',
                'type' => 'checkbox',
                'desc' => __('Enable this feature to activate the dynamic content feature in the Divi Map module.', 'divi-dynamic-helper'),
                'std' => 'on',
            ];
            $custom_options[] = [
                'name' => __('Enable Dynamic Content In Code Module', 'divi-dynamic-helper'),
                'id' => 'ddh_enable_dynamic_code_module',
                'type' => 'checkbox',
                'desc' => __('Enable this feature to activate the dynamic content feature in the Divi Code module.', 'divi-dynamic-helper'),
                'std' => 'off',
            ];
            $custom_options[] = [
                'name' => __('Enable Dynamic Library Layouts In All Modules', 'divi-dynamic-helper'),
                'id' => 'dh_enable_dynamic_lib_layouts',
                'type' => 'checkbox',
                'desc' => __('Enable this feature to activate the dynamic content feature in the Divi Code module.', 'divi-dynamic-helper'),
                'std' => 'on',
            ];
            $custom_options[] = [
                'name' => __('Enable Dynamic Colors In All Sections, Rows, And Modules', 'divi-dynamic-helper'),
                'id' => 'ddh_enable_dynamic_colors',
                'type' => 'checkbox',
                'desc' => __('Enable this feature to activate the dynamic content feature in all the color picker fields in all the Divi sections, rows, and modules.', 'divi-dynamic-helper'),
                'std' => 'off',
            ];
            $custom_options[] = [
                'name' => __('Enable Dynamic Content In Counter Module', 'divi-dynamic-helper'),
                'id' => 'ddh_enable_dynamic_counter_module',
                'type' => 'checkbox',
                'desc' => __('Enable this feature to activate the dynamic content feature in the Divi Bar Counter module for the percent field.', 'divi-dynamic-helper'),
                'std' => 'off',
            ];
            $custom_options[] = [
                'name' => __('Enable Dynamic Content In The Number Counter Module', 'divi-dynamic-helper'),
                'id' => 'ddh_enable_dynamic_number_module',
                'type' => 'checkbox',
                'desc' => __('Enable this feature to activate the dynamic content feature in the Divi Number Counter module for the number field.', 'divi-dynamic-helper'),
                'std' => 'off',
            ];
            $custom_options[] = [
                'name' => __('Enable Dynamic Content In The Circle Counter Module', 'divi-dynamic-helper'),
                'id' => 'ddh_enable_dynamic_circle_module',
                'type' => 'checkbox',
                'desc' => __('Enable this feature to activate the dynamic content feature in the Divi Circle Counter module for the number field.', 'divi-dynamic-helper'),
                'std' => 'off',
            ];
            $custom_options[] = [
                'name' => __('Enable Modified Timestamp', 'divi-dynamic-helper'),
                'id' => 'ddh_enable_dynamic_modified_timestamp',
                'type' => 'checkbox',
                'desc' => __('Enable this feature to activate the modified timestamp dynamic content option.', 'divi-dynamic-helper'),
                'std' => 'off',
            ];
            $custom_options[] = [
                'name' => 'tab-dynamic-content',
                'type' => 'subcontent-end',
            ];
            $custom_options[] = [
                'name' => 'wrap-divi-dynamic-helper',
                'type' => 'contenttab-wrapend',
            ];

            return wp_parse_args($custom_options, $options);
        }

    }

    (new PAC_DDH_Epanel_Settings())->instance()->init();
}
