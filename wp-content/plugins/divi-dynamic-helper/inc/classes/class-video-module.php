<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Video_Module')) {
    class PAC_DDH_Video_Module
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
            add_filter('et_pb_all_fields_unprocessed_et_pb_video', [$this, 'get_fields']);
            add_filter('et_pb_module_shortcode_attributes', [$this, 'maybe_filter_shortcode_attributes'], 10, 5);
            add_action('wp_ajax_et_pb_video', [$this, 'maybe_ajax_request']);
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
            // Adding dynamic content for 'src' and 'src_webm' fields
            $fields_unprocessed['src']['dynamic_content'] = 'text';
            $fields_unprocessed['src_webm']['dynamic_content'] = 'text';

            // Merge custom fields with unprocessed fields
            return wp_parse_args($custom_fields, $fields_unprocessed);
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
            if ('et_pb_video' !== $render_slug) {
                return $props;
            }
            // If dynamic attributes go
            $_dynamic_attributes = isset($props['_dynamic_attributes']) ? $props['_dynamic_attributes'] : '';
            if (!empty($_dynamic_attributes)) {
                if (preg_match("/src/", $_dynamic_attributes) || preg_match("/src_webm/", $_dynamic_attributes)) {
                    $src = isset($props['src']) ? $props['src'] : '';
                    $src_webm = isset($props['src_webm']) ? $props['src_webm'] : '';
                    if (!empty($src_webm) && empty($src)) {
                        $index = 'src_webm';
                        $dynmaic_video = $src_webm;
                    } else {
                        $index = 'src';
                        $dynmaic_video = $src;
                    }
                    if (!empty($dynmaic_video)) {
                        if (is_numeric($dynmaic_video)) {
                            $props[$index] = esc_url(wp_get_attachment_url(intval($dynmaic_video)));
                        } else if (preg_match('/^<iframe /', html_entity_decode($dynmaic_video)) && is_string($dynmaic_video)) {
                            $dom_doc = pac_ddh_create_dom(html_entity_decode($dynmaic_video));
                            $iframe = $dom_doc->getElementsByTagName('iframe');
                            if (isset($iframe->length) && 0 !== $iframe->length) {
                                $iframe_item = $iframe->item(0);
                                $props[$index] = esc_url($iframe_item->getAttribute('src'));
                            }
                        } else {
                            $props[$index] = $dynmaic_video;
                        }
                    }
                }
            }

            return $props;
        }

        /**
         * Handles the maybe AJAX request.
         *
         * @return void
         */
        public function maybe_ajax_request()
        {
            //wp_send_json_success(['success' => true, 'data' => et_builder_get_oembed($resolve)]);
            if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
                $_wpnonce = sanitize_text_field($_POST['_wpnonce']);
                if (wp_verify_nonce($_wpnonce, 'pac-ddh-ajax')) {
                    $_post_id = isset($_POST['_post_id']) ? sanitize_text_field($_POST['_post_id']) : '';
                    $dynamic_content = isset($_POST['dynamic_content']) ? sanitize_text_field($_POST['dynamic_content']) : '';
                    if (!empty($dynamic_content)) {
                        $dynamic_content = preg_replace("#^[^:/.]*[:/]+#i", "", $dynamic_content);
                        require_once ET_BUILDER_DIR.'class-et-builder-value.php';
                        require_once ET_BUILDER_DIR.'framework.php';
                        require_once ET_BUILDER_DIR.'functions.php';
                        $dynamic_content = et_builder_parse_dynamic_content($dynamic_content);
                        if (!empty($dynamic_content)) {
                            $video_url = '';
                            $resolve = $dynamic_content->resolve($_post_id);
                            if ('' !== $resolve) {
                                if (filter_var($resolve, FILTER_VALIDATE_URL)) {
                                    $video_url = $resolve;
                                }
                                if (is_numeric($resolve) && wp_attachment_is('video', $resolve)) {
                                    $video_url = wp_get_attachment_url(intval($resolve));
                                }
                                if (!empty($video_url)) {
                                    if (pac_ddh_is_youtube_video($video_url) || pac_ddh_is_vimeo_video($video_url)) {
                                        wp_send_json_success(['success' => true, 'data' => wp_oembed_get($video_url)], 200);
                                    } else {
                                        wp_send_json_success(['success' => true, 'data' => do_shortcode(sprintf('[video src="%s" /]', esc_js($video_url)))], 200);
                                    }
                                } else {
                                    wp_send_json_success(['success' => false, 'data' => ''], 200);
                                }
                            } else {
                                wp_send_json_success(['success' => false, 'data' => ''], 200);
                            }
                        }
                    }
                }
            }
        }

        /**
         * Check if the provided URL is self-hosted.
         *
         * @param string $url The URL to check.
         *
         * @return bool True if the URL is self-hosted, false otherwise.
         */
        private function is_self_hosted($url)
        {
            $host = wp_parse_url($url, 1);
            if (($host == 'localhost' || $host == 'http://localhost') || $host == wp_parse_url(get_option('siteurl'), 1)) {
                return true;
            }

            return false;
        }

    }

    (new PAC_DDH_Video_Module())->instance()->init();
}
