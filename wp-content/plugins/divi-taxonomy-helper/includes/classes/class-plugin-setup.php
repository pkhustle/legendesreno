<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DTH_Plugin_Setup')) {
    class PAC_DTH_Plugin_Setup
    {
        private static $_instance;

        /**
         * Get Class Instance
         * @return PAC_DTH_Plugin_Setup
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
            register_activation_hook(PAC_DTH_PLUGIN_FILE, [$this, 'maybe_plugin_activation']);
            add_filter('init', [$this, 'maybe_plugin_translation_ready']);
            add_filter('plugin_row_meta', [$this, 'maybe_plugin_row_meta'], 10, 4);
            add_filter('plugin_action_links', [$this, 'maybe_action_links'], 10, 2);
        }

        /**
         *  Plugin Activation
         * @return void
         */
        public function maybe_plugin_activation()
        {
            // Check Requirements
            $divi_builder = is_plugin_active('divi-builder/divi-builder.php');
            $divi_ghoster = is_plugin_active('divi-ghoster/divi-ghoster.php');
            $theme = is_child_theme() ? wp_get_theme()->parent()->get('Name') : wp_get_theme()->get('Name');
            if ((!in_array(strtolower($theme), ['divi', 'extra'])) && !$divi_builder && !$divi_ghoster) {
                deactivate_plugins(PAC_DTH_PLUGIN_BASENAME);
                wp_die(sprintf('<p>The Divi Taxonomy Helper plugin only works with Divi Theme, Extra Theme or Divi Builder plugin only. Your current active theme is <b>%s</b>. The plugin is deactivated, and you may return to your WordPress dashboard.</p><a href="%s">Go Back</a>', esc_html($theme), esc_url(admin_url('index.php'))));
            }
            // Run Migration
            $taxonomies = get_taxonomies(['public' => true, 'show_ui' => true,], 'objects');
            if (!empty($taxonomies)) {
                foreach ($taxonomies as $key => $taxonomy) {
                    if (!in_array($key, ['layout_category', 'layout_tag'])) {
                        $key = "pac_dth_$key";
                        if (!et_get_option($key)) {
                            et_update_option($key, 'on');
                        }
                    }
                }
            }
        }

        /**
         * Plugin Textdomain
         * @return void
         */
        public function maybe_plugin_translation_ready()
        {
            load_plugin_textdomain('divi-taxonomy-helper', false, wp_basename(PAC_DTH_PLUGIN_DIR).'/languages/');
        }

        /**
         * Plugin Row Meta
         *
         * @param $plugin_meta
         * @param $plugin_file
         * @param $plugin_data
         * @param $status
         *
         * @return array|mixed
         */
        public function maybe_plugin_row_meta($plugin_meta, $plugin_file, $plugin_data, $status)
        {
            if (PAC_DTH_PLUGIN_BASENAME !== $plugin_file) {
                return $plugin_meta;
            }
            /* translators: 1: URL to page, 2: Link Title. */
            $plugin_meta ['pac_dth_doc_support'] = sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://peeayecreative.com/docs/divi-taxonomy-helper/', __('Documentation & Support', 'divi-taxonomy-helper'));

            return $plugin_meta;
        }

        /**
         * Add Plugin Links
         *
         * @param $links
         * @param $file
         *
         * @return mixed
         */
        public function maybe_action_links($links, $file)
        {
            if (PAC_DTH_PLUGIN_BASENAME !== $file) {
                return $links;
            }
            $slug = is_plugin_active('divi-ghoster/divi-ghoster.php') ? 'et_'.get_option('agsdg_settings').'_options' : pac_dth_get_theme_option_name().'_options';
            $url = admin_url("admin.php?page=$slug#wrap-divi-taxonomy-helper");
            /* translators: 1: URL to page, 2: Link Title. */
            $settings_link = sprintf('<a href="%1$s">%2$s</a>', $url, __('Settings', 'divi-taxonomy-helper'));
            array_unshift($links, $settings_link);

            return $links;
        }

    }

    (new PAC_DTH_Plugin_Setup())->instance()->init();
}
