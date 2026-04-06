<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Audio_Module')) {
    class PAC_DDH_Audio_Module
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
            add_filter('et_pb_all_fields_unprocessed_et_pb_audio', [$this, 'get_fields']);
            add_filter('et_pb_module_shortcode_attributes', [$this, 'maybe_filter_shortcode_attributes'], 10, 5);
            add_filter('shortcode_atts_audio', [$this, 'maybe_filter_audio_shortcode'], 10, 4);
            add_action('wp_ajax_et_pb_audio', [$this, 'maybe_ajax_request']);
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
            $fields_unprocessed['audio']['dynamic_content'] = 'text';

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
            if ('et_pb_audio' !== $render_slug) {
                return $props;
            }
            // Check if $_dynamic_attributes is empty or does not contain the word 'audio' using a regular expression.
            $_dynamic_attributes = isset($props['_dynamic_attributes']) ? $props['_dynamic_attributes'] : '';
            if (empty($_dynamic_attributes) || !preg_match("/audio/", $_dynamic_attributes)) {
                return $props;
            }
            $audio_url = isset($props['audio']) ? $props['audio'] : '';
            if (!empty($audio_url) && is_numeric($audio_url)) {
                $props['audio'] = esc_url(wp_get_attachment_url(intval($audio_url)));
            }

            return $props;
        }

        /**
         * Filter Shortcode Attributes Of Audio
         *
         * @param $out
         * @param $pairs
         * @param $atts
         * @param $shortcode
         *
         * @return mixed
         */
        public function maybe_filter_audio_shortcode($out, $pairs, $atts, $shortcode)
        {

            $out['preload'] = 'metadata';

            return $out;
        }

        /**
         * Ajax Request To Show Audio When Builder Enabled
         *
         * @return void
         */
        public function maybe_ajax_request()
        {
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
                            $resolve = $dynamic_content->resolve($_post_id);
                            $audio_url = '';
                            if ('' !== $resolve) {
                                if (filter_var($resolve, FILTER_VALIDATE_URL)) {
                                    $audio_url = $resolve;
                                }
                                if (is_numeric($resolve) && wp_attachment_is('audio', $resolve)) {
                                    $audio_url = wp_get_attachment_url(intval($resolve));
                                }
                                if (!empty($audio_url)) {
                                    wp_send_json_success(['success' => true, 'data' => do_shortcode(sprintf('[audio src="%s" /]', esc_js($audio_url)))], 200);
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
    }

    (new PAC_DDH_Audio_Module())->instance()->init();
}
