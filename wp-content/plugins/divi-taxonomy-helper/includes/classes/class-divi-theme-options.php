<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DTH_Divi_Theme_Options')) {
    class PAC_DTH_Divi_Theme_Options
    {
        private static $_instance;

        /**
         * Get Class Instance
         * @return PAC_DTH_Divi_Theme_Options
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
            add_filter('et_epanel_tab_names', [$this, 'maybe_filter_tabs']);
            add_filter('et_epanel_layout_data', [$this, 'maybe_filter_tabs_data']);
        }

        /**
         * @param $tabs
         *
         * @return array
         */
        public function maybe_filter_tabs($tabs)
        {
            return array_merge($tabs, ['divi-taxonomy-helper' => __('Divi Taxonomy Helper', 'divi-taxonomy-helper')]);
        }

        /**
         * @param $options
         *
         * @return mixed
         */
        public function maybe_filter_tabs_data($options)
        {
            $options[] = [
                'name' => 'wrap-divi-taxonomy-helper',
                'type' => 'contenttab-wrapstart',
            ];
            // Sub Navs
            $options[] = [
                "type" => 'subnavtab-start',
            ];
            $options[] = [
                'name' => 'pac-dth-tab-term-images',
                'type' => 'subnav-tab',
                'desc' => __('Taxonomy Term Images', 'divi-taxonomy-helper')
            ];
            $options[] = [
                'type' => 'subnavtab-end',
            ];
            // General
            $options[] = [
                'name' => 'pac-dth-tab-term-images',
                'type' => 'subcontent-start',
            ];
            foreach (pac_dth_get_registered_taxonomies() as $key => $taxonomy) {
                $options[] = [
                    'name' => esc_html($taxonomy),
                    'id' => "pac_dth_$key",
                    'type' => 'checkbox2',
                    'std' => 'on',
                    'desc' => __('Enable this setting to add image support to the taxonomy terms.', 'divi-taxonomy-helper'),
                ];
                $options[] = [
                    'name' => sprintf(__('Default %s Featured Image', 'divi-taxonomy-helper'), esc_html($taxonomy)),
                    'id' => "pac_dth_{$key}_featured_image",
                    'type' => 'upload',
                    'button_text' => __("Set As Default Image", 'divi-taxonomy-helper'),
                    'std' => '',
                    'desc' => __("Set a default featured image for $taxonomy.", 'divi-taxonomy-helper')
                ];
            }
            $options[] = [
                'name' => 'pac-dth-tab-term-images',
                'type' => 'subcontent-end',
            ];
            // End
            $options[] = [
                'name' => 'wrap-divi-taxonomy-helper',
                'type' => 'contenttab-wrapend',
            ];

            return $options;
        }
    }

    (new PAC_DTH_Divi_Theme_Options())->instance()->init();
}
