<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_ACF_Dynamic_Options')) {
    class PAC_DDH_ACF_Dynamic_Options
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
            add_filter('et_builder_custom_dynamic_content_fields', [$this, 'maybe_dynamic_content_fields'], 999, 3);
            add_filter('et_builder_resolve_dynamic_content', [$this, 'maybe_resolve_dynamic_content'], 999, 6);
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
        public function maybe_dynamic_content_fields($custom_fields, $post_id, $raw_custom_fields)
        {
            $options = $this->get_option_fields();
            if (!empty(array_filter($options))) {
                foreach ($options as $option) {
                    foreach ($option['fields'] as $field) {
                        $custom_fields[$field['name']] = [
                            'label' => esc_html($field['label']),
                            'type' => 'any',
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
                                'default_label' => [
                                    //'type' => 'warning',
                                    'label' => __('Show Default Label', 'divi-dynamic-helper'),
                                    'description' => __('Choose to show an icon inside the search field.', 'divi-dynamic-helper'),
                                    'type' => 'yes_no_button',
                                    'option_category' => 'configuration',
                                    'options' => [
                                        'on' => et_builder_i18n('Yes'),
                                        'off' => et_builder_i18n('No'),
                                    ],
                                    'default' => 'off',
                                ],
                            ],
                            'meta_key' => $field['name'], // phpcs:ignore
                            'custom' => false,
                            'group' => sprintf(__('Divi Dynamic Helper: %s', 'divi-dynamic-helper'), esc_html($option['title'])),
                        ];
                    }
                }
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
        public function maybe_resolve_dynamic_content($content, $name, $settings, $post_id, $context, $overrides)
        {
            $options = $this->get_option_fields();
            if (!empty(array_filter($options))) {
                foreach ($options as $option) {
                    foreach ($option['fields'] as $field) {
                        $field_name = $field['name'];
                        $field_type = $field['type'];
                        if ($field_name === $name) {
                            $et_utils = ET_Core_Data_Utils::instance();
                            $def = 'et_builder_get_dynamic_attribute_field_default';
                            $before = $et_utils->array_get($settings, 'before', $def($post_id, $name, 'before'));
                            $after = $et_utils->array_get($settings, 'after', $def($post_id, $name, 'after'));
                            $default_label = $et_utils->array_get($settings, 'default_label', $def($post_id, $name, 'default_label'));
                            // ACF Data
                            $field_obj = get_field_object($field_name, 'option');
                            $field_data = get_field($field_name, 'option');
                            $field_label = 'on' === $default_label ? $field_obj['label'].': ' : '';
                            $field_return_format = isset($field_obj['return_format']) ? $field_obj['return_format'] : '';
                            $field_value = '';
                            if (in_array($field_type, [
                                'text',
                                'number',
                                'url',
                                'email',
                                'textarea',
                                'date_time_picker',
                                'date_picker',
                                'time_picker',
                                'color_picker',
                                'range'
                            ])) {
                                $field_value = $field_data;
                            }
                            // If field type 'wysiwyg'
                            if ('wysiwyg' === $field_type) {
                                $field_value = $field_data;
                            }
                            // If a field type 'selects'
                            if ('select' === $field_type) {
                                if ($field_obj['multiple']) {
                                    $field_value = implode(',', array_values(array_intersect_key($field_obj['choices'], array_change_key_case(array_flip($field_data)))));
                                } else {
                                    $field_value = $field_data;
                                }
                            }
                            // If field type 'checkbox'
                            if ('checkbox' === $field_type) {
                                $field_value = implode(', ', $field_data);
                            }
                            // If field type 'radio'
                            if ('radio' === $field_type) {
                                $field_value = $field_data;
                            }
                            // If field type 'image'
                            if ('image' === $field_type) {
                                if ('array' === $field_return_format) {
                                    $field_value = wp_get_attachment_image(pac_ddh_get_attachment_id_by_url($field_data['url']), 'full');
                                } elseif ('url' === $field_return_format) {
                                    $field_value = wp_get_attachment_image(pac_ddh_get_attachment_id_by_url($field_data), 'full');
                                } else {
                                    $field_value = wp_get_attachment_image($field_data, 'full');
                                }
                            }
                            // If field type 'gallery'
                            if ('gallery' === $field_type) {
                                $html_output = "<ul class='ddh__gallery'>";
                                if ('array' === $field_return_format) {
                                    $images = wp_list_pluck($field_data, 'url');
                                    foreach ($images as $image) {
                                        $attachment_id = pac_ddh_get_attachment_id_by_url($image);
                                        $html_output .= sprintf('<li><a href="%1$s">%2$s</a></li>', wp_get_attachment_url($attachment_id), wp_get_attachment_image($attachment_id, 'full'));
                                    }
                                } elseif ('url' === $field_return_format) {
                                    foreach ($field_data as $image) {
                                        $attachment_id = pac_ddh_get_attachment_id_by_url($image);
                                        $html_output .= sprintf('<li><a href="%1$s">%2$s</a></li>', wp_get_attachment_url($attachment_id), wp_get_attachment_image($attachment_id, 'full'));
                                    }
                                } else {
                                    foreach ($field_data as $image) {
                                        $attachment_id = $image;
                                        $html_output .= sprintf('<li><a href="%1$s">%2$s</a></li>', wp_get_attachment_url($attachment_id), wp_get_attachment_image($attachment_id, 'full'));
                                    }
                                }
                                $html_output .= "</ul>";
                                $field_value = $html_output;
                            }
                            // If field type 'file'
                            if ('file' === $field_type) {
                                if ('array' === $field_return_format) {
                                    $field_value = $field_data['url'];
                                } elseif ('url' === $field_return_format) {
                                    $field_value = $field_data;
                                } else {
                                    $field_value = wp_get_attachment_url($field_data);
                                }
                                $field_value = sprintf('<a href="%1$s" download>%2$s</a>', esc_url($field_value), wp_basename($field_value));
                            }
                            // If field type 'oembed'
                            if ('oembed' === $field_type) {
                                $field_value = $field_data;
                            }
                            // If field type 'true_false'
                            if ('true_false' === $field_type) {
                                $field_value = esc_html($field_obj['message']).' '.(!empty($field_data) ? __('Yes', 'divi-dynamic-helper') : __('No', 'divi-dynamic-helper'));
                            }
                            // If field type 'link'
                            if ('link' === $field_type) {
                                if ('array' === $field_return_format) {
                                    $field_value = sprintf('<a href="%1$s">%2$s</a>', esc_url($field_data['url']), esc_html($field_data['title']));
                                } else {
                                    $field_value = sprintf('<a href="%1$s">%2$s</a>', esc_url($field_data), $field_data);
                                }
                            }
                            // If field type 'page_link'
                            if ('page_link' === $field_type) {
                                if ($field_obj['multiple'] && is_array($field_data)) {
                                    /*$field_value = implode('<br>', array_map(function ($index, $link) {
                                        $number = $index + 1;

                                        return $number.': <a href="'.esc_url($link).'">'.wp_basename($link).'</a>';
                                    }, array_keys($field_data), $field_data));*/
                                    $html_output = "<ul class='ddh__page_links'>";
                                    foreach ($field_data as $link) {
                                        $link_id = pac_ddh_get_post_id_by_guid($link);
                                        $link_label = ucwords(wp_basename($link));
                                        if (!empty($link_id)) {
                                            $html_output .= sprintf('<li class"post-%4$s"><a href="%1$s">%2$s</a> <span>(%3$s)</span></li>', esc_url(get_the_permalink($link_id)), esc_html($link_label), esc_html(get_post_type($link_id)), esc_attr($link_id));
                                        } else {
                                            $html_output .= sprintf('<li><a href="%1$s">%2$s</a></li>', esc_url($link), esc_html($link_label));
                                        }
                                    }
                                    $html_output .= "</ul>";
                                    $field_value = $html_output;
                                } else {
                                    $field_value = sprintf('<a href="%1$s">%2$s</a>', esc_url($field_data), esc_html(wp_basename($field_data)));
                                }
                            }
                            // If field type 'post_object' || field type 'relationship'
                            if ('post_object' === $field_type || 'relationship' === $field_type) {
                                $posts = 'object' === $field_return_format ? wp_list_pluck($field_data, 'ID') : $field_data;
                                $html_output = "<ul class='ddh__posts_list'>";
                                foreach ($posts as $post) {
                                    $html_output .= sprintf('<li class"post-%4$s"><a href="%1$s">%2$s</a> <span>(%3$s)</span></li>', esc_url(get_the_permalink($post)), esc_html(get_the_title($post)), esc_html(get_post_type($post)), esc_attr($post));
                                }
                                $html_output .= "</ul>";
                                $field_value = $html_output;
                            }
                            // If field type 'taxonomy'
                            if ('taxonomy' === $field_type) {
                                $terms = 'object' === $field_return_format ? wp_list_pluck($field_data, 'term_id') : $field_data;
                                $html_output = "<ul class='ddh__terms_list'>";
                                foreach ($terms as $term) {
                                    $term_obj = get_term($term);
                                    $html_output .= sprintf('<li class"term-%3$s"><a href="%1$s">%2$s</a></li>', esc_url(get_term_link($term)), esc_html($term_obj->name), esc_attr($term));
                                }
                                $html_output .= "</ul>";
                                $field_value = $html_output;
                            }
                            // If field type 'user'
                            if ('user' === $field_type) {
                                if ($field_obj['multiple']) {
                                    if ('array' === $field_return_format || 'object' === $field_return_format) {
                                        $users = wp_list_pluck($field_data, 'ID');
                                    } else {
                                        $users = $field_data;
                                    }
                                } else {
                                    if ('array' === $field_return_format) {
                                        $users[] = $field_data['ID'];
                                    } elseif ('object' === $field_return_format) {
                                        $users[] = $field_data->data->ID;

                                    } else {
                                        $users[] = $field_data;
                                    }
                                }
                                $html_output = "<ul class='ddh__users_list'>";
                                foreach ($users as $user) {
                                    $user_obj = get_user_by('ID', $user);
                                    if ($user_obj && $user_obj->exists()) {
                                        $roles = ucwords(implode(', ', $user_obj->roles));
                                        $html_output .= sprintf('<li class"term-%1$s">%2$s <span>(%3$s)</span></li>', esc_attr($user_obj->ID), esc_html($user_obj->first_name.' '.$user_obj->last_name), esc_html($roles));
                                    }
                                }
                                $html_output .= "</ul>";
                                $field_value = $html_output;
                            }
                            // If field type 'google_map'
                            if ('google_map' === $field_type) {
                                $field_value = sprintf('<iframe style="border:0;overflow:hidden" width="100%%" height="%4$s" src="https://www.google.com/maps/embed/v1/place?q=%1$s&key=%2$s&zoom=%3$s"></iframe>', esc_url($field_data['address']), esc_attr(et_pb_get_google_api_key()), esc_attr($field_obj['zoom']), esc_attr($field_obj['height']));
                            }
                            $content = wp_kses_post($before.$field_label.$field_value.$after);

                        }
                    }
                }
            }

            return $content;
        }

        /**
         * Get Option Pages Fields
         * @return array
         */
        private function get_option_fields()
        {
            $data = [];
            $fields = get_field_objects('option');
            if (is_array($fields) && !empty(array_filter($fields))) {
                foreach ($fields as $field_name => $field) {
                    // Check if the field has a parent
                    if (isset($field['parent'])) {
                        // Get the parent ID
                        $parent_id = $field['parent'];
                        $parent_title = get_the_title($parent_id);
                        $parent_excerpt = get_post_field('post_excerpt', $parent_id);
                        // $options_page = acf_get_options_page($parent_excerpt);
                        $parent_index = str_replace(['-', '_'], '_', $parent_excerpt).'_'.$parent_id;
                        // Check if the parent ID already exists in the array
                        if (!isset($data[$parent_index])) {
                            // If not, initialize an array for the parent ID
                            $data[$parent_index] = [];
                        }
                        // Add the field to the array under the parent ID
                        $data[$parent_index]['ID'] = $parent_id;
                        $data[$parent_index]['title'] = $parent_title;
                        $data[$parent_index]['fields'][] = [
                            'ID' => $field['ID'],
                            'label' => $field['label'],
                            'name' => $field['name'],
                            'type' => $field['type'],
                        ];

                    }
                }
                usort($data, function ($a, $b) {
                    return strcmp($a['title'], $b['title']);
                });
            }

            return $data;
        }
    }

    (new PAC_DDH_ACF_Dynamic_Options())->instance()->init();
}