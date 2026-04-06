<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PAC_DDH_Plugin_Setup')) {
    class PAC_DDH_Plugin_Setup
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
            register_activation_hook(PAC_DDH_PLUGIN_FILE, [$this, 'maybe_plugin_activation']);
            add_action('init', [$this, 'maybe_plugin_translation_ready']);
            add_filter('plugin_row_meta', [$this, 'maybe_plugin_row_meta'], 10, 4);
            add_filter('plugin_action_links', [$this, 'maybe_action_links'], 10, 2);
            add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_scripts']);
            add_action('admin_enqueue_scripts', [$this, 'maybe_enqueue_scripts']);
        }

        /**
         * Checks if the required plugins and themes are active for the functionality of the Divi Dynamic Helper plugin.
         * Deactivates the plugin if the necessary conditions are not met.
         */
        public function maybe_plugin_activation()
        {
            $divi_builder = is_plugin_active('divi-builder/divi-builder.php');
            $divi_ghoster = is_plugin_active('divi-ghoster/divi-ghoster.php');
            $theme = is_child_theme() ? wp_get_theme()->parent()->get('Name') : wp_get_theme()->get('Name');
            if ((!in_array(strtolower($theme), ['divi', 'extra'])) && !$divi_builder && !$divi_ghoster) {
                deactivate_plugins(PAC_DDH_PLUGIN_BASENAME);
                wp_die(sprintf('<p>The Divi Dynamic Helper plugin only works with Divi Theme, Extra Theme or Divi Builder plugin only. Your current active theme is <b>%s</b>. The plugin is deactivated, and you may return to your WordPress dashboard.</p><a href="%s">Go Back</a>', esc_html($theme), esc_url(admin_url('index.php'))));
            }
        }

        /**
         * Makes the plugin translation ready by loading its text domain.
         *
         * This function loads the text domain for the plugin, enabling translation support.
         *
         * @return void
         * @since 1.0.0
         * @access public
         */
        public function maybe_plugin_translation_ready()
        {
            load_plugin_textdomain('divi-dynamic-helper', false, wp_basename(PAC_DDH_PLUGIN_DIR).'/languages/');
        }

        /**
         * Add meta-link to plugin row.
         *
         * @param array $plugin_meta An array of the plugin's metadata.
         * @param string $plugin_file Path to the plugin file relative to the plugins' directory.
         * @param array $plugin_data An array of plugin data.
         * @param string $status Status of the plugin from the plugin list table.
         *
         * @return array Modified array of plugin metadata.
         */
        public function maybe_plugin_row_meta($plugin_meta, $plugin_file, $plugin_data, $status)
        {
            if (PAC_DDH_PLUGIN_BASENAME !== $plugin_file) {
                return $plugin_meta;
            }
            /* translators: 1: URL to page, 2: Link Title. */
            $plugin_meta ['pac_ddh_doc_support'] = sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'ttps://www.peeayecreative.com/docs/divi-dynamic-helper/', __('Documentation & Support', 'divi-dynamic-helper'));

            return $plugin_meta;
        }

        /**
         * Add action links to the plugin on the Plugins page.
         *
         * This function hooks into the 'plugin_action_links' filter to add a settings link for the plugin.
         *
         * @param array $links Array of plugin action links.
         * @param string $file Plugin base name.
         *
         * @return array Modified array of plugin action links.
         */
        public function maybe_action_links($links, $file)
        {
            if (PAC_DDH_PLUGIN_BASENAME !== $file) {
                return $links;
            }
            $theme = is_child_theme() ? wp_get_theme()->parent()->get('Name') : wp_get_theme()->get('Name');
            $option_name = 'divi' === strtolower($theme) ? 'et_divi_options' : 'et_extra_options';
            $slug = is_plugin_active('divi-ghoster/divi-ghoster.php') ? 'et_'.get_option('agsdg_settings').'_options' : $option_name;
            $url = admin_url("admin.php?page=$slug#wrap-divi-dynamic-helper");
            /* translators: 1: URL to page, 2: Link Title. */
            $settings_link = sprintf('<a href="%1$s">%2$s</a>', $url, __('Settings', 'divi-dynamic-helper'));
            array_unshift($links, $settings_link);

            return $links;
        }

        /**
         * Enqueues necessary scripts and styles.
         * @return void
         */
        public function maybe_enqueue_scripts()
        {

            $min = pac_ddh_is_debug_mod() ? '' : '.min';
            global $post;
            $_post_id = isset($post->ID) ? $post->ID : '';
            wp_enqueue_style('pac-ddh-head', pac_ddh_get_plugin_url("/assets/css/main$min.css"), [], PAC_DDH_PLUGIN_VERSION);
            wp_enqueue_script('pac-ddh-head', pac_ddh_get_plugin_url("/assets/js/main$min.js"), ['jquery'], PAC_DDH_PLUGIN_VERSION, true);
            wp_localize_script('pac-ddh-head', 'pac_ddh_obj', [
                'ajaxURL' => esc_js(admin_url('admin-ajax.php')),
                'ajaxNonce' => wp_create_nonce('pac-ddh-ajax'),
                'pluginURL' => esc_js(PAC_DDH_PLUGIN_URL),
                'blogURL' => get_bloginfo('url'),
                'postID' => $_post_id,
                'isAdmin' => is_admin(),
                'isUserLoggedIn' => is_user_logged_in(),
                'i18n' => [
                    'dynamicAudioText' => __('Your Dynamic Audio will display here.', 'divi-dynamic-helper'),
                    'dynamicGalleryText' => __('Your Dynamic Gallery will display here.', 'divi-dynamic-helper'),
                    'dynamicVideoText' => __('Your Dynamic Video will display here.', 'divi-dynamic-helper'),
                    'dynamicCodeText' => __('Your Dynamic Code will display here.', 'divi-dynamic-helper'),
                    'dynamicBarCounter' => __('Your Dynamic Bar Counter will display here.', 'divi-dynamic-helper'),
                    'dynamicNumberCounter' => 50,
                    'dynamicCircleCounter' => 50,
                ],
            ]);
        }
    }

    (new PAC_DDH_Plugin_Setup())->instance()->init();
}
