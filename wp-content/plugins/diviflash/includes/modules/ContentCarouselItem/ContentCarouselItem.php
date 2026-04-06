<?php

class DIFL_ContentCarouselItem extends ET_Builder_Module {
    public $slug       = 'difl_contentcarouselitem';
    public $vb_support = 'on';
    public $type       = 'child';
    public $child_title_var          = 'title';
	public $child_title_fallback_var = 'admin_label';
    use DF_UTLS;

    protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
    );

    public function init() {
        $this->name = esc_html__( 'Advanced Carousel Item', 'divi_flash' );
        $this->main_css_element = "%%order_class%%";
    }

    public function get_settings_modal_toggles(){
        return array(
            'general'   => array(
                'toggles'      => array(
                    'content' => esc_html__('Content', 'divi_flash'),
                    'image' => esc_html__('Image', 'divi_flash'),
                    'df_button' => esc_html__('Button', 'divi_flash'),
                    'item_order'    => esc_html__('Item Order', 'divi_flash')
                ),
            ),
            'advanced'   => array(
                'toggles'   => array(
                    'image' => esc_html__('Image', 'divi_flash'),
                    'title' => esc_html__('Title', 'divi_flash'),
                    'sub_title' => esc_html__('Sub Title', 'divi_flash'),
                    'df_content' => esc_html__('Content', 'divi_flash'),
                    'df_button' => esc_html__('Button', 'divi_flash'),
                    'custom_spacing'        => array (
                        'title'             => esc_html__('Custom Spacing', 'divi_flash'),
                        'tabbed_subtoggles' => true,
                        'sub_toggles' => array(
                            'wrapper'   => array(
                                'name' => 'Wrapper',
                            ),
                            'content'     => array(
                                'name' => 'Content',
                            )
                        )
                    )
                )
            ),
        );
    }

    public function get_advanced_fields_config() {
        $advanced_fields = array();
        $advanced_fields['text'] = false;
        $advanced_fields['fonts'] = array(
            'cc_title'     => array(
                'label'         => esc_html__( 'Title', 'divi_flash' ),
                'toggle_slug'   => 'title',
                'tab_slug'		=> 'advanced',
                'hide_text_shadow'  => true,
                'line_height' => array (
                    'default' => '1em',
                ),
                'font_size' => array(
                    'default' => '24px',
                ),
                'css'      => array(
                    'main' => ".difl_contentcarousel {$this->main_css_element} .df_cc_title",
                    'hover' => ".difl_contentcarousel {$this->main_css_element} .df_cci_container:hover .df_cc_title",
                    'important'	=> 'all'
                ),
            ),
            'cc_subtitle'     => array(
                'label'         => esc_html__( 'Sub Title', 'divi_flash' ),
                'toggle_slug'   => 'sub_title',
                'tab_slug'		=> 'advanced',
                'hide_text_shadow'  => true,
                'line_height' => array (
                    'default' => '1em',
                ),
                'font_size' => array(
                    'default' => '20px',
                ),
                'css'      => array(
                    'main' => ".difl_contentcarousel {$this->main_css_element} .df_cc_subtitle",
                    'hover' => ".difl_contentcarousel {$this->main_css_element} .df_cci_container:hover .df_cc_subtitle",
                    'important'	=> 'all'
                ),
            ),
            'cc_content'     => array(
                'label'         => esc_html__( 'Content', 'divi_flash' ),
                'toggle_slug'   => 'df_content',
                'tab_slug'		=> 'advanced',
                'hide_text_shadow'  => true,
                'line_height' => array (
                    'default' => '1.7em',
                ),
                'font_size' => array(
                    'default' => '14px',
                ),
                'css'      => array(
                    'main' => ".difl_contentcarousel {$this->main_css_element} .df_cc_content",
                    'hover' => ".difl_contentcarousel {$this->main_css_element} .df_cci_container:hover .df_cc_content",
                    'important'	=> 'all'
                ),
            ),
            'button'     => array(
                'label'         => esc_html__( 'Button', 'divi_flash' ),
                'toggle_slug'   => 'df_button',
                'tab_slug'		=> 'advanced',
                'hide_text_shadow'  => true,
                'line_height' => array (
                    'default' => '1em',
                ),
                'font_size' => array(
                    'default' => '14px',
                ),
                'css'      => array(
                    'main' => ".difl_contentcarousel {$this->main_css_element} .df_cci_button",
                    'hover' => ".difl_contentcarousel {$this->main_css_element} .df_cci_button:hover",
                    'important'	=> 'all'
                ),
            ),
        );
        $advanced_fields['borders'] = array(
            'default'   => array(
                'css'               => array(
                    'main' => array(
                        'border_radii' => ".difl_contentcarousel .swiper-wrapper {$this->main_css_element} > div:first-child",
                        'border_radii_hover'  => ".difl_contentcarousel .swiper-wrapper {$this->main_css_element} > div:first-child:hover",
                        'border_styles' => ".difl_contentcarousel .swiper-wrapper {$this->main_css_element} > div:first-child",
                        'border_styles_hover' => ".difl_contentcarousel .swiper-wrapper {$this->main_css_element} > div:first-child:hover",
                    )
                )
            ),
            'image_wrapper_border' => array(
                'css'      => array(
                    'main' => array(
                        'border_radii' => ".difl_contentcarousel {$this->main_css_element} .df_cci_image_container",
                        'border_radii_hover'  => ".difl_contentcarousel {$this->main_css_element} .df_cci_image_container:hover",
                        'border_styles' => ".difl_contentcarousel {$this->main_css_element} .df_cci_image_container",
                        'border_styles_hover' => ".difl_contentcarousel {$this->main_css_element} .df_cci_image_container:hover",
                    )
                ),
                'toggle_slug'    => 'image',
                'tab_slug'       => 'advanced'
            ),
            'button'                => array (
                'css'               => array(
                    'main' => array(
                        'border_radii' => ".difl_contentcarousel {$this->main_css_element} .df_cci_button",
                        'border_radii_hover'  => ".difl_contentcarousel {$this->main_css_element} .df_cci_button:hover",
                        'border_styles' => ".difl_contentcarousel {$this->main_css_element} .df_cci_button",
                        'border_styles_hover' => ".difl_contentcarousel {$this->main_css_element} .df_cci_button:hover",
                    )
                ),
                'tab_slug'          => 'advanced',
                'toggle_slug'       => 'df_button'
            ),
        );
        $advanced_fields['box_shadow'] = array(
            'default'   => array(
                'css' => array(
                    'main' => ".difl_contentcarousel .swiper-wrapper {$this->main_css_element} > div:first-child"
                )
            ),
            'image_wrapper_box_shadow' => array(
                'css' => array(
                    'main'  => ".difl_contentcarousel {$this->main_css_element} .df_cci_image_container",
                    'hover' => ".difl_contentcarousel {$this->main_css_element} .df_cci_image_container:hover",
                ),
                'toggle_slug' => 'image',
                'tab_slug'    => 'advanced',
            ),
            'button'              => array(
                'css' => array(
                    'main' => ".difl_contentcarousel {$this->main_css_element} .df_cci_button",
                ),
                'tab_slug'        => 'advanced',
                'toggle_slug'     => 'df_button'
            )
        );
        $advanced_fields['transform'] = array(
            'css' => array(
                'main' => "{$this->main_css_element} > div:first-child"
            )
        );
        $advanced_fields['background'] = array(
            'css' => array(
                'main' => ".difl_contentcarousel {$this->main_css_element}.difl_contentcarouselitem .df_cci_container"
            )
        );
        $advanced_fields['margin_padding'] = false;

        return $advanced_fields;
    }

    public function get_fields() {
        $general = array (
            'admin_label' => array (
				'label'           => esc_html__( 'Admin Label', 'divi_flash' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'toggle_slug'     => 'admin_label',
				'default_on_front'=> 'Content Carousel Item',
			)
        );
        $content = array (
            'title' => array (
                'label'                 => esc_html__( 'Title', 'divi_flash' ),
				'type'                  => 'text',
                'toggle_slug'           => 'content',
                'dynamic_content'       => 'text'
            ),
            'sub_title' => array (
                'label'                 => esc_html__( 'Sub Title', 'divi_flash' ),
				'type'                  => 'text',
                'toggle_slug'           => 'content',
                'dynamic_content'       => 'text'
            ),
            'content'        => array (
                'label'                 => esc_html__('Body', 'divi_flash'),
                'type'                  => 'tiny_mce',
                'toggle_slug'           => 'content',
                'dynamic_content'       => 'text'
            ),
            'title_tag' => array (
                'default'         => 'h4',
                'label'           => esc_html__( 'Title Tag', 'divi_flash' ),
                'type'            => 'select',
                'options'         => array(
                    'h1'    => esc_html__( 'h1 tag', 'divi_flash' ),
                    'h2'    => esc_html__( 'h2 tag', 'divi_flash' ),
                    'h3'    => esc_html__( 'h3 tag', 'divi_flash' ),
                    'h4'    => esc_html__( 'h4 tag', 'divi_flash' ),
                    'h5'    => esc_html__( 'h5 tag', 'divi_flash' ),
                    'h6'    => esc_html__( 'h6 tag', 'divi_flash'),
                    'p'     => esc_html__( 'p tag', 'divi_flash'),
                    'span'  => esc_html__( 'span tag', 'divi_flash'),
                    'div'  => esc_html__( 'div tag', 'divi_flash')
                ),
                'toggle_slug'   => 'title',
                'tab_slug'		=> 'advanced'
            ),
            'subtitle_tag' => array (
                'default'         => 'h5',
                'label'           => esc_html__( 'Title Tag', 'divi_flash' ),
                'type'            => 'select',
                'options'         => array(
                    'h1'    => esc_html__( 'h1 tag', 'divi_flash' ),
                    'h2'    => esc_html__( 'h2 tag', 'divi_flash' ),
                    'h3'    => esc_html__( 'h3 tag', 'divi_flash' ),
                    'h4'    => esc_html__( 'h4 tag', 'divi_flash' ),
                    'h5'    => esc_html__( 'h5 tag', 'divi_flash' ),
                    'h6'    => esc_html__( 'h6 tag', 'divi_flash'),
                    'p'     => esc_html__( 'p tag', 'divi_flash'),
                    'span'  => esc_html__( 'span tag', 'divi_flash'),
                    'div'  => esc_html__( 'div tag', 'divi_flash')
                ),
                'toggle_slug'   => 'sub_title',
                'tab_slug'		=> 'advanced'
            )
        );
        $image = $this->df_add_icon_settings(array (
            'title_prefix'          => 'Image & Icon',
            'key'                   => 'df_cci',
            'toggle_slug'           => 'image',
            'default_size'          => '48px',
            'icon_alignment'        => true,
            'image_styles'          => true,
            'icon_bg'               => true,
            'circle_icon'           => true,
            'img_toggle'            => 'image',
            'img_tab'               => 'advanced',
            'max_width'             => true,
            'image_alt'             => true,
            'dynamic_option'        => true
        ));
        $cc_button = $this->df_add_btn_content(array (
            'key'                   => 'cc_button',
            'toggle_slug'           => 'df_button',
            'dynamic_option'        =>  true
        ));
        $item_order = array (
            'image_order' => array (
                'label'             => esc_html__( 'Image Order', 'divi_flash' ),
                'type'              => 'range',
                'toggle_slug'       => 'item_order',
                'default_on_front' => 'off',
                'range_settings'    => array(
                    'min'  => '1',
                    'max'  => '15',
                    'step' => '1',
                ),
                'validate_unit'     => false,
                'description'       => esc_html__('Increase the order number to position the item lower.', 'divi_flash')
            ),
            'title_order' => array (
                'label'             => esc_html__( 'Title Order', 'divi_flash' ),
                'type'              => 'range',
                'toggle_slug'       => 'item_order',
                'default_on_front' => 'off',
                'range_settings'    => array(
                    'min'  => '1',
                    'max'  => '15',
                    'step' => '1',
                ),
                'validate_unit'     => false,
                'description'       => esc_html__('Increase the order number to position the item lower.', 'divi_flash')
            ),
            'subtitle_order' => array (
                'label'             => esc_html__( 'Sub Title Order', 'divi_flash' ),
                'type'              => 'range',
                'toggle_slug'       => 'item_order',
                'default_on_front' => 'off',
                'range_settings'    => array(
                    'min'  => '1',
                    'max'  => '15',
                    'step' => '1',
                ),
                'validate_unit'     => false,
                'description'       => esc_html__('Increase the order number to position the item lower.', 'divi_flash')
            ),
            'content_order' => array (
                'label'             => esc_html__( 'Content Order', 'divi_flash' ),
                'type'              => 'range',
                'toggle_slug'       => 'item_order',
                'default_on_front' => 'off',
                'range_settings'    => array(
                    'min'  => '1',
                    'max'  => '15',
                    'step' => '1',
                ),
                'validate_unit'     => false,
                'description'       => esc_html__('Increase the order number to position the item lower.', 'divi_flash')
            ),
            'button_order' => array (
                'label'             => esc_html__( 'Button Order', 'divi_flash' ),
                'type'              => 'range',
                'toggle_slug'       => 'item_order',
                'default_on_front' => 'off',
                'range_settings'    => array(
                    'min'  => '1',
                    'max'  => '15',
                    'step' => '1',
                ),
                'validate_unit'     => false,
                'description'       => esc_html__('Increase the order number to position the item lower.', 'divi_flash')
            )
        );
        $title_bg = $this->df_add_bg_field(array (
			'label'				    => 'Background',
            'key'                   => 'df_title_bg',
            'toggle_slug'           => 'title',
            'tab_slug'              => 'advanced'
        ));
        $subtitle_bg = $this->df_add_bg_field(array (
			'label'				    => 'Background',
            'key'                   => 'df_subtitle_bg',
            'toggle_slug'           => 'sub_title',
            'tab_slug'              => 'advanced'
        ));
        $content_bg = $this->df_add_bg_field(array (
			'label'				    => 'Background',
            'key'                   => 'df_content_bg',
            'toggle_slug'           => 'df_content',
            'tab_slug'              => 'advanced'
        ));
        $cc_button_style = $this->df_add_btn_styles(array (
            'key'                   => 'cc_button',
            'toggle_slug'           => 'df_button',
            'tab_slug'              => 'advanced'
        ));
        $buttons_bg = $this->df_add_bg_field(array (
			'label'				    => 'Button Background',
            'key'                   => 'df_button_bg',
            'toggle_slug'           => 'df_button',
            'tab_slug'              => 'advanced'
        ));
        $button_wrapper_spacing = $this->add_margin_padding(array(
            'title'         => 'Button Wrapper',
            'key'           => 'button_wrapper',
            'toggle_slug'   => 'df_button'
        ));
        $button_spacing = $this->add_margin_padding(array(
            'title'         => 'Button',
            'key'           => 'button',
            'toggle_slug'   => 'df_button'
        ));

        // spacing
        $item_wrapper_spacing = $this->add_margin_padding(array(
            'title'         => 'Item Wrapper',
            'key'           => 'item_wrapper',
            'toggle_slug'   => 'custom_spacing',
            'sub_toggle'    => 'wrapper'
        ));
        $image_wrapper_spacing = $this->add_margin_padding(array(
            'title'         => 'Image Wrapper',
            'key'           => 'image_wrapper',
            'toggle_slug'   => 'custom_spacing',
            'sub_toggle'    => 'wrapper'
        ));
        $image_spacing = $this->add_margin_padding(array(
            'title'         => 'Image',
            'key'           => 'image',
            'toggle_slug'   => 'custom_spacing',
            'sub_toggle'    => 'content',
            'option'        => 'margin'
        ));
        $title_spacing = $this->add_margin_padding(array(
            'title'         => 'Title',
            'key'           => 'title',
            'toggle_slug'   => 'custom_spacing',
            'sub_toggle'    => 'content'
        ));
        $subtitle_spacing = $this->add_margin_padding(array(
            'title'         => 'Subtitle',
            'key'           => 'subtitle',
            'toggle_slug'   => 'custom_spacing',
            'sub_toggle'    => 'content'
        ));
        $content_spacing = $this->add_margin_padding(array(
            'title'         => 'Content',
            'key'           => 'content',
            'toggle_slug'   => 'custom_spacing',
            'sub_toggle'    => 'content'
        ));

        return array_merge(
            $general,
            $content,
            $image,
            $title_bg,
            $subtitle_bg,
            $content_bg,
            $cc_button,
            $item_order,
            $cc_button_style,
            $buttons_bg,
            $button_wrapper_spacing,
            $button_spacing,
            $item_wrapper_spacing,
            $image_wrapper_spacing,
            $image_spacing,
            $title_spacing,
            $subtitle_spacing,
            $content_spacing
        );
    }

    public function get_transition_fields_css_props() {
        $fields = parent::get_transition_fields_css_props();
        $button_wrapper = '%%order_class%% .df_cci_button_wrapper';
        $button = '%%order_class%% .df_cci_button';
        $icon = '%%order_class%% .et-pb-icon';
        $title = '%%order_class%% .df_cc_title';
        $subtitle = '%%order_class%% .df_cc_subtitle';
        $content = '%%order_class%% .df_cc_content';
        // spacing
        $fields['button_wrapper_margin'] = array('margin' => $button_wrapper);
        $fields['button_wrapper_padding'] = array('padding' => $button_wrapper);
        $fields['button_margin'] = array('margin' => $button);
        $fields['button_padding'] = array('padding' => $button);

        $fields['df_cci_icon_color'] = array('color' => $icon);
        $fields['df_cci_icon_bg'] = array('background-color' => $icon);

        $fields['item_wrapper_margin'] = array('margin' => '%%order_class%% > div');
        $fields['item_wrapper_padding'] = array('padding' => '%%order_class%% > div');

        $fields['image_wrapper_margin'] = array('margin' => '%%order_class%% .df_cci_image_container');
        $fields['image_wrapper_padding'] = array('padding' => '%%order_class%% .df_cci_image_container');
        
        $fields['image_margin'] = array('margin' => '%%order_class%% .df_cci_image_container img');
        
        $fields['title_margin'] = array('margin' => $title);
        $fields['title_padding'] = array('padding' => $title);

        $fields['subtitle_margin'] = array('margin' => $subtitle);
        $fields['subtitle_padding'] = array('padding' => $subtitle);

        $fields['content_margin'] = array('margin' => $content);
        $fields['content_padding'] = array('padding' => $content);

        // background
        $fields = $this->df_background_transition(array (
            'fields'        => $fields,
            'key'           => 'df_button_bg',
            'selector'      => $button
        ));
        $fields = $this->df_background_transition(array (
            'fields'        => $fields,
            'key'           => 'df_title_bg',
            'selector'      => $title
        ));
        $fields = $this->df_background_transition(array (
            'fields'        => $fields,
            'key'           => 'df_subtitle_bg',
            'selector'      => $subtitle
        ));
        $fields = $this->df_background_transition(array (
            'fields'        => $fields,
            'key'           => 'df_content_bg',
            'selector'      => $content
        ));

        // border fix
        $fields = $this->df_fix_border_transition(
            $fields, 
            'button', 
            $button
        );
        
        $fields = $this->df_fix_border_transition(
            $fields, 
            'image_wrapper_border', 
            ".difl_contentcarousel {$this->main_css_element} .df_cci_image_container"
        );

        //box shadow
        $fields = $this->df_fix_box_shadow_transition($fields, 'image_wrapper_box_shadow', ".difl_contentcarousel {$this->main_css_element} .df_cci_image_container");

        return $fields;
    }
    
    public function additional_css_styles($render_slug) {

        // item order
        $this->df_process_range( array(
            'render_slug'       => $render_slug,
            'slug'              => 'image_order',
            'type'              => 'order',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cci_image_container"
        ) );
        $this->df_process_range( array(
            'render_slug'       => $render_slug,
            'slug'              => 'title_order',
            'type'              => 'order',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cc_title"
        ) );
        $this->df_process_range( array(
            'render_slug'       => $render_slug,
            'slug'              => 'subtitle_order',
            'type'              => 'order',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cc_subtitle"
        ) );
        $this->df_process_range( array(
            'render_slug'       => $render_slug,
            'slug'              => 'content_order',
            'type'              => 'order',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cc_content"
        ) );
        $this->df_process_range( array(
            'render_slug'       => $render_slug,
            'slug'              => 'button_order',
            'type'              => 'order',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cci_button_wrapper"
        ) );
        // icons
        $this->process_icon_styles(array (
            'render_slug'       => $render_slug,
            'slug'              => 'df_cci',
            'selector'          => '%%order_class%% .et-pb-icon',
            'hover'             => '%%order_class%% .et-pb-icon:hover',
            'align_container'   => '%%order_class%% .df_cci_image_container',
            'image_selector'    => '%%order_class%% .df_cci_image_container img'
        ));
        // button
        $this->df_process_bg(array (
            'render_slug'       => $render_slug,
            'slug'              => 'df_button_bg',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cci_button",
            'hover'             => ".difl_contentcarousel {$this->main_css_element} .df_cci_button:hover"
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'button_wrapper_margin',
            'type'              => 'margin',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cci_button_wrapper",
            'hover'             => ".difl_contentcarousel {$this->main_css_element} .df_cci_button_wrapper:hover",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'button_wrapper_padding',
            'type'              => 'padding',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cci_button_wrapper",
            'hover'             => ".difl_contentcarousel {$this->main_css_element} .df_cci_button_wrapper:hover",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'button_margin',
            'type'              => 'margin',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cci_button",
            'hover'             => ".difl_contentcarousel {$this->main_css_element} .df_cci_button:hover",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'button_padding',
            'type'              => 'padding',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cci_button",
            'hover'             => ".difl_contentcarousel {$this->main_css_element} .df_cci_button:hover",
        ));
        $this->df_process_btn_styles(array (
            'render_slug'       => $render_slug,
            'slug'              => 'cc_button',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cci_button",
            'hover'             => ".difl_contentcarousel {$this->main_css_element} .df_cci_button:hover",
            'align_container'   => ".difl_contentcarousel {$this->main_css_element} .df_cci_button_wrapper"
        ));

        // content area background
        $this->df_process_bg(array (
            'render_slug'       => $render_slug,
            'slug'              => 'df_title_bg',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cc_title",
            'hover'             => ".difl_contentcarousel {$this->main_css_element} .df_cci_container .df_cc_title:hover"
        ));
        $this->df_process_bg(array (
            'render_slug'       => $render_slug,
            'slug'              => 'df_subtitle_bg',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cc_subtitle",
            'hover'             => ".difl_contentcarousel {$this->main_css_element} .df_cci_container .df_cc_subtitle:hover"
        ));
        $this->df_process_bg(array (
            'render_slug'       => $render_slug,
            'slug'              => 'df_content_bg',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} .df_cc_content",
            'hover'             => ".difl_contentcarousel {$this->main_css_element} .df_cci_container .df_cc_content:hover"
        ));

        // spacing
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'item_wrapper_margin',
            'type'              => 'margin',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} > div:first-child",
            'hover'             => ".difl_contentcarousel {$this->main_css_element}:hover > div:first-child",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'item_wrapper_padding',
            'type'              => 'padding',
            'selector'          => ".difl_contentcarousel {$this->main_css_element} > div:first-child",
            'hover'             => ".difl_contentcarousel {$this->main_css_element}:hover > div:first-child",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'image_wrapper_margin',
            'type'              => 'margin',
            'selector'          => "{$this->main_css_element} .df_cci_image_container",
            'hover'             => "{$this->main_css_element}:hover .df_cci_image_container",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'image_wrapper_padding',
            'type'              => 'padding',
            'selector'          => "{$this->main_css_element} .df_cci_image_container",
            'hover'             => "{$this->main_css_element}:hover .df_cci_image_container",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'image_margin',
            'type'              => 'margin',
            'selector'          => "{$this->main_css_element} .df_cci_image_container img",
            'hover'             => "{$this->main_css_element}:hover .df_cci_image_container img",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'title_margin',
            'type'              => 'margin',
            'selector'          => "{$this->main_css_element} .df_cc_title",
            'hover'             => "{$this->main_css_element}:hover .df_cc_title",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'title_padding',
            'type'              => 'padding',
            'selector'          => "{$this->main_css_element} .df_cc_title",
            'hover'             => "{$this->main_css_element}:hover .df_cc_title",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'subtitle_margin',
            'type'              => 'margin',
            'selector'          => "{$this->main_css_element} .df_cc_subtitle",
            'hover'             => "{$this->main_css_element}:hover .df_cc_subtitle",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'subtitle_padding',
            'type'              => 'padding',
            'selector'          => "{$this->main_css_element} .df_cc_subtitle",
            'hover'             => "{$this->main_css_element}:hover .df_cc_subtitle",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'content_margin',
            'type'              => 'margin',
            'selector'          => "{$this->main_css_element} .df_cc_content",
            'hover'             => "{$this->main_css_element}:hover .df_cc_content",
        ));
        $this->set_margin_padding_styles(array(
            'render_slug'       => $render_slug,
            'slug'              => 'content_padding',
            'type'              => 'padding',
            'selector'          => "{$this->main_css_element} .df_cc_content",
            'hover'             => "{$this->main_css_element}:hover .df_cc_content",
        ));

        // icon font family
        if(method_exists('ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon')) {
            $this->generate_styles(
                array(
                    'utility_arg'    => 'icon_font_family',
                    'render_slug'    => $render_slug,
                    'base_attr_name' => 'df_cci_font_icon',
                    'important'      => true,
                    'selector'       => '%%order_class%% .et-pb-icon',
                    'processor'      => array(
                        'ET_Builder_Module_Helper_Style_Processor',
                        'process_extended_icon',
                    ),
                )
            );
        }
    }

    /**
     * Render image for the front image
     * 
     * @param String $key
     * @return HTML | markup for the image
     */
    public function df_render_image($key = '') {
        if ( isset($this->props[$key . '_use_icon']) && $this->props[$key . '_use_icon'] === 'on' ) {
            return sprintf('<div class="df_cci_image_container">
                    <span class="et-pb-icon">%1$s</span>
                </div>', 
                isset($this->props[$key . '_font_icon']) && $this->props[$key . '_font_icon'] !== '' ? 
                    esc_attr(et_pb_process_font_icon( $this->props[$key . '_font_icon'] )) : '5'
            );
        } else if ( isset($this->props[$key . '_image']) && $this->props[$key . '_image'] !== ''){
            $image_alt = $this->props[$key . '_alt_text'] !== '' ? $this->props[$key . '_alt_text']  : df_image_alt_by_url($this->props[$key . '_image']);
            $image_url = $this->props[$key . '_image'];  
            return sprintf('<div class="df_cci_image_container">
                    <img class="df_cci_image" alt="%2$s" src="%1$s" />
                </div>',
                esc_attr($image_url),
                esc_attr($image_alt)
            );
        }
    }

    /**
     * Render button HTML markup
     * 
     * @param String $key
     * @return String HTML markup of the button
     */
    public function df_render_button($key) {
        $text = isset($this->props[$key . '_button_text']) ? $this->props[$key . '_button_text'] : '';
        $url = isset($this->props[$key . '_button_url']) ? $this->props[$key . '_button_url'] : '';
        $target = $this->props[$key . '_button_url_new_window'] === 'on'  ? 
            'target="_blank"' : '';
        if($text !== '' || $url !== '') {
            return sprintf('<div class="df_cci_button_wrapper">
                <a class="df_cci_button" href="%1$s" %3$s>%2$s</a>
            </div>', 
            esc_attr($url), esc_html($text), $target);
        } else { return ''; }
    }

    public function render( $attrs, $content, $render_slug ) {
        $this->additional_css_styles($render_slug);
        array_push($this->classname, 'swiper-slide');

        $parent_module = isset(self::get_parent_modules('page')['difl_contentcarousel']) ? self::get_parent_modules('page')['difl_contentcarousel']: new stdClass();

        $title = isset($this->props['title']) && $this->props['title'] !== '' ?
            sprintf('<%2$s class="df_cc_title">%1$s</%2$s>', 
                esc_html($this->props['title']),
                esc_attr($this->props['title_tag'])
            ) : '';
        $sub_title = isset($this->props['sub_title']) && $this->props['sub_title'] !== '' ?
            sprintf('<%2$s class="df_cc_subtitle">%1$s</%2$s>', 
                esc_html($this->props['sub_title']),
                esc_attr($this->props['subtitle_tag'])
            ) : '';
        $content = isset($this->props['content']) && $this->props['content'] !== '' ?
            sprintf('<div class="df_cc_content">%1$s</div>', $this->props['content']) : '';
         
        if(isset($parent_module->props['use_lightbox_title']) && $parent_module->props['use_lightbox_title'] === 'on'){
            
            $lightbox_title = $this->props['title'] !== '' ? 'data-sub-html=".df_cc_title"' : '';
        }
        else{
            $lightbox_title = '';
        }
        
        return sprintf('%8$s%9$s
            <div class="df_cci_container" data-src="%6$s" %7$s>
                %10$s%11$s
                %4$s%1$s%2$s%3$s%5$s
            </div>',
            $title,
            $sub_title,
            $content,
            $this->df_render_image('df_cci'),
            $this->df_render_button('cc_button'),
            esc_attr($this->props['df_cci_image']),
            $lightbox_title,
            isset($parent_module->props['background_enable_pattern_style']) ? $this->df_render_pattern_or_mask_html($parent_module->props['background_enable_pattern_style'], 'pattern') : '',
            isset($parent_module->props['background_enable_mask_style']) ? $this->df_render_pattern_or_mask_html($parent_module->props['background_enable_mask_style'], 'mask') : '',
            isset($this->props['background_enable_pattern_style']) ? $this->df_render_pattern_or_mask_html($this->props['background_enable_pattern_style'], 'pattern') : '',
            isset($this->props['background_enable_mask_style']) ? $this->df_render_pattern_or_mask_html($this->props['background_enable_mask_style'], 'mask') : ''
        
        );
    }
    
}
new DIFL_ContentCarouselItem;
