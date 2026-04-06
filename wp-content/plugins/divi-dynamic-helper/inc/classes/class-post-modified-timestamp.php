<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Post_Modified_TimeStamp')) {
    class PAC_DDH_Post_Modified_TimeStamp
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
            add_filter('et_builder_custom_dynamic_content_fields', [$this, 'maybe_cutom_dynamic_field'], 10, 3);
            add_filter('et_builder_resolve_dynamic_content', [$this, 'maybe_resolve_custom_dynamic_field'], 10, 6);
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
        public function maybe_cutom_dynamic_field($custom_fields, $post_id, $raw_custom_fields)
        {
            $post_type_object = get_post_type_object(get_post_type($post_id));
            if (null !== $post_type_object) {
                $post_type_label = $post_type_object->labels->singular_name;
                $custom_fields['pac_ddh_modified_date'] = [
                    'label' => esc_html(sprintf(__('%1$s Modified Date', 'divi-dynamic-helper'), $post_type_label)),
                    'type' => 'text',
                    'fields' => [
                        'before' => [
                            'label' => et_builder_i18n('Before'),
                            'type' => 'text',
                            'default' => '',
                            'show_on' => 'text',
                        ],
                        'after' => [
                            'label' => et_builder_i18n('After'),
                            'type' => 'text',
                            'default' => '',
                            'show_on' => 'text',
                        ],
                        'date_format' => [
                            'label' => __('Modified Date Format', 'divi-dynamic-helper'),
                            'type' => 'select',
                            'options' => [
                                'default' => et_builder_i18n('Default'),
                                'M j, Y' => __('Aug 6, 1999 (M j, Y)', 'divi-dynamic-helper'),
                                'F d, Y' => __('August 06, 1999 (F d, Y)', 'divi-dynamic-helper'),
                                'm/d/Y' => __('08/06/1999 (m/d/Y)', 'divi-dynamic-helper'),
                                'm.d.Y' => __('08.06.1999 (m.d.Y)', 'divi-dynamic-helper'),
                                'j M, Y' => __('6 Aug, 1999 (j M, Y)', 'divi-dynamic-helper'),
                                'l, M d' => __('Tuesday, Aug 06 (l, M d)', 'divi-dynamic-helper'),
                                'custom' => __('Custom', 'divi-dynamic-helper'),
                            ],
                            'default' => 'default',
                        ],
                        'custom_date_format' => [
                            'label' => __('Custom Modified Date Format', 'divi-dynamic-helper'),
                            'type' => 'text',
                            'default' => '',
                            'show_if' => ['date_format' => 'custom'],
                        ],
                    ],
                    'custom' => true,
                    'group' => __('Divi Dynamic Helper: Dynamic Fields', 'divi-dynamic-helper'),
                ];
            }

            return $custom_fields;
        }

        /**
         * Maybe resolves a custom dynamic field.
         *
         * @param string $content The content.
         * @param string $name The name of the field.
         * @param array $settings The settings for the field.
         * @param int $post_id The ID of the post.
         * @param string $context The context of the field.
         * @param array $overrides Any overrides for the field.
         *
         * @return string The resolved content.
         */
        public function maybe_resolve_custom_dynamic_field($content, $name, $settings, $post_id, $context, $overrides)
        {
            if ('pac_ddh_modified_date' === $name) {
                $_ = ET_Core_Data_Utils::instance();
                $def = 'et_builder_get_dynamic_attribute_field_default';
                $before = $_->array_get($settings, 'before', $def($post_id, $name, 'before'));
                $after = $_->array_get($settings, 'after', $def($post_id, $name, 'after'));
                $format = $_->array_get($settings, 'date_format', $def($post_id, $name, 'date_format'));
                $custom_format = $_->array_get($settings, 'custom_date_format', $def($post_id, $name, 'custom_date_format'));
                if ('default' === $format) {
                    $format = '';
                }
                if ('custom' === $format) {
                    $format = $custom_format;
                }
                $value = esc_html(get_the_modified_date($format, $post_id));
                $content = $before.$value.$after;

            }

            return $content;
        }

    }

    (new PAC_DDH_Post_Modified_TimeStamp())->instance()->init();
}