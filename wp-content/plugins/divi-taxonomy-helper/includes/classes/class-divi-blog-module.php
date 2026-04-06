<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DTH_Divi_Blog_Module')) {
    class PAC_DTH_Divi_Blog_Module
    {
        private static $_instance;

        /**
         * Get Class Instance
         * @return PAC_DTH_Divi_Blog_Module
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
            add_filter('et_pb_all_fields_unprocessed_et_pb_blog', [$this, 'maybe_filter_fields']);
            add_filter('et_pb_module_shortcode_attributes', [$this, 'maybe_filter_attributes'], 10, 5);
        }

        /**
         * Filter Fields
         *
         * @param $fields_unprocessed
         *
         * @return array
         */
        public function maybe_filter_fields($fields_unprocessed)
        {
            $custom_fields = [];
            $custom_fields['use_current_category_level'] = [
                'label' => esc_html__('Only Show Posts In Current Category Level', 'divi-taxonomy-helper'),
                'type' => 'yes_no_button',
                'option_category' => 'basic_option',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'description' => esc_html__('Choose to limit the posts that are shown to only the current category level instead of showing all posts from other levels when using multiple levels of parent and child category hierarchy.', 'divi-taxonomy-helper'),
                'toggle_slug' => 'main_content',
                'default' => 'off',
                'show_if' => [
                    'use_current_loop' => 'on',
                ],
            ];

            return wp_parse_args($custom_fields, $fields_unprocessed);
        }

        /**
         * Filter Props
         *
         * @param $props
         * @param $attrs
         * @param $render_slug
         * @param $_address
         * @param $content
         *
         * @return mixed
         */
        public function maybe_filter_attributes($props, $attrs, $render_slug, $_address, $content)
        {
            // Return If Frontend Builder
            if (function_exists('et_fb_is_enabled') && et_fb_is_enabled()) {
                return $props;
            }
            // Return If Backend Builder
            if (function_exists('et_builder_bfb_enabled') && et_builder_bfb_enabled()) {
                return $props;
            }
            // Return If Admin/Ajax
            if (is_admin() || wp_doing_ajax()) {
                return $props;
            }
            // Return If Not Slug Match
            if ('et_pb_blog' != $render_slug) {
                return $props;
            }
            if (isset($attrs['use_current_category_level']) && 'on' === $attrs['use_current_category_level']) {
                $current_term = get_queried_object();
                if (pac_dth_get_is_taxonomy_page($current_term->taxonomy)) {
                    add_action('pre_get_posts', function ($query) use ($current_term) {
                        if (is_admin() && !$query->is_main_query()) {
                            return;
                        }
                        $term_id = isset($current_term->term_id) ? $current_term->term_id : '';
                        if (!empty($term_id)) {
                            $query->set('tax_query', [
                                [
                                    'taxonomy' => $current_term->taxonomy,
                                    'field' => 'term_id',
                                    'terms' => [$term_id],
                                    'operator' => 'IN',
                                    'include_children' => false,
                                ]
                            ]);
                        }
                    });
                }
            }

            return $props;
        }
    }

    (new PAC_DTH_Divi_Blog_Module())->instance()->init();
}