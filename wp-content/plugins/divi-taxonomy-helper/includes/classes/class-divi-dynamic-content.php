<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DTH_Divi_Dynamic_Content')) {
    class PAC_DTH_Divi_Dynamic_Content
    {
        private static $_instance;

        /**
         * Get Class Instance
         * @return PAC_DTH_Divi_Dynamic_Content
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
            add_filter('et_builder_resolve_dynamic_content', [$this, 'maybe_filter_dynamic_content'], 999, 6);
        }

        /**
         * Filter Dynamic Content
         *
         * @param $content
         * @param $name
         * @param $settings
         * @param $post_id
         * @param $context
         * @param $overrides
         *
         * @return mixed|string
         */
        public function maybe_filter_dynamic_content($content, $name, $settings, $post_id, $context, $overrides)
        {
            // Return Case
            if ('edit' === $context) {
                return $content;
            }
            // Return Case
            if (!is_category() && !is_tag() && !is_tax()) {
                return $content;
            }
            // Return Case
            $queried_object = get_queried_object();
            if (!isset($queried_object->taxonomy)) {
                return $content;
            }
            $taxonomy = $queried_object->taxonomy;
            $term_id = $queried_object->term_id;
            $d_featured_img = et_get_option("pac_dth_{$taxonomy}_featured_image");
            $d_featured_img_alt = get_post_meta(pac_dth_get_attachment_id($d_featured_img), '_wp_attachment_image_alt', true);
            $d_featured_img_title = get_the_title(pac_dth_get_attachment_id($d_featured_img));
            $attachment_id = (int)get_term_meta($term_id, 'thumbnail_id', true);
            if ('post_featured_image' === $name) {
                $url = wp_get_attachment_image_url($attachment_id, 'full');
                $content = !empty($url) ? esc_url($url) : esc_url($d_featured_img);
            }
            if ('post_featured_image_alt_text' === $name) {
                $img_alt = $attachment_id ? get_post_meta($attachment_id, '_wp_attachment_image_alt', true) : '';
                $content = $img_alt ? esc_attr($img_alt) : $d_featured_img_alt;
            }
            if ('post_featured_image_title_text' === $name) {
                $img_title = $attachment_id ? get_the_title($attachment_id) : '';
                $content = $img_title ? esc_attr($img_title) : $d_featured_img_title;
            }

            return $content;
        }
    }

    (new PAC_DTH_Divi_Dynamic_Content())->instance()->init();
}
