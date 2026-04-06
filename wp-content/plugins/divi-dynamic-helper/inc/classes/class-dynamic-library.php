<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Dynamic_Library')) {
    class PAC_DDH_Dynamic_Library
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
            add_filter('et_builder_custom_dynamic_content_fields', [$this, 'maybe_custom_dynamic_fields'], 10, 3);
        }

        /**
         * Add custom dynamic field to Divi Builder.
         *
         * @param array $custom_fields An array of custom fields.
         * @param int $post_id The ID of the post.
         * @param array $raw_custom_fields An array of raw custom fields.
         *
         * @return array Modified array of custom fields.
         */
        public function maybe_custom_dynamic_fields($custom_fields, $post_id, $raw_custom_fields)
        {
            $layouts = get_posts(['post_type' => 'et_pb_layout', 'posts_per_page' => '-1']);
            if (!empty($layouts) && !is_wp_error($layouts)) {
                $layouts = wp_list_pluck($layouts, 'post_title', 'ID');
                foreach ($layouts as $layout_index => $layout_value) {
                    $custom_fields["pac_ddh_library_layout_".$layout_index] = [
                        'label' => $layout_value,
                        'type' => 'any',
                        'fields' => [
                            'before' => [
                                'label' => $layout_value,
                                'type' => 'text',
                                'default' => '[et_pb_section global_module="'.$layout_index.'"]',
                                'value' => '[et_pb_section global_module="'.$layout_index.'"]',
                                'show_on' => 'text',
                            ],
                        ],
                        'meta_key' => 'pac_ddh_library_meta_key_'.$layout_index, // phpcs:ignore
                        'group' => __('Divi Library - Divi Dynamic Helper', 'divi-dynamic-helper'),
                    ];
                }
            }

            return $custom_fields;
        }
    }

    (new PAC_DDH_Dynamic_Library())->instance()->init();
}
