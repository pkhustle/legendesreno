<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('TaxonomyHelper')):
    class TaxonomyHelper extends ET_Builder_Module
    {
        protected $module_credits = [
            'module_uri' => 'https://peeayecreative.com/product/divi-taxonomy-helper/',
            'author' => 'Pee-Aye Creative',
            'author_uri' => 'https://www.peeayecreative.com/',
        ];

        /**
         * Init Module
         * @return void
         */
        public function init()
        {
            $this->name = __('Divi Taxonomy Helper', 'divi-taxonomy-helper');
            $this->slug = 'pac_dth_taxonomy_list';
            // $this->icon_path = plugin_dir_path(__FILE__) . "balloon.svg";
            $this->vb_support = 'on';
            $this->settings_modal_toggles = [
                'general' => [
                    'toggles' => [
                        'list' => __('Content', 'divi-taxonomy-helper'),
                        'layout' => __('Layout', 'divi-taxonomy-helper'),
                        'elements' => __('Elements', 'divi-taxonomy-helper'),
                        'links' => __('Links', 'divi-taxonomy-helper'),
                    ],
                ],
                'advanced' => [
                    'toggles' => [
                        'taxonomies' => __('Taxonomies', 'divi-taxonomy-helper'),
                        'image' => __('Images', 'divi-taxonomy-helper'),
                        'icon' => [
                            'title' => __('Icons', 'divi-taxonomy-helper'),
                            'tabbed_subtoggles' => true,
                            'sub_toggles' => [
                                'default_toggle' => [
                                    'name' => __('Icons', 'divi-taxonomy-helper')
                                ],
                                'design_toggle' => [
                                    'name' => __('Design', 'divi-taxonomy-helper')
                                ],
                            ],
                        ],
                        'title' => __('Title Text', 'divi-taxonomy-helper'),
                        'count' => __('Count Text', 'divi-taxonomy-helper'),
                        'description' => __('Description Text', 'divi-taxonomy-helper'),
                    ],
                ],
            ];
            $this->custom_css_fields = [
                'custom_css_fields_taxonomies' => [
                    'label' => esc_html__('Taxonomies', 'divi-taxonomy-helper'),
                    'selector' => implode(',', ['%%order_class%% .pac_dth_taxonomies']),
                    'no_space_before_selector' => true,
                    'important' => 'all',
                ],
                'custom_css_fields_image' => [
                    'label' => esc_html__('Image', 'divi-taxonomy-helper'),
                    'selector' => implode(',', ['%%order_class%% .pac_dth_image img']),
                    'no_space_before_selector' => true,
                    'important' => 'all',
                ],
                'custom_css_fields_icon' => [
                    'label' => esc_html__('Icon', 'divi-taxonomy-helper'),
                    'selector' => implode(',', ['%%order_class%% .pac_dth_icon a']),
                    'no_space_before_selector' => true,
                    'important' => 'all',
                ],
                'custom_css_fields_title_text' => [
                    'label' => esc_html__('Title Text', 'divi-taxonomy-helper'),
                    'selector' => implode(',', ['%%order_class%% .pac_dth_content .pac_dth_title']),
                    'no_space_before_selector' => true,
                    'important' => 'all',
                ],
                'custom_css_fields_description_text' => [
                    'label' => esc_html__('Description Text', 'divi-taxonomy-helper'),
                    'selector' => implode(',', ['%%order_class%% .pac_dth_content .pac_dth_desc']),
                    'no_space_before_selector' => true,
                    'important' => 'all',
                ],
                'custom_css_fields_button' => [
                    'label' => esc_html__('Button', 'divi-taxonomy-helper'),
                    'selector' => implode(',', ['%%order_class%% .pac_dth_readmore_wrap .et_pb_button']),
                    'no_space_before_selector' => true,
                    'important' => 'all',
                ],
            ];
        }

        /**
         * Add Advanced Fields
         * @return array|array[]
         */
        public function get_advanced_fields_config()
        {
            $advanced_fields = [];
            $advanced_fields['text'] = false;
            $advanced_fields['text_shadow'] = false;
           // $advanced_fields['fonts'] = false;
            $advanced_fields['link_options'] = false;
            $advanced_fields['fonts']['title'] = [
                'label' => __('Title', 'divi-taxonomy-helper'),
                'css' => [
                    'main' => '%%order_class%% .pac_dth_title, %%order_class%% .pac_dth_title a',
                    'important' => 'all',
                ],
                'header_level' => [
                    'default' => 'h3',
                    'computed_affects' => [
                        '__terms',
                    ],
                ],
                'text_align' => ['options' => et_builder_get_text_orientation_options(['justified'])],
                'toggle_slug' => 'title',
            ];
            $advanced_fields['fonts']['count'] = [
                'label' => __('Count', 'divi-taxonomy-helper'),
                'css' => [
                    'main' => '%%order_class%% .pac_dth_count',
                    'important' => 'all',
                ],
                'header_level' => [
                    'default' => 'h4',
                    'computed_affects' => [
                        '__terms',
                    ],
                ],
                'text_color' => ['default' => '#8c8c8c'],
                'text_align' => ['options' => et_builder_get_text_orientation_options(['justified'])],
                'toggle_slug' => 'count',
            ];
            $advanced_fields['fonts']['description'] = [
                'label' => __('Description', 'divi-taxonomy-helper'),
                'css' => [
                    'main' => '%%order_class%% .pac_dth_content .pac_dth_desc',
                    'important' => 'all',
                ],
                'toggle_slug' => 'description',
            ];
            $advanced_fields['box_shadow']['taxonomies'] = [
                'css' => [
                    'main' => '%%order_class%% .pac_dth_taxonomy_inner',
                ],
                'toggle_slug' => 'taxonomies',
            ];
            $advanced_fields['box_shadow']['image'] = [
                'css' => [
                    'main' => '%%order_class%% .pac_dth_taxonomy img',
                ],
                'toggle_slug' => 'image',
            ];
            $advanced_fields['box_shadow']['icon'] = [
                'css' => [
                    'main' => '%%order_class%% .pac_dth_icon a',
                ],
                'toggle_slug' => 'icon',
                'sub_toggle' => 'design_toggle',
            ];
            $advanced_fields['borders']['taxonomies'] = [
                'css' => [
                    'main' => [
                        'border_radii' => '%%order_class%% .pac_dth_taxonomy_inner',
                        'border_styles' => '%%order_class%% .pac_dth_taxonomy_inner',
                    ],
                ],
                'toggle_slug' => 'taxonomies',
            ];
            $advanced_fields['borders']['image'] = [
                'css' => [
                    'main' => [
                        'border_radii' => '%%order_class%% .pac_dth_taxonomy img',
                        'border_styles' => '%%order_class%% .pac_dth_taxonomy img',
                    ],
                ],
                'toggle_slug' => 'image',
            ];
            $advanced_fields['borders']['icon'] = [
                'css' => [
                    'main' => [
                        'border_radii' => '%%order_class%% .pac_dth_icon a',
                        'border_styles' => '%%order_class%% .pac_dth_icon a',
                    ],
                ],
                'toggle_slug' => 'icon',
                'sub_toggle' => 'design_toggle',
            ];
            $advanced_fields['button']['button'] = [
                'label' => __('Button', 'divi-taxonomy-helper'),
                'css' => [
                    'main' => '%%order_class%% .pac_dth_readmore_btn',
                    'alignment' => '%%order_class%% .pac_dth_readmore_wrap',
                ],
                'use_alignment' => true,
                'box_shadow' => [
                    'css' => [
                        'main' => '%%order_class%% .pac_dth_readmore_btn',
                        'important' => true,
                    ],
                ],
                'borders' => [
                    'css' => [
                        'main' => '%%order_class%% .pac_dth_readmore_wrap',
                        'important' => true,
                    ],
                ],
                'margin_padding' => [
                    'css' => [
                        'margin' => '%%order_class%% .pac_dth_readmore_wrap',
                        'padding' => '%%order_class%% .pac_dth_readmore_wrap a',
                        'important' => 'all',
                    ],
                    /*   'custom_margin'  => [ 'default' => '0px|0px|0px|0px|false|false'],
                       'custom_padding'  => [ 'default' => '0px|0px|0px|0px|false|false']*/
                ],
            ];

            return $advanced_fields;
        }

        /**
         * Add Fields
         * @return array|array[]|array[][]|int[][]|null[][]|string[][]|string[][][]
         */
        public function get_fields()
        {
            $conditional_field = array_merge([
                'post_type' => [
                    'label' => __('Post Type', 'divi-taxonomy-helper'),
                    'description' => __('Choose which post type taxonomies you want to display.', 'divi-taxonomy-helper'),
                    'type' => 'select',
                    'options' => pac_dth_get_registered_post_types(),
                    'default' => key(pac_dth_get_registered_post_types()),
                    'computed_affects' => ['__terms'],
                    'toggle_slug' => 'list',
                ],
            ], self::get_taxonomies_fields());
            $fields = [
                'taxonomy_terms_count' => [
                    'label' => __('Taxonomy Terms Count', 'divi-taxonomy-helper'),
                    'type' => 'text',
                    'description' => __('Set how many taxonomies to display at once time.', 'divi-taxonomy-helper'),
                    'computed_affects' => ['__terms'],
                    'toggle_slug' => 'list',
                    'default' => 6,
                ],
                'taxonomy_terms_offset' => [
                    'label' => __('Term Offset Number', 'divi-taxonomy-helper'),
                    'type' => 'text',
                    'description' => __('Choose how many terms you would like to skip. These terms will not be shown in the feed.', 'divi-taxonomy-helper'),
                    'computed_affects' => ['__terms'],
                    'toggle_slug' => 'list',
                    'default' => '',
                ],
                'show_taxonomy_image' => [
                    'label' => __('Show Images', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'description' => __('Choose to show or hide the taxonomy image.', 'divi-taxonomy-helper'),
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'computed_affects' => ['__terms'],
                    'default' => 'on',
                    'toggle_slug' => 'elements',
                ],
                'show_taxonomy_icon' => [
                    'label' => __('Use Icons', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'description' => __('Choose to show icons for each taxonomy term instead of images.', 'divi-taxonomy-helper'),
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'show_if' => ['show_taxonomy_image' => 'off'],
                    'computed_affects' => ['__terms'],
                    'default' => 'off',
                    'toggle_slug' => 'elements',
                ],
                'show_taxonomy_title' => [
                    'label' => __('Show Title', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'description' => __('Choose to show or hide the taxonomy title.', 'divi-taxonomy-helper'),
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'computed_affects' => ['__terms'],
                    'default' => 'on',
                    'toggle_slug' => 'elements',
                ],
                'show_taxonomy_posts_count' => [
                    'label' => __('Show Post Count', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'description' => __('Choose to show the number of posts within the taxonomy.', 'divi-taxonomy-helper'),
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'computed_affects' => ['__terms'],
                    'default' => 'off',
                    'toggle_slug' => 'elements',
                ],
                'show_taxonomy_desc' => [
                    'label' => __('Show Description', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'description' => __('Choose to show or hide the taxonomy description.', 'divi-taxonomy-helper'),
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'computed_affects' => ['__terms'],
                    'default' => 'on',
                    'toggle_slug' => 'elements',
                ],
                'show_taxonomy_button' => [
                    'label' => __('Show Button', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'description' => __('Choose to show or hide the view taxonomy button.', 'divi-taxonomy-helper'),
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'computed_affects' => ['__terms'],
                    'default' => 'on',
                    'toggle_slug' => 'elements',
                ],
                'taxonomy_layout_type' => [
                    'label' => __('Layout Type', 'divi-taxonomy-helper'),
                    'type' => 'select',
                    'description' => __('Choose the type of layout to use for displaying the taxonomy terms.', 'divi-taxonomy-helper'),
                    'options' => [
                        'columns' => __('Columns', 'divi-taxonomy-helper'),
                        'inline' => __('Inline', 'divi-taxonomy-helper'),
                    ],
                    'computed_affects' => ['__terms'],
                    'mobile_options' => true,
                    'default' => 'columns',
                    'toggle_slug' => 'layout',
                ],
                'show_taxonomy_pagination' => [
                    'label' => __('Show Pagination', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'description' => __('Turn pagination on and off.', 'divi-taxonomy-helper'),
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'show_if' => ['show_dynamic_taxonomies' => 'off'],
                    'computed_affects' => ['__terms'],
                    'default' => 'off',
                    'toggle_slug' => 'elements',
                ],
                'taxonomies_column' => [
                    'label' => __('Number Of Columns', 'divi-taxonomy-helper'),
                    'description' => __('Set the number of taxonomy grid columns for each device.', 'divi-taxonomy-helper'),
                    'type' => 'range',
                    'default' => 3,
                    'default_on_front' => 3,
                    'responsive' => true,
                    'default_tablet' => 2,
                    'default_phone' => 1,
                    'mobile_options' => true,
                    'unitless' => true,
                    'computed_affects' => ['__terms'],
                    'range_settings' => [
                        'min' => 1,
                        'max' => 12,
                        'min_limit' => 1,
                        'max_limit' => 12,
                        'step' => 1,
                    ],
                    'show_if' => ['taxonomy_layout_type' => 'columns'],
                    'toggle_slug' => 'layout',
                ],
                'taxonomies_gap' => [
                    'label' => __('Spacing Between', 'divi-taxonomy-helper'),
                    'description' => __('Adjust the spacing between the taxonomy items.', 'divi-taxonomy-helper'),
                    'type' => 'range',
                    'default' => '1rem',
                    'mobile_options' => true,
                    'fixed_unit' => 'rem',
                    'validate_unit' => true,
                    'fixed_range' => true,
                    'computed_affects' => ['__terms'],
                    'range_settings' => [
                        'min' => 0,
                        'max' => 5,
                        'min_limit' => 0,
                        'max_limit' => 5,
                        'step' => 0.1,
                    ],
                    'show_if' => ['taxonomy_layout_type' => 'columns'],
                    'toggle_slug' => 'layout',
                ],
                'taxonomies_image_position' => [
                    'label' => __('Image & Icon Position', 'divi-taxonomy-helper'),
                    'type' => 'select',
                    'description' => __('Show the image top, left or right.', 'divi-taxonomy-helper'),
                    'options' => [
                        'top' => et_builder_i18n('Top'),
                        'left' => et_builder_i18n('Left'),
                        'right' => et_builder_i18n('Right'),
                    ],
                    'default' => 'top',
                    'mobile_options' => true,
                    'computed_affects' => ['__terms'],
                    'toggle_slug' => 'layout',
                ],
                'taxonomy_equalize_height' => [
                    'label' => __('Equalize Taxonomy Height', 'divi-taxonomy-helper'),
                    'description' => __('Choose to make the height of each taxonomy item the same.', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'default' => 'off',
                    'mobile_options' => true,
                    'computed_affects' => ['__terms'],
                    'toggle_slug' => 'layout',
                ],
                'taxonomy_desc_length' => [
                    'label' => __('Description Length', 'divi-taxonomy-helper'),
                    'type' => 'text',
                    'default' => 270,
                    'description' => __('Define the character count length of the taxonomy description text.', 'divi-taxonomy-helper'),
                    'computed_affects' => ['__terms'],
                    'toggle_slug' => 'list',
                ],
                'taxonomy_orderby' => [
                    'label' => __('Order By', 'divi-taxonomy-helper'),
                    'type' => 'select',
                    'options' => [
                        'name' => __('Title', 'divi-taxonomy-helper'),
                        'count' => __('Post Count', 'divi-taxonomy-helper'),
                        'rand' => __('Random', 'divi-taxonomy-helper'),
                    ],
                    'default' => 'name',
                    'description' => __('Choose the order for displaying taxonomies in the grid.', 'divi-taxonomy-helper'),
                    'computed_affects' => ['__terms'],
                    'toggle_slug' => 'list',
                ],
                'taxonomy_order' => [
                    'label' => __('Order', 'divi-taxonomy-helper'),
                    'type' => 'select',
                    'options' => [
                        'ASC' => __('Ascending', 'divi-taxonomy-helper'),
                        'DESC' => __('Descending', 'divi-taxonomy-helper'),
                    ],
                    'default' => 'ASC',
                    'description' => __('Choose to display the taxonomies in ascending or descending order.', 'divi-taxonomy-helper'),
                    'computed_affects' => ['__terms'],
                    'show_if_not' => ['taxonomy_orderby' => 'rand'],
                    'toggle_slug' => 'list',
                ],
                'taxonomy_button_text' => [
                    'label' => __('Button Text', 'divi-taxonomy-helper'),
                    'type' => 'text',
                    'description' => __('Enter custom text for the button.', 'divi-taxonomy-helper'),
                    'default' => 'View Taxonomy',
                    'show_if' => ['show_taxonomy_button' => 'on'],
                    'toggle_slug' => 'list',
                ],
                'hide_empty_taxonomies' => [
                    'label' => __('Hide Taxonomy Term If No Associated Posts', 'divi-taxonomy-helper'),
                    'description' => __('Choose to hide any taxonomy term that does not have any posts assigned to it.', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'default' => 'off',
                    'show_if' => ['show_dynamic_taxonomies' => 'off'],
                    'computed_affects' => ['__terms'],
                    'toggle_slug' => 'list',
                ],
                'current_level_taxonomies' => [
                    'label' => __('Only Show Current Level Taxonomies', 'divi-taxonomy-helper'),
                    'description' => __('Choose to limit the taxonomies that shown to only the current level instead of showing all taxonomies from lower levels when using multiple levels of taxonomy hierarchy.', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'default' => 'off',
                    'computed_affects' => ['__terms'],
                    'toggle_slug' => 'list',
                ],
                'if_no_taxonomies' => [
                    'label' => __('If No Taxonomies', 'divi-taxonomy-helper'),
                    'description' => __('Choose an action if there are no taxonomies to show in the module.', 'divi-taxonomy-helper'),
                    'type' => 'select',
                    'options' => [
                        'hide_module' => __('Hide Module', 'divi-taxonomy-helper'),
                        'display_message' => __('Display Message'),
                    ],
                    'default' => 'hide_module',
                    'computed_affects' => ['__terms'],
                    'toggle_slug' => 'list',
                ],
                'custom_message_no_taxonomy' => [
                    'label' => __('Custom Message If No Taxonomies', 'divi-taxonomy-helper'),
                    'type' => 'text',
                    'description' => __('Write a custom message to show if there are no taxonomies to show in the module.', 'divi-taxonomy-helper'),
                    'show_if' => ['if_no_taxonomies' => 'display_message'],
                    'computed_affects' => ['__terms'],
                    'toggle_slug' => 'list',
                    'default' => '',
                ],
                'taxonomy_button_fullwidth' => [
                    'label' => __('Button Fullwidth', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'description' => __('Choose to make the button fullwidth.', 'divi-taxonomy-helper'),
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'show_if' => ['custom_button' => 'on'],
                    'mobile_options' => true,
                    'computed_affects' => ['__terms'],
                    'default' => 'off',
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'button',
                ],
                'taxonomy_margin' => [
                    'label' => __('Taxonomy Margin', 'divi-taxonomy-helper'),
                    'description' => __('Adjust the spacing around the outside of the taxonomy item image.', 'divi-taxonomy-helper'),
                    'type' => 'custom_margin',
                    'default' => '0px|0px|0px|0px',
                    'mobile_options' => true,
                    'responsive' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'taxonomies',
                ],
                'taxonomy_padding' => [
                    'label' => __('Taxonomy Padding', 'divi-taxonomy-helper'),
                    'description' => __('Adjust the spacing around the content area in the taxonomy grid items.', 'divi-taxonomy-helper'),
                    'type' => 'custom_margin',
                    //'default' => '10px|10px|10px|10px',
                    'default' => '0px|0px|0px|0px',
                    'mobile_options' => true,
                    'responsive' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'taxonomies',
                ],
                'taxonomy_image_aspect_ratio' => [
                    'label' => __('Image Aspect Ratio', 'divi-taxonomy-helper'),
                    'type' => 'select',
                    'description' => __('Set the aspect ratio of the taxonomy term image.', 'divi-taxonomy-helper'),
                    'options' => [
                        'none' => __('Default', 'divi-image-helper'),
                        '1__1' => __('Square 1:1', 'divi-image-helper'),
                        '16__9' => __('Landscape 16:9', 'divi-image-helper'),
                        '4__3' => __('Landscape 4:3', 'divi-image-helper'),
                        '3__2' => __('Landscape 3:2', 'divi-image-helper'),
                        '9__16' => __('Portrait 9:16', 'divi-image-helper'),
                        '3__4' => __('Portrait 3:4', 'divi-image-helper'),
                        '2__3' => __('Portrait 2:3', 'divi-image-helper'),
                    ],
                    'default' => 'none',
                    'mobile_options' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'image',
                ],
                'taxonomy_image_width' => [
                    'label' => __('Image Width', 'divi-taxonomy-helper'),
                    'description' => __('Set a custom width for the taxonomy grid image.', 'divi-taxonomy-helper'),
                    'type' => 'range',
                    'default' => '',
                    'range_settings' => [
                        'min' => '0',
                        'max' => '500',
                        'step' => '1',
                    ],
                    'mobile_options' => true,
                    'fixed_unit' => 'px',
                    'validate_unit' => true,
                    'fixed_range' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'image',
                ],
                'taxonomy_image_height' => [
                    'label' => __('Image Height', 'divi-taxonomy-helper'),
                    'description' => __('Set a custom height for the taxonomy grid image.', 'divi-taxonomy-helper'),
                    'type' => 'range',
                    'default' => '',
                    'range_settings' => [
                        'min' => '0',
                        'max' => '500',
                        'step' => '1',
                    ],
                    'mobile_options' => true,
                    'fixed_unit' => 'px',
                    'validate_unit' => true,
                    'fixed_range' => true,
                    'show_if' => ['taxonomy_image_aspect_ratio' => 'none'],
                    'computed_affects' => ['__terms'],
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'image',
                ],
                'taxonomy_image_fit' => [
                    'label' => __('Image Fit', 'divi-taxonomy-helper'),
                    'type' => 'select',
                    'description' => __('Choose how the image should fit within its container.', 'divi-taxonomy-helper'),
                    'options' => ['contain' => 'Contain', 'cover' => 'Cover'],
                    'default' => 'contain',
                    'mobile_options' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'image',
                ],
                'taxonomy_image_margin' => [
                    'label' => __('Image Margin', 'divi-taxonomy-helper'),
                    'description' => __('Adjust the spacing around the outside of the taxonomy item image.', 'divi-taxonomy-helper'),
                    'type' => 'custom_margin',
                    'default' => '0px|0px|0px|0px',
                    'mobile_options' => true,
                    'responsive' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'image',
                ],
                'taxonomy_image_padding' => [
                    'label' => __('Image Padding', 'divi-taxonomy-helper'),
                    'description' => __('Adjust the spacing around the inside of the taxonomy item image.', 'divi-taxonomy-helper'),
                    'type' => 'custom_margin',
                    'default' => '0px|0px|0px|0px',
                    'mobile_options' => true,
                    'responsive' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'image',
                ],
                'taxonomy_image_alignment' => [
                    'label' => __('Image Alignment', 'divi-taxonomy-helper'),
                    'type' => 'align',
                    'description' => __('Align the image to the left, right or center.', 'divi-taxonomy-helper'),
                    'options' => et_builder_get_text_orientation_options(['justified']),
                    'advanced_fields' => true,
                    'mobile_options' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'image',
                ],
                'taxonomy_icon_size' => [
                    'label' => __('Icon Size', 'divi-taxonomy-helper'),
                    'type' => 'range',
                    'description' => __('Choose a custom size for the taxonomy icons.', 'divi-taxonomy-helper'),
                    'default' => '40px',
                    'default_unit' => 'px',
                    'default_on_front' => '40px',
                    'allowed_units' => ['%', 'em', 'rem', 'px', 'cm', 'mm', 'in', 'pt', 'pc', 'ex', 'vh', 'vw'],
                    'range_settings' => [
                        'min' => '1',
                        'max' => '150',
                        'step' => '1',
                    ],
                    'validate_unit' => true,
                    'mobile_options' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'icon',
                    'sub_toggle' => 'design_toggle',
                ],
                'taxonomy_icon_color' => [
                    'label' => __('Icon Color', 'divi-taxonomy-helper'),
                    'type' => 'color-alpha',
                    'description' => __('Choose a custom color for the taxonomy icons.', 'divi-taxonomy-helper'),
                    'mobile_options' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'icon',
                    'sub_toggle' => 'design_toggle',
                ],
                'taxonomy_icon_bg_color' => [
                    'label' => __('Icon Background Color', 'divi-taxonomy-helper'),
                    'type' => 'color-alpha',
                    'description' => __('Choose a custom background color for the taxonomy icons.', 'divi-taxonomy-helper'),
                    'mobile_options' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'icon',
                    'sub_toggle' => 'design_toggle',
                ],
                'taxonomy_icon_margin' => [
                    'label' => __('Icon Margin', 'divi-taxonomy-helper'),
                    'description' => __('Adjust the spacing around the outside of the taxonomy item icon.', 'divi-taxonomy-helper'),
                    'type' => 'custom_margin',
                    'default' => '0px|0px|20px|0px',
                    'mobile_options' => true,
                    'responsive' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'icon',
                    'sub_toggle' => 'design_toggle',
                ],
                'taxonomy_icon_padding' => [
                    'label' => __('Icon Padding', 'divi-taxonomy-helper'),
                    'description' => __('Adjust the spacing around the inside of the taxonomy item icon.', 'divi-taxonomy-helper'),
                    'type' => 'custom_margin',
                    'default' => '5px|5px|5px|5px',
                    'mobile_options' => true,
                    'responsive' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'icon',
                    'sub_toggle' => 'design_toggle',
                ],
                'taxonomy_icon_alignment' => [
                    'label' => __('Icon Alignment', 'divi-taxonomy-helper'),
                    'type' => 'align',
                    'description' => __('Align the icon to the left, right or center.', 'divi-taxonomy-helper'),
                    'options' => et_builder_get_text_orientation_options(['justified']),
                    'advanced_fields' => true,
                    'mobile_options' => true,
                    'tab_slug' => 'advanced',
                    'toggle_slug' => 'icon',
                    'sub_toggle' => 'design_toggle',
                ],
                'taxonomy_term_clickable' => [
                    'label' => __('Make Entire Term Clickable', 'divi-taxonomy-helper'),
                    'type' => 'yes_no_button',
                    'description' => __('Choose to make the entire term area clickable.', 'divi-taxonomy-helper'),
                    'options' => [
                        'on' => et_builder_i18n('Yes'),
                        'off' => et_builder_i18n('No'),
                    ],
                    'computed_affects' => ['__terms'],
                    'default' => 'off',
                    'tab_slug' => 'general',
                    'toggle_slug' => 'links',
                ],
                '__terms' => [
                    'type' => 'computed',
                    'computed_callback' => ['TaxonomyHelper', 'render_content'],
                    'computed_depends_on' => [
                        'post_type',
                        'show_dynamic_taxonomies',
                        'taxonomy_terms_count',
                        'taxonomy_terms_offset',
                        'show_taxonomy_image',
                        'show_taxonomy_icon',
                        'show_taxonomy_title',
                        'show_taxonomy_posts_count',
                        'show_taxonomy_desc',
                        'show_taxonomy_button',
                        'show_taxonomy_pagination',
                        'taxonomy_button_text',
                        'taxonomy_button_fullwidth',
                        // 'taxonomies_column',
                        'title_level',
                        'count_level',
                        'custom_button',
                        'button_icon',
                        'button_use_icon',
                        'taxonomy_desc_length',
                        'taxonomy_orderby',
                        'taxonomy_order',
                        'hide_empty_taxonomies',
                        'current_level_taxonomies',
                        'if_no_taxonomies',
                        'custom_message_no_taxonomy',
                        'taxonomy_image_aspect_ratio',
                    ],
                ],
            ];
            foreach (self::computed_fields_names() as $value) {
                $fields['__terms']['computed_depends_on'][] = $value;
            }

            return array_merge($conditional_field, $fields);
        }

        /**
         * Render Data
         *
         * @param $attrs
         * @param $content
         * @param $render_slug
         *
         * @return string
         */
        public function render($attrs, $content, $render_slug)
        {
            $this->render_css($render_slug);

            return $this->render_content($this->props);
        }

        /**
         * Render Content
         *
         * @param array $args
         *
         * @return string
         */
        public static function render_content(array $args = [])
        {
            global $wp_query;
            $paged = isset($wp_query->query['paged']) ? $wp_query->query['paged'] : 1;
            $output = '';
            $args = wp_parse_args($args, [
                'post_type' => 'post',
                'show_dynamic_taxonomies' => 'off',
                'taxonomy_terms_count' => 6,
                'taxonomy_terms_offset' => '',
                'taxonomies_column' => 3,
                'show_taxonomy_pagination' => 'off',
                'taxonomy_desc_length' => 270,
                'taxonomy_orderby' => 'name',
                'taxonomy_order' => 'ASC',
                'show_taxonomy_image' => 'on',
                'show_taxonomy_icon' => 'off',
                'show_taxonomy_title' => 'on',
                'show_taxonomy_posts_count' => 'off',
                'show_taxonomy_desc' => 'on',
                'show_taxonomy_button' => 'on',
                'taxonomy_term_clickable' => 'off',
                'title_level' => 'h3',
                'count_level' => 'h4',
                'custom_button' => '',
                'taxonomy_button_text' => '',
                'taxonomy_button_fullwidth' => 'off',
                'button_icon' => '',
                'button_use_icon' => '',
                'hide_empty_taxonomies' => 'off',
                'current_level_taxonomies' => 'off',
                'if_no_taxonomies' => 'hide_modules',
                'custom_message_no_taxonomy' => '',
            ]);
            $show_dynamic_taxonomies = $args['show_dynamic_taxonomies'];
            if ('on' === $show_dynamic_taxonomies
                && ((function_exists('et_fb_is_enabled') && et_fb_is_enabled())
                    || (function_exists('et_builder_bfb_enabled') && et_builder_bfb_enabled()))) {
                return __('Your Dynamic Taxonomies will display here.', 'divi-taxonomy-helper');
            }
            $post_type = $args['post_type'];
            $taxonomy = isset($args["taxonomy_$post_type"]) ? $args["taxonomy_$post_type"] : '';
            $selected_taxonomies = isset($args["taxonomy_terms_$taxonomy"]) ? $args["taxonomy_terms_$taxonomy"] : '';
            $unselected_taxonomies = isset($args["excluded_taxonomy_terms_$taxonomy"]) ? $args["excluded_taxonomy_terms_$taxonomy"] : '';
            $show_taxonomy_pagination = $args['show_taxonomy_pagination'];
            $current_level_taxonomies = $args['current_level_taxonomies'];
            $taxonomy_terms_count = !is_numeric($args['taxonomy_terms_count']) ? 6 : absint($args['taxonomy_terms_count']);
            $button_use_icon = $args['button_use_icon'];
            $button_icon = $args['button_icon'] ? et_pb_process_font_icon($args['button_icon']) : '$';
            $button_icon_class = 'on' === $button_use_icon ? 'et_pb_custom_button_icon' : '';
            $title_heading_level = et_pb_process_header_level($args['title_level'], 'h3');
            $count_heading_level = et_pb_process_header_level($args['count_level'], 'h4');
            $hide_empty_taxonomies = filter_var($args['hide_empty_taxonomies'], FILTER_VALIDATE_BOOLEAN);
            $if_no_taxonomies = $args['if_no_taxonomies'];
            if (!empty($post_type) && !empty($taxonomy)) {
                $is_selected_terms = !empty($selected_taxonomies) && strpos($selected_taxonomies, 'on') !== false;
                $is_unselected_terms = !empty($unselected_taxonomies) && strpos($unselected_taxonomies, 'on') !== false;
                $terms = [];
                $offset = (empty($args['taxonomy_terms_offset'])) ? ($paged - 1) * $taxonomy_terms_count : intval($args['taxonomy_terms_offset']);
                $total_terms = wp_count_terms($taxonomy);
                $term_args = ['taxonomy' => $taxonomy, 'hide_empty' => $hide_empty_taxonomies, 'pad_counts' => true];
                if (!$is_selected_terms) {
                    $term_args['number'] = $taxonomy_terms_count;
                    $term_args['offset'] = $offset;
                }
                // Order & Orderby
                if ('rand' !== $args['taxonomy_orderby']) {
                    $term_args['orderby'] = $args['taxonomy_orderby'];
                    $term_args['order'] = $args['taxonomy_order'];
                }
                if ('off' === $show_dynamic_taxonomies) {
                    if ('on' === $current_level_taxonomies) {
                        $term_args['parent'] = 0;
                    }
                    $terms = get_terms($term_args);
                    $terms = wp_list_pluck($terms, 'term_id');
                    if ($is_selected_terms) {
                        $processed_terms = pac_dth_process_multiple_checkboxes($selected_taxonomies, array_keys(pac_dth_get_terms_by_taxonomy($taxonomy)));
                        $selected_terms = array_map(function ($val) {
                            return substr($val, strpos($val, '_') + 1);
                        }, $processed_terms);
                        if (!empty($selected_terms)) {
                            $terms = array_intersect($terms, $selected_terms);
                        }
                    }
                    if ($is_unselected_terms) {
                        $processed_terms = pac_dth_process_multiple_checkboxes($unselected_taxonomies, array_keys(pac_dth_get_terms_by_taxonomy($taxonomy)));
                        $selected_terms = array_map(function ($val) {
                            return substr($val, strpos($val, '_') + 1);
                        }, $processed_terms);
                        if (!empty($selected_terms)) {
                            $unterms = array_intersect($terms, $selected_terms);
                            $terms = array_diff_assoc($terms, $unterms);
                        }
                    }
                } else {
                    if (pac_dth_get_is_taxonomy_page($taxonomy)) {
                        $queried_term = get_queried_object();
                        if (!empty($queried_term->term_id)) {
                            $queried_term_id = $queried_term->term_id;
                            //$term_args['child_of'] = $queried_term_id;
                            $term_args['parent'] = $queried_term_id;
                            $terms = get_terms($term_args);
                            $terms = wp_list_pluck($terms, 'term_id');
                            if (!empty($terms) && 'on' === $current_level_taxonomies) {
                                foreach ($terms as $term_key => $term_id) {
                                    if ($queried_term_id !== get_term($term_id)->parent && in_array($term_id, $terms)) {
                                        unset($terms[$term_key]);
                                    }
                                }
                            }
                        }
                    } else {
                        global $post;
                        if (isset($post->ID) && is_a($post, 'WP_POST')) {
                            if ('on' === $current_level_taxonomies) {
                                $term_args['parent'] = 0;
                            }
                            $post_terms = wp_get_post_terms($post->ID, $taxonomy, $term_args);
                            $terms = wp_list_pluck($post_terms, 'term_id');
                        }
                    }
                }
                // Show Terms Content
                if (!empty($terms)) {
                    if ('rand' === $args['taxonomy_orderby']) {
                        shuffle($terms);
                    }
                    $output .= et_core_intentionally_unescaped("<div class='pac_dth_taxonomies taxonomy_$taxonomy post_type_$post_type'>", 'html');
                    $d_featured_img = et_get_option("pac_dth_{$taxonomy}_featured_image");
                    if (empty($d_featured_img)) {
                        $d_featured_img = ET_BUILDER_PLACEHOLDER_LANDSCAPE_IMAGE_DATA;
                    }
                    foreach ($terms as $term_id) {
                        $the_term = get_term($term_id, $taxonomy);
                        $term_id = $the_term->term_id;
                        $term_name = $the_term->name;
                        $term_count = $the_term->count;
                        $term_slug = $the_term->slug;
                        $term_description = $the_term->description;
                        $term_link = get_term_link($term_id);
                        $term_thumbnail_id = get_term_meta($term_id, 'thumbnail_id', true);
                        $term_attr_id = sprintf('taxonomy_%s', $term_id);
                        $term_attr_class = sprintf('%1$s %2$s', str_replace(['-'], '_', $term_slug), $term_attr_id);
                        $term_link_data_attr = 'on' === $args['taxonomy_term_clickable'] ? sprintf(' data-term-permalink="%s"', esc_url($term_link)) : '';
                        $output .= "<div class='pac_dth_taxonomy $term_attr_class' id='".esc_attr($term_attr_id)."'>";
                        if (!empty($term_id)) {
                            $output .= '<div class="pac_dth_taxonomy_inner"'.$term_link_data_attr.'>';
                            // Image
                            if ('on' === $args['show_taxonomy_image']) {
                                $term_thumbnail = $term_thumbnail_id > 0 ? wp_get_attachment_image($term_thumbnail_id, 'full') : sprintf('<img src="%1$s"  alt="%2$s"/>', $d_featured_img, $term_name);
                                $output .= sprintf('<div class="pac_dth_image"><a href="%1$s">%2$s</a></div>', $term_link, $term_thumbnail);
                            }
                            // Icon
                            if ('on' === $args['show_taxonomy_icon'] && 'off' === $args['show_taxonomy_image']) {
                                $term_icon = isset($args['taxonomy_term_icon_'.$term_slug.'_'.$term_id]) ? $args['taxonomy_term_icon_'.$term_slug.'_'.$term_id] : '';
                                if (!empty($term_icon)) {
                                    $output .= sprintf('<div class="pac_dth_icon"><a href="%1$s" class="et-pb-font-icon">%2$s</a></div>', $term_link, et_pb_process_font_icon($term_icon));
                                } else {
                                    $default_icon = !empty($args['taxonomy_term_default_icon']) ? $args['taxonomy_term_default_icon'] : null;
                                    $output .= sprintf('<div class="pac_dth_icon"><a href="%1$s" class="et-pb-font-icon">%2$s</a></div>', $term_link, et_pb_process_font_icon($default_icon));
                                }
                            }
                            $output .= '<div class="pac_dth_content">';
                            $output .= '<div>';
                            // Title
                            if ('on' === $args['show_taxonomy_title']) {
                                $output .= sprintf('<%1$s class="pac_dth_title"><a href="%2$s">%3$s</a></%1$s>', et_core_esc_previously($title_heading_level), $term_link, $term_name);
                            }
                            // Count
                            if ('on' === $args['show_taxonomy_posts_count']) {
                                $post_type_obj = get_post_type_object($post_type);
                                $post_singular_name = isset($post_type_obj->labels->singular_name) ? $post_type_obj->labels->singular_name.'s' : $post_type;
                                $post_singular_name = apply_filters('pac_dth_post_count_label', $post_singular_name, $taxonomy);
                                $output .= sprintf('<%1$s class="pac_dth_count">%2$s</%1$s>', et_core_esc_previously($count_heading_level), sprintf(_n('%s %s', '%s %s', $term_count), number_format_i18n($term_count), $post_singular_name));
                            }
                            // Desc
                            if ('on' === $args['show_taxonomy_desc']) {
                                $output .= sprintf('<p class="pac_dth_desc">%1$s</p>', pac_dth_chars_limit($term_description, $args['taxonomy_desc_length'], '...'));
                            }
                            $output .= '</div>';
                            // Read More
                            $read_more = 'on' === $args['show_taxonomy_button'] ? sprintf('<div class="pac_dth_readmore_wrap"><a href="%1$s" class="%3$s pac_dth_readmore_btn %5$s" data-icon="%4$s">%2$s</a></div>', $term_link, $args['taxonomy_button_text'], 'et_pb_button', esc_attr($button_icon), $button_icon_class) : '';
                            $output .= et_core_intentionally_unescaped($read_more, 'html');
                            $output .= '</div>';
                            $output .= '</div>';
                        }
                        $output .= '</div>';
                    }
                    $output .= '</div>';
                    // Pagination
                    if ('on' === $show_taxonomy_pagination && !$is_selected_terms) {
                        $big = 999999999;
                        $pages = paginate_links([
                            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                            'format' => '?paged=%#%',
                            'current' => $paged,
                            'total' => ceil($total_terms / $taxonomy_terms_count),
                            'prev_text' => '&larr;',
                            'next_text' => '&rarr;',
                            'type' => 'array'
                        ]);
                        if ($pages) {
                            $output .= '<nav class="pac_dth_pagination">';
                            $output .= '<ul class="page-numbers">';
                            foreach ($pages as $page) {
                                $output .= sprintf('<li>%s</li>', $page);
                            }
                            $output .= '</ul>';
                            $output .= '</nav>';
                        }
                    }
                } else {
                    if ('display_message' === $if_no_taxonomies) {
                        $output .= $args['custom_message_no_taxonomy'];
                    } else {
                        add_filter('et_module_shortcode_output', function ($output, $render_slug, $module) {
                            // Return If Frontend Builder
                            if (function_exists('et_fb_is_enabled') && et_fb_is_enabled()) {
                                return $output;
                            }
                            // Return If Backend Builder
                            if (function_exists('et_builder_bfb_enabled') && et_builder_bfb_enabled()) {
                                return $output;
                            }
                            // Return If Admin/Ajax and Output Array/Empty
                            if (is_admin() || wp_doing_ajax() || is_array($output) || empty($output)) {
                                return $output;
                            }
                            // Return If Not Slug Match
                            if ('pac_dth_taxonomy_list' !== $render_slug) {
                                return $output;
                            }
                            if (!preg_match("~\bpac_dth_taxonomies\b~", $output)) {
                                return '';
                            }

                            return $output;
                        }, 10, 3);
                    }
                }
            }

            return $output;
        }

        /**
         * Render CSS
         *
         * @param $render_slug
         *
         * @return void
         */
        public function render_css($render_slug)
        {
            // Columns
            $taxonomies_column = $this->props['taxonomies_column'];
            $taxonomies_column_responsive_status = et_pb_responsive_options()->is_responsive_enabled($this->props, 'taxonomies_column');
            $taxonomies_column_tablet = $taxonomies_column_responsive_status ? $this->props['taxonomies_column_tablet'] : $taxonomies_column;
            $taxonomies_column_phone = $taxonomies_column_responsive_status ? $this->props['taxonomies_column_phone'] : $taxonomies_column_tablet;
            // Gap
            $taxonomies_gap = $this->props['taxonomies_gap'];
            $taxonomies_gap_responsive_status = et_pb_responsive_options()->is_responsive_enabled($this->props, 'taxonomies_gap');
            $taxonomies_gap_tablet = ($taxonomies_gap_responsive_status && isset($this->props['taxonomies_gap_tablet']) && !empty($this->props['taxonomies_gap_tablet'])) ? $this->props['taxonomies_gap_tablet'] : $taxonomies_gap;
            $taxonomies_gap_phone = ($taxonomies_gap_responsive_status && isset($this->props['taxonomies_gap_phone']) && !empty($this->props['taxonomies_gap_phone'])) ? $this->props['taxonomies_gap_phone'] : $taxonomies_gap_tablet;
            self::set_style($render_slug, [
                'selector' => "%%order_class%% .pac_dth_taxonomies",
                'declaration' => "gap: $taxonomies_gap;"
            ]);
            self::set_style($render_slug, [
                'selector' => "%%order_class%% .pac_dth_taxonomy",
                'declaration' => "width: calc((100% / $taxonomies_column) - $taxonomies_gap + ($taxonomies_gap / $taxonomies_column));"
            ]);
            if ($taxonomies_column_responsive_status && !empty($taxonomies_column_tablet)) {
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomies",
                    'declaration' => "gap: $taxonomies_gap_tablet;",
                    'media_query' => self::get_media_query('768_980'),
                ]);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomy",
                    'declaration' => "width: calc((100% / $taxonomies_column_tablet) - $taxonomies_gap_tablet + ($taxonomies_gap_tablet / $taxonomies_column_tablet));",
                    'media_query' => self::get_media_query('768_980'),
                ]);
            }
            if ($taxonomies_column_responsive_status && !empty($taxonomies_column_phone)) {
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomies",
                    'declaration' => "gap: $taxonomies_gap_phone;",
                    'media_query' => self::get_media_query('max_width_767'),
                ]);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomy",
                    'declaration' => "width: calc((100% / $taxonomies_column_phone) - $taxonomies_gap_phone + ($taxonomies_gap_phone / $taxonomies_column_phone));",
                    'media_query' => self::get_media_query('max_width_767'),
                ]);
            }
            // Inline Layout
            $taxonomy_layout_type = $this->props['taxonomy_layout_type'];
            $taxonomy_layout_type_responsive_status = et_pb_responsive_options()->is_responsive_enabled($this->props, 'taxonomy_layout_type');
            $taxonomy_layout_type_tablet = $taxonomy_layout_type_responsive_status ? $this->props['taxonomy_layout_type_tablet'] : $taxonomy_layout_type;
            $taxonomy_layout_type_phone = $taxonomy_layout_type_responsive_status ? $this->props['taxonomy_layout_type_phone'] : $taxonomy_layout_type_tablet;
            if ('inline' === $taxonomy_layout_type) {
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomy",
                    'declaration' => "display: inline-block!important;width: auto;",
                ]);
            }
            if ($taxonomy_layout_type_responsive_status && 'inline' === $taxonomy_layout_type_tablet) {
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomy",
                    'declaration' => "display: inline-block!important;width: auto;",
                    'media_query' => self::get_media_query('768_980'),
                ]);
            }
            if ($taxonomy_layout_type_responsive_status && 'inline' === $taxonomy_layout_type_phone) {
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomies",
                    'declaration' => "display: inline-block!important;width: auto;",
                    'media_query' => self::get_media_query('max_width_767'),
                ]);

            }
            // Image Position
            $taxonomies_image_position = $this->props['taxonomies_image_position'];
            $taxonomies_image_position_responsive = et_pb_responsive_options()->is_responsive_enabled($this->props, 'taxonomies_image_position');
            $taxonomies_image_position_tablet = $taxonomies_image_position_responsive ? $this->props['taxonomies_image_position_tablet'] : $taxonomies_image_position;
            $taxonomies_image_position_phone = $taxonomies_image_position_responsive ? $this->props['taxonomies_image_position_phone'] : $taxonomies_image_position_tablet;
            if ('left' === $taxonomies_image_position || 'right' === $taxonomies_image_position) {
                $image_order = 'left' === $taxonomies_image_position ? 0 : 1;
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomy_inner",
                    'declaration' => "flex-direction: row !important;justify-content: space-between; gap: .875rem;",
                    'media_query' => self::get_media_query('min_width_981'),
                ]);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_image,%%order_class%% .pac_dth_icon",
                    'declaration' => "order: $image_order;",
                    'media_query' => self::get_media_query('min_width_981'),
                ]);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_content",
                    'declaration' => "width: 100%;display: flex;flex-wrap: wrap;justify-content: space-between;",
                    'media_query' => self::get_media_query('min_width_981'),
                ]);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_desc",
                    'declaration' => "flex-basis: 100%;",
                    'media_query' => self::get_media_query('min_width_981'),
                ]);
            }
            if ($taxonomies_image_position_responsive && ('left' === $taxonomies_image_position_tablet || 'right' === $taxonomies_image_position_tablet)) {
                $image_order = 'left' === $taxonomies_image_position_tablet ? 0 : 1;
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomy_inner",
                    'declaration' => "flex-direction: row !important;justify-content: space-between; gap: .875rem;",
                    'media_query' => self::get_media_query('768_980'),
                ]);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_image,%%order_class%% .pac_dth_icon",
                    'declaration' => "order: $image_order;",
                    'media_query' => self::get_media_query('768_980'),
                ]);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_content",
                    'declaration' => "width: 100%;display: flex;flex-wrap: wrap;justify-content: space-between;",
                    'media_query' => self::get_media_query('768_980'),
                ]);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_desc",
                    'declaration' => "flex-basis: 100%;",
                    'media_query' => self::get_media_query('768_980'),
                ]);
            }
            if ($taxonomies_image_position_responsive && ('left' === $taxonomies_image_position_phone || 'right' === $taxonomies_image_position_phone)) {
                $image_order = 'left' === $taxonomies_image_position_phone ? 0 : 1;
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomy_inner",
                    'declaration' => "flex-direction: row !important;justify-content: space-between; gap: .875rem;",
                    'media_query' => self::get_media_query('max_width_767'),
                ]);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_image,%%order_class%% .pac_dth_icon",
                    'declaration' => "order: $image_order;",
                    'media_query' => self::get_media_query('max_width_767'),
                ]);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_content",
                    'declaration' => "width: 100%;display: flex;flex-wrap: wrap;justify-content: space-between;",
                    'media_query' => self::get_media_query('max_width_767'),
                ]);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_desc",
                    'declaration' => "flex-basis: 100%;",
                    'media_query' => self::get_media_query('max_width_767'),
                ]);
            }
            // Taxonomies Equalize Height
            $d_taxonomy_equalize_height = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_equalize_height', 'auto', true);
            $d_taxonomy_equalize_height = array_map(function ($value) {
                if ($value == 'on') {
                    $value = '100%';
                } else {
                    $value = 'auto';
                }

                return $value;
            }, $d_taxonomy_equalize_height);
            et_pb_responsive_options()->generate_responsive_css($d_taxonomy_equalize_height, "%%order_class%% .pac_dth_taxonomy_inner", 'height', $render_slug);
            // Background Color
            $d_taxonomy_background = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_background');
            et_pb_responsive_options()->generate_responsive_css(pac_dth_get_global_color($d_taxonomy_background), "%%order_class%% .pac_dth_taxonomy_inner", 'background', $render_slug, '', 'color');
            // Background Color Hover
            $is_hover_d_color = et_pb_hover_options()->is_enabled('taxonomy_background', $this->props);
            if ($is_hover_d_color) {
                $d_color_hover = et_pb_hover_options()->get_value('taxonomy_background', $this->props);
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomy_inner:hover",
                    'declaration' => "background:$d_color_hover;"
                ]);
            }
            $taxonomy_term_bg = array_filter($this->props, function ($key) {
                return strpos($key, 'taxonomy_term_bg_') === 0;
            }, ARRAY_FILTER_USE_KEY);
            $taxonomy_term_bg = array_filter($taxonomy_term_bg, 'strlen');
            if (!empty($taxonomy_term_bg)) {
                foreach ($taxonomy_term_bg as $bg_key => $bg_value) {
                    $term_bg_arr = explode('_', $bg_key);
                    if (is_array($term_bg_arr) && end($term_bg_arr)) {
                        $term_bg_id = end($term_bg_arr);
                        $taxonomy_bg_class = "%%order_class%% .pac_dth_taxonomy.taxonomy_$term_bg_id .pac_dth_taxonomy_inner";
                        $d_taxonomy_background = et_pb_responsive_options()->get_property_values($this->props, $bg_key);
                        et_pb_responsive_options()->generate_responsive_css(pac_dth_get_global_color($d_taxonomy_background), $taxonomy_bg_class, 'background', $render_slug, '', 'color');
                        $is_hover_d_color = et_pb_hover_options()->is_enabled($bg_key, $this->props);
                        if ($is_hover_d_color) {
                            $d_color_hover = et_pb_hover_options()->get_value($bg_key, $this->props);
                            self::set_style($render_slug, [
                                'selector' => "$taxonomy_bg_class:hover",
                                'declaration' => "background:$d_color_hover;"
                            ]);
                        }
                    }
                }
            }
            // Margin
            $d_taxonomy_margin = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_margin');
            $d_taxonomy_margin = pac_dth_get_mpb_props($d_taxonomy_margin);
            et_pb_responsive_options()->generate_responsive_css($d_taxonomy_margin, "%%order_class%% .pac_dth_taxonomy_inner", 'margin', $render_slug, '', 'custom_padding');
            // Padding
            $d_taxonomy_padding = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_padding');
            $d_taxonomy_padding = pac_dth_get_mpb_props($d_taxonomy_padding);
            et_pb_responsive_options()->generate_responsive_css($d_taxonomy_padding, "%%order_class%% .pac_dth_taxonomy_inner", 'padding', $render_slug, '', 'custom_padding');
            /* IMAGE */
            // Image Aspect Ratio
            $d_image_aspect_ratio = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_image_aspect_ratio');
            $d_image_aspect_ratio['tablet'] = empty($d_image_aspect_ratio['tablet']) ? $d_image_aspect_ratio['desktop'] : $d_image_aspect_ratio['tablet'];
            $d_image_aspect_ratio['phone'] = empty($d_image_aspect_ratio['phone']) ? $d_image_aspect_ratio['tablet'] : $d_image_aspect_ratio['phone'];
            if ('none' !== $d_image_aspect_ratio['desktop'] && !empty($d_image_aspect_ratio['desktop'])) {
                ET_Builder_Element::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_image",
                    'declaration' => pac_dth_get_aspect_ratio_css($d_image_aspect_ratio['desktop']),
                ]);
                ET_Builder_Element::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_image img",
                    'declaration' => "position: absolute;height: 100%;width: 100%;top: 0;left: 0;right: 0;bottom: 0;",
                ]);

            }
            if ('none' !== $d_image_aspect_ratio['tablet'] && !empty($d_image_aspect_ratio['tablet'])) {
                ET_Builder_Element::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_image",
                    'declaration' => pac_dth_get_aspect_ratio_css($d_image_aspect_ratio['tablet']),
                    'media_query' => ET_Builder_Element::get_media_query('768_980'),
                ]);
                ET_Builder_Element::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_image img",
                    'declaration' => "position: absolute;height: 100%;width: 100%;top: 0;left: 0;right: 0;bottom: 0;",
                    'media_query' => ET_Builder_Element::get_media_query('768_980'),
                ]);

            }
            if ('none' !== $d_image_aspect_ratio['phone'] && !empty($d_image_aspect_ratio['phone'])) {
                ET_Builder_Element::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_image",
                    'declaration' => pac_dth_get_aspect_ratio_css($d_image_aspect_ratio['phone']),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                ]);
                ET_Builder_Element::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_image img",
                    'declaration' => "position: absolute;height: 100%;width: 100%;top: 0;left: 0;right: 0;bottom: 0;",
                    'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                ]);

            }
            // Width
            $d_taxonomy_image_width = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_image_width');
            et_pb_responsive_options()->generate_responsive_css($d_taxonomy_image_width, "%%order_class%% .pac_dth_image", 'width', $render_slug);
            // Height
            $d_taxonomy_image_height = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_image_height');
            et_pb_responsive_options()->generate_responsive_css($d_taxonomy_image_height, "%%order_class%% .pac_dth_image img", 'height', $render_slug);
            // Fit
            $d_taxonomy_image_fit = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_image_fit');
            et_pb_responsive_options()->generate_responsive_css($d_taxonomy_image_fit, "%%order_class%% .pac_dth_image img", 'object-fit', $render_slug, '', 'select');
            // Margin
            $d_taxonomy_image_margin = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_image_margin');
            $d_taxonomy_image_margin = pac_dth_get_mpb_props($d_taxonomy_image_margin);
            et_pb_responsive_options()->generate_responsive_css($d_taxonomy_image_margin, "%%order_class%% .pac_dth_taxonomy img", 'margin', $render_slug, '', 'custom_padding');
            // Padding
            $d_taxonomy_image_padding = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_image_padding');
            $d_taxonomy_image_padding = pac_dth_get_mpb_props($d_taxonomy_image_padding);
            et_pb_responsive_options()->generate_responsive_css($d_taxonomy_image_padding, "%%order_class%% .pac_dth_taxonomy img", 'padding', $render_slug, '', 'custom_padding');
            // Alignment
            $d_taxonomy_image_alignment = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_image_alignment');
            $d_taxonomy_image_alignment = pac_dth_get_flex_alignment($d_taxonomy_image_alignment);
            et_pb_responsive_options()->generate_responsive_css($d_taxonomy_image_alignment, "%%order_class%% .pac_dth_image", 'align-self', $render_slug, '', 'align');
            /* ICON */
            $show_taxonomy_icon = isset($this->props['show_taxonomy_icon']) ? esc_attr($this->props['show_taxonomy_icon']) : 'off';
            if ('on' === $show_taxonomy_icon) {
                // Default Icon
                $default_icon = !empty($this->props['taxonomy_term_default_icon']) ? $this->props['taxonomy_term_default_icon'] : null;
                $taxonomy_icon_class = "%%order_class%% .pac_dth_taxonomy .pac_dth_icon";
                if ((function_exists('et_pb_get_icon_font_family') && function_exists('et_pb_get_icon_font_weight'))) {
                    self::set_style($render_slug, [
                        'selector' => $taxonomy_icon_class,
                        'declaration' => sprintf("font-family:%s, serif;font-weight:%s;", et_pb_get_icon_font_family($default_icon), et_pb_get_icon_font_weight($default_icon)),
                    ]);
                } else {
                    self::set_style($render_slug, [
                        'selector' => $taxonomy_icon_class,
                        'declaration' => "font-family:ETModules, serif;",
                    ]);
                }
                // Each Icon
                $taxonomy_term_icons = array_filter($this->props, function ($key) {
                    return strpos($key, 'taxonomy_term_icon_') === 0;
                }, ARRAY_FILTER_USE_KEY);
                $taxonomy_term_icons = array_filter($taxonomy_term_icons, 'strlen');
                if (!empty($taxonomy_term_icons)) {
                    foreach ($taxonomy_term_icons as $icon_key => $icon_value) {
                        $term_icons_arr = explode('_', $icon_key);
                        if (is_array($term_icons_arr) && end($term_icons_arr)) {
                            $term_icon_id = end($term_icons_arr);
                            $taxonomy_icon_class = "%%order_class%% .pac_dth_taxonomy.taxonomy_$term_icon_id .pac_dth_icon";
                            if ((function_exists('et_pb_get_icon_font_family') && function_exists('et_pb_get_icon_font_weight'))) {
                                self::set_style($render_slug, [
                                    'selector' => $taxonomy_icon_class,
                                    'declaration' => sprintf("font-family:%s, serif;font-weight:%s;", et_pb_get_icon_font_family($icon_value), et_pb_get_icon_font_weight($icon_value)),
                                ]);
                            } else {
                                self::set_style($render_slug, [
                                    'selector' => $taxonomy_icon_class,
                                    'declaration' => "font-family:ETModules, serif;",
                                ]);
                            }
                        }
                    }
                }
                // Size
                $d_taxonomy_icon_size = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_icon_size');
                et_pb_responsive_options()->generate_responsive_css($d_taxonomy_icon_size, "%%order_class%% .pac_dth_taxonomy .pac_dth_icon", 'font-size', $render_slug);
                // Color
                $d_taxonomy_icon_color = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_icon_color');
                et_pb_responsive_options()->generate_responsive_css(pac_dth_get_global_color($d_taxonomy_icon_color), "%%order_class%% .pac_dth_taxonomy .pac_dth_icon a", 'color', $render_slug, '', 'color');
                // Background
                $d_taxonomy_icon_bg_color = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_icon_bg_color');
                et_pb_responsive_options()->generate_responsive_css(pac_dth_get_global_color($d_taxonomy_icon_bg_color), "%%order_class%% .pac_dth_taxonomy .pac_dth_icon a", 'background', $render_slug, '', 'color');
                // Alignment
                $d_taxonomy_icon_alignment = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_icon_alignment');
                $d_taxonomy_icon_alignment = pac_dth_get_flex_alignment($d_taxonomy_icon_alignment);
                et_pb_responsive_options()->generate_responsive_css($d_taxonomy_icon_alignment, "%%order_class%% .pac_dth_taxonomy .pac_dth_icon", 'align-self', $render_slug, '', 'align');
                // Margin
                $d_taxonomy_icon_margin = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_icon_margin');
                $d_taxonomy_icon_margin = pac_dth_get_mpb_props($d_taxonomy_icon_margin);
                et_pb_responsive_options()->generate_responsive_css($d_taxonomy_icon_margin, "%%order_class%% .pac_dth_taxonomy .pac_dth_icon", 'margin', $render_slug, '', 'custom_padding');
                // Padding
                $d_taxonomy_icon_padding = et_pb_responsive_options()->get_property_values($this->props, 'taxonomy_icon_padding');
                $d_taxonomy_icon_padding = pac_dth_get_mpb_props($d_taxonomy_icon_padding);
                et_pb_responsive_options()->generate_responsive_css($d_taxonomy_icon_padding, "%%order_class%% .pac_dth_taxonomy .pac_dth_icon a", 'padding', $render_slug, '', 'custom_padding');
            }
            // Button Full Width
            $taxonomy_button_fullwidth = $this->props['taxonomy_button_fullwidth'];
            $is_taxonomy_button_fullwidth_responsive = et_pb_responsive_options()->is_responsive_enabled($this->props, 'taxonomy_button_fullwidth');
            $taxonomy_button_fullwidth_tablet = $is_taxonomy_button_fullwidth_responsive ? $this->props['taxonomy_button_fullwidth_tablet'] : $taxonomy_button_fullwidth;
            $taxonomy_button_fullwidth_phone = $is_taxonomy_button_fullwidth_responsive ? $this->props['taxonomy_button_fullwidth_phone'] : $taxonomy_button_fullwidth_tablet;
            if ('on' === $taxonomy_button_fullwidth) {
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_readmore_wrap .et_pb_button",
                    'declaration' => "display: block;text-align: center;",
                ]);
            }
            if ($is_taxonomy_button_fullwidth_responsive && 'off' === $taxonomy_button_fullwidth_tablet) {
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_readmore_wrap .et_pb_button",
                    'declaration' => "display: revert;",
                    'media_query' => self::get_media_query('pac_dth_min_768_max_980'),
                ]);
            } elseif ($is_taxonomy_button_fullwidth_responsive && 'on' === $taxonomy_button_fullwidth_tablet) {
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_readmore_wrap .et_pb_button",
                    'declaration' => "display: block;text-align: center;",
                    'media_query' => self::get_media_query('pac_dth_min_768_max_980'),
                ]);
            }
            if ($is_taxonomy_button_fullwidth_responsive && 'off' === $taxonomy_button_fullwidth_phone) {
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_readmore_wrap .et_pb_button",
                    'declaration' => "display: revert;",
                    'media_query' => self::get_media_query('pac_dth_max_767'),
                ]);
            } elseif ($is_taxonomy_button_fullwidth_responsive && 'on' === $taxonomy_button_fullwidth_phone) {
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_readmore_wrap .et_pb_button",
                    'declaration' => "display: block;text-align: center;",
                    'media_query' => self::get_media_query('pac_dth_max_767'),
                ]);
            }
            // Entire Term Clickable
            $taxonomy_term_clickable = isset($this->props['taxonomy_term_clickable']) ? $this->props['taxonomy_term_clickable'] : 'off';
            if ('on' === $taxonomy_term_clickable) {
                self::set_style($render_slug, [
                    'selector' => "%%order_class%% .pac_dth_taxonomy_inner",
                    'declaration' => "cursor: pointer;",
                ]);
            }
            // Transition
            $d_hover_transition_duration = et_pb_responsive_options()->get_property_values($this->props, 'hover_transition_duration');
            $d_hover_transition_delay = et_pb_responsive_options()->get_property_values($this->props, 'hover_transition_delay');
            $d_hover_transition_speed_curve = et_pb_responsive_options()->get_property_values($this->props, 'hover_transition_speed_curve');
            et_pb_responsive_options()->generate_responsive_css($d_hover_transition_duration, "%%order_class%% .pac_dth_taxonomy_inner:hover,%%order_class%% .pac_dth_image:hover,%%order_class%% .pac_dth_title:hover,%%order_class%% .pac_dth_desc:hover,%%order_class%% .pac_dth_readmore_btn:hover", 'transition-duration', $render_slug);
            et_pb_responsive_options()->generate_responsive_css($d_hover_transition_delay, "%%order_class%% .pac_dth_taxonomy_inner:hover,%%order_class%% .pac_dth_image:hover,%%order_class%% .pac_dth_title:hover,%%order_class%% .pac_dth_desc:hover,%%order_class%% .pac_dth_readmore_btn:hover", 'transition-delay', $render_slug);
            et_pb_responsive_options()->generate_responsive_css($d_hover_transition_speed_curve, "%%order_class%% .pac_dth_taxonomy_inner:hover,%%order_class%% .pac_dth_image:hover,%%order_class%% .pac_dth_title:hover,%%order_class%% .pac_dth_desc:hover,%%order_class%% .pac_dth_readmore_btn:hover", 'transition-timing-function', $render_slug, '', 'select');
        }

        /**
         * Get Taxonomies Fields
         * @return array
         */
        private static function get_taxonomies_fields()
        {
            $fields = [];
            $fields['taxonomy_background'] = [
                'label' => __('Background Color', 'divi-taxonomy-helper'),
                'description' => __('Set a background color for the taxonomy grid items.', 'divi-taxonomy-helper'),
                'type' => 'color-alpha',
                'custom_color' => true,
                'mobile_options' => true,
                'hover' => 'tabs',
                'tab_slug' => 'advanced',
                'toggle_slug' => 'taxonomies',
            ];
            $fields["taxonomy_term_default_icon"] = [
                'label' => __('Default Icon', 'divi-taxonomy-helper'),
                'description' => __('Set a default icon that applies to all taxonomy terms.', 'divi-taxonomy-helper'),
                'type' => 'select_icon',
                'default' => 'm',
                'tab_slug' => 'advanced',
                'toggle_slug' => 'icon',
                'sub_toggle' => 'default_toggle',
                'computed_affects' => ['__terms'],
            ];
            // Post Types
            foreach (pac_dth_get_registered_post_types() as $post_type_slug => $post_type) {
                $fields['taxonomy_'.$post_type_slug] = [
                    'label' => __('Taxonomy Type', 'divi-taxonomy-helper'),
                    'description' => __('Choose the type of taxonomies you want to display.', 'divi-taxonomy-helper'),
                    'type' => 'select',
                    'show_if' => ['post_type' => $post_type_slug],
                    'options' => pac_dth_get_taxonomies_by_object($post_type_slug),
                    'default' => key(pac_dth_get_taxonomies_by_object($post_type_slug)),
                    'toggle_slug' => 'list',
                    'computed_affects' => ['__terms'],
                ];
            }
            // Dynamic Taxonomies
            $fields['show_dynamic_taxonomies'] = [
                'label' => __('Dynamic Taxonomies', 'divi-taxonomy-helper'),
                'description' => __('Choose to enable dynamic content which allows you to place the module in a Divi Theme Builder layout to dynamically display taxonomies based on the template assignment.', 'divi-taxonomy-helper'),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                // 'show_if' => ['function.isTBLayout' => 'on'],
                'computed_affects' => ['__terms'],
                'toggle_slug' => 'list',
            ];
            // Include/Exclude Taxonomies
            $fields['include_exclude_taxonomies'] = [
                'label' => __('Include Or Exclude Taxonomy Terms', 'divi-taxonomy-helper'),
                'description' => __('Choose whether selected taxonomy terms should be included or excluded.', 'divi-taxonomy-helper'),
                'type' => 'select',
                'options' => [
                    'include' => __('Include', 'divi-taxonomy-helper'),
                    'exclude' => __('Exclude', 'divi-taxonomy-helper'),
                ],
                'default' => 'include',
                // 'show_if' => ['function.isTBLayout' => 'on'],
                'computed_affects' => ['__terms'],
                'toggle_slug' => 'list',
            ];
            // Taxonomies
            foreach (pac_dth_get_registered_post_types() as $post_type_slug => $post_type) {
                foreach (pac_dth_get_taxonomies_by_object($post_type_slug) as $taxonomies_slug => $taxonomy) {
                    if (isset($fields["taxonomy_terms_$taxonomies_slug"])) {
                        continue;
                    }
                    $fields['taxonomy_terms_'.$taxonomies_slug] = [
                        'label' => __('Included Taxonomy Terms', 'divi-taxonomy-helper'),
                        'description' => __('Choose which taxonomies you would like to include in the feed.', 'divi-taxonomy-helper'),
                        'type' => 'multiple_checkboxes',
                        'option_category' => 'basic_option',
                        'show_if' => [
                            'post_type' => $post_type_slug,
                            'taxonomy_'.$post_type_slug => $taxonomies_slug,
                            'show_dynamic_taxonomies' => 'off',
                            'include_exclude_taxonomies' => 'include',
                        ],
                        'options' => pac_dth_get_terms_by_taxonomy($taxonomies_slug),
                        'toggle_slug' => 'list',
                        'computed_affects' => ['__terms'],
                    ];
                    $fields['excluded_taxonomy_terms_'.$taxonomies_slug] = [
                        'label' => __('Excluded Taxonomy Terms', 'divi-taxonomy-helper'),
                        'description' => __('Choose which taxonomy terms you would like to exclude from the feed.', 'divi-taxonomy-helper'),
                        'type' => 'multiple_checkboxes',
                        'option_category' => 'basic_option',
                        'show_if' => [
                            'post_type' => $post_type_slug,
                            'taxonomy_'.$post_type_slug => $taxonomies_slug,
                            'show_dynamic_taxonomies' => 'off',
                            'include_exclude_taxonomies' => 'exclude',
                        ],
                        'options' => pac_dth_get_terms_by_taxonomy($taxonomies_slug),
                        'toggle_slug' => 'list',
                        'computed_affects' => ['__terms'],
                    ];
                    foreach (pac_dth_get_terms_by_taxonomy($taxonomies_slug, false) as $term_id => $term) {
                        // Term Background
                        $fields["taxonomy_term_bg_$term_id"] = [
                            'label' => __("$term Background", 'divi-taxonomy-helper'),
                            'description' => __('Set a background color for the taxonomy grid item.', 'divi-taxonomy-helper'),
                            'type' => 'color-alpha',
                            'custom_color' => true,
                            'mobile_options' => true,
                            'hover' => 'tabs',
                            'tab_slug' => 'advanced',
                            'toggle_slug' => 'taxonomies',
                            'show_if' => [
                                'post_type' => $post_type_slug,
                                'taxonomy_'.$post_type_slug => $taxonomies_slug,
                            ],
                            'computed_affects' => ['__terms'],
                        ];
                        // Term Icons
                        $fields["taxonomy_term_icon_$term_id"] = [
                            'label' => __("$term Icon", 'divi-taxonomy-helper'),
                            'description' => __('Choose icon to display with the taxonomy term.', 'divi-taxonomy-helper'),
                            'type' => 'select_icon',
                            'tab_slug' => 'advanced',
                            'toggle_slug' => 'icon',
                            'sub_toggle' => 'default_toggle',
                            'show_if' => [
                                'post_type' => $post_type_slug,
                                'taxonomy_'.$post_type_slug => $taxonomies_slug,
                            ],
                            'computed_affects' => ['__terms'],
                        ];
                        /* // Term Icons Color
                        $fields["taxonomy_term_icon_color_$term_id"] = [
                            'label' => __("$term Icon Color", 'divi-taxonomy-helper'),
                            'description' => __('Set a color for the taxonomy icon', 'divi-taxonomy-helper'),
                            'type' => 'color-alpha',
                            'custom_color' => true,
                            'mobile_options' => true,
                            'hover' => 'tabs',
                            'tab_slug' => 'advanced',
                            'toggle_slug' => 'icon',
                            'sub_toggle' => 'default_toggle',
                            'show_if' => [
                                'post_type' => $post_type_slug,
                                'taxonomy_'.$post_type_slug => $taxonomies_slug,
                            ],
                            'computed_affects' => ['__terms'],
                        ];*/
                    }
                }
            }

            return $fields;
        }

        /**
         * Get Computed Field Name
         * @return int[]|string[]
         */
        private static function computed_fields_names()
        {
            $fields = self::get_taxonomies_fields();
            $fields_name = [];
            foreach ($fields as $field_key => $field_name) {
                $fields_name[$field_key] = $field_key;
            }

            return array_keys($fields_name);
        }
    }

    (new TaxonomyHelper());
endif;