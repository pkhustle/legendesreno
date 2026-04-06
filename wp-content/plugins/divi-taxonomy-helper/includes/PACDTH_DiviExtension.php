<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('PACDTH_DiviExtension')) {
    class PACDTH_DiviExtension extends DiviExtension
    {
        public $gettext_domain = 'divi-taxonomy-helper';

        public $name = 'divi-taxonomy-helper';

        public function __construct($name = 'divi-taxonomy-helper', $args = [])
        {
            $this->version = PAC_DTH_PLUGIN_VERSION;
            $this->plugin_dir = plugin_dir_path(__FILE__);
            $this->plugin_dir_url = plugin_dir_url($this->plugin_dir);
            parent::__construct($name, $args);
        }
    }

    (new PACDTH_DiviExtension);
}