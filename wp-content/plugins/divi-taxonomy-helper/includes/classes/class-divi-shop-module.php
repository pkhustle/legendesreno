<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DTH_Divi_Shop_Module')) {
    class PAC_DTH_Divi_Shop_Module
    {
        private static $_instance;

        /**
         * Get Class Instance
         * @return PAC_DTH_Divi_Shop_Module
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
            add_filter('et_pb_all_fields_unprocessed_et_pb_shop', [$this, 'maybe_filter_fields']);
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
            $custom_fields['use_current_level_products'] = [
                'label' => esc_html__('Only Show Products In Current Category Level', 'divi-taxonomy-helper'),
                'type' => 'yes_no_button',
                'option_category' => 'configuration',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'description' => esc_html__('Choose to limit the products that are shown to only the current category level instead of showing all products from other levels when using multiple levels of parent and child category hierarchy.', 'divi-taxonomy-helper'),
                'toggle_slug' => 'main_content',
                'default' => 'off',
                'show_if' => [
                    'use_current_loop' => 'on',
                    //'function.isTBLayout' => 'on',
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
            if ('et_pb_shop' != $render_slug) {
                return $props;
            }
            if (isset($props['use_current_level_products']) && 'on' === $props['use_current_level_products']) {
                add_action('pre_get_posts', function ($query) {
                    if (is_admin()
                        && !$query->is_main_query()
                        && (isset($query->query_vars['post_type'])
                            && 'product' !== $query->query_vars['post_type'])
                        && !pac_dth_get_is_taxonomy_page('product_cat')
                    ) {
                        return;
                    }
                    $current_term = get_queried_object();
                    $term_children = get_term_children($current_term->term_id, 'product_cat');
                    if (!is_wp_error($term_children)) {
                        $tax_query = $query->get('tax_query');
                        $query->set('tax_query', [
                            'relation' => 'AND',
                            $tax_query,
                            [
                                'taxonomy' => 'product_cat',
                                'field' => 'term_id',
                                'terms' => $term_children,
                                'operator' => 'NOT IN',
                                'include_children' => false,
                            ],
                        ]);
                    }
                });
            }

            return $props;
        }
    }

    (new PAC_DTH_Divi_Shop_Module())->instance()->init();
}
/*$child_term_ids = get_term_children($current_term->term_id, 'product_cat');
                   if (!empty($child_term_ids)) {
                       $tax_query = $query->get('tax_query');
                       $tax_query[] = [
                           'taxonomy' => 'product_cat',
                           'field' => 'term_id',
                           'parent' => 0,
                           'terms' => $child_term_ids,
                           'operator' => 'NOT IN'
                       ];
                       $query->set('tax_query', $tax_query);
                   }*/