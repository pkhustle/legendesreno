<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Gallery_Module')) {
    class PAC_DDH_Gallery_Module
    {

        private static $_instance;

        private $dynamic_key;

        private $is_wp_custom_field = false;

        private $gallery_ids;

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
            add_filter('et_pb_all_fields_unprocessed_et_pb_gallery', [$this, 'get_fields']);
            add_filter('pre_do_shortcode_tag', [$this, 'maybe_filter_shortcode_callback'], 10, 4);
            add_filter('et_pb_module_shortcode_attributes', [$this, 'maybe_filter_shortcode_attributes'], 10, 5);
        }

        /**
         * Get processed fields.
         *
         * @param array $fields_unprocessed The unprocessed fields.
         *
         * @return array Processed fields.
         */
        public function get_fields($fields_unprocessed)
        {
            $custom_fields = [];
            $fields_unprocessed['gallery_ids']['dynamic_content'] = 'image';

            return wp_parse_args($custom_fields, $fields_unprocessed);
        }

        /**
         * Pre Filter Shortode
         *
         * @param $bool
         * @param $tag
         * @param $attr
         * @param $m
         *
         * @return mixed
         */
        public function maybe_filter_shortcode_callback($bool, $tag, $attr, $m)
        {
            // Check if the current request is from admin or an AJAX call; if yes, return props
            if (is_admin() || wp_doing_ajax()) {
                return $bool;
            }
            // Check if the rendered slug is not 'et_pb_audio'; if yes, return props
            if ('et_pb_gallery' !== $tag) {
                return $bool;
            }
            // Check if $_dynamic_attributes is empty or does not contain the word 'audio' using a regular expression.
            $_dynamic_attributes = isset($attr['_dynamic_attributes']) ? $attr['_dynamic_attributes'] : '';
            if (empty($_dynamic_attributes) || !preg_match("/gallery_ids/", $_dynamic_attributes)) {
                return $bool;
            }
            $dynamic_content = et_builder_parse_dynamic_content($attr['gallery_ids']);
            $meta_key = $dynamic_content->get_content();
            $dynamic_gallery_arr = [];
            $queried_object = get_queried_object();
            $meta_key = str_replace(PAC_DDH_DYNAMIC_META_PREFIX, '', $meta_key);
            $this->dynamic_key = $meta_key;
            /* If ACF Installed Then No Custom Field Work */
            if (class_exists('ACF')) {
                $type = 'image';
                /*$is_free_acf = true;
                if (is_plugin_active('advanced-custom-fields-pro/acf.php')) {
                    $is_free_acf = false;
                }*/
                $is_option_page_gallery = !empty(get_field_object($meta_key, 'option'));
                $field_obj = get_field_object($meta_key, $is_option_page_gallery ? 'option' : $queried_object);
                if ('gallery' === $field_obj['type']) {
                    $type = 'gallery';
                }
                $field_data = get_field($meta_key, $is_option_page_gallery ? 'option' : $queried_object);
                $field_return_format = isset($field_obj['return_format']) ? $field_obj['return_format'] : '';
                // ACF Free: Grouped Gallery
                if (!$is_option_page_gallery && 'image' === $type) {
                    if ('array' === $field_return_format) {
                        $attachment_id = $field_data['ID'];
                    } elseif ('url' === $field_return_format) {
                        $attachment_id = attachment_url_to_postid($field_data);
                    } else {
                        $attachment_id = $field_data;
                    }
                    if ($attachment_id > 0) {
                        // $field_data = pac_ddh_get_acf_gallery($attachment_id, $queried_object);
                        if ((isset($field_obj['return_format']) && 'array' === $field_obj['return_format'])
                            && (isset($field_obj['value']['ID'])
                                && $attachment_id === $field_obj['value']['ID'])
                            && (isset($field_obj['parent'])
                                && '' !== $field_obj['parent'])) {
                            $field_data = get_field(get_post_field('post_name', $field_obj['parent']), $queried_object);
                        } else {
                            if (!empty($field_obj['ID']) && isset($field_obj['parent']) && '' !== $field_obj['parent']) {
                                $field_data = get_field(get_post_field('post_name', $field_obj['parent']), $queried_object);
                            }
                        }
                        if (!empty($field_data) && is_array($field_data)) {
                            $field_data = array_filter($field_data);
                            foreach ($field_data as $v) {
                                if ('array' === $field_return_format) {
                                    $dynamic_gallery_arr[] = $v['ID'];
                                } elseif ('url' === $field_return_format) {
                                    $dynamic_gallery_arr[] = attachment_url_to_postid($v);
                                } else {
                                    $dynamic_gallery_arr[] = $v;
                                }
                            }
                        }
                    }
                }
                // ACF PRO: Grouped Gallery
                if ($is_option_page_gallery || 'gallery' === $type) {
                    if ('array' === $field_return_format) {
                        $images = wp_list_pluck($field_data, 'ID');
                        foreach ($images as $image) {
                            $dynamic_gallery_arr[] = $image;
                        }
                    } elseif ('url' === $field_return_format) {
                        foreach ($field_data as $image) {
                            $dynamic_gallery_arr[] = pac_ddh_get_attachment_id_by_url($image);
                        }
                    } else {
                        foreach ($field_data as $image) {
                            $dynamic_gallery_arr[] = $image;
                        }
                    }
                }
            } /* If PODS Installed Then No Custom Field Work. */
            elseif (class_exists('Pods')) {
                $data = pods_field($meta_key);
                if (!empty($data) && is_array($data)) {
                    foreach ($data as $v) {
                        if (isset($v['ID'])) {
                            $dynamic_gallery_arr[] = $v['ID'];
                        }
                    }
                }
            } /* If Toolset Installed Then No Custom Field Work */
            elseif (class_exists('WPCF_Loader')) {
                $field_data = get_post_meta($queried_object->ID, $meta_key);
                if (!empty($field_data)) {
                    foreach ($field_data as $v) {
                        if (filter_var($v, FILTER_VALIDATE_URL)) {
                            $dynamic_gallery_arr[] = attachment_url_to_postid($v);
                        }
                    }
                }
            } /* Else */
            else {
                $this->is_wp_custom_field = true;
            }
            // Update Gallery IDS
            if (!$this->is_wp_custom_field && !empty($dynamic_gallery_arr)) {
                $this->gallery_ids = implode(',', array_unique($dynamic_gallery_arr));
            }

            return $bool;
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
            if ('et_pb_gallery' !== $render_slug) {
                return $props;
            }
            // Check if $_dynamic_attributes is empty or does not contain the word 'audio' using a regular expression.
            $_dynamic_attributes = isset($props['_dynamic_attributes']) ? $props['_dynamic_attributes'] : '';
            if (empty($_dynamic_attributes) || !preg_match("/gallery_ids/", $_dynamic_attributes)) {
                return $props;
            }
            $gallery_ids = self::get_gallery_ids();
            if (!empty($gallery_ids)) {
                $props['gallery_ids'] = $gallery_ids;
            }

            return $props;
        }

        /**
         * Get Gallery IDS
         * @return mixed
         */
        private function get_gallery_ids()
        {
            if ($this->is_wp_custom_field) {
                global $post;
                if (is_a($post, 'WP_POST')) {
                    $_post_id = isset($post->ID) ? $post->ID : '';
                    if (!empty($_post_id)) {
                        return get_post_meta($_post_id, $this->dynamic_key, true);
                    }
                }
            }

            return $this->gallery_ids;
        }
    }

    (new PAC_DDH_Gallery_Module())->instance()->init();
}