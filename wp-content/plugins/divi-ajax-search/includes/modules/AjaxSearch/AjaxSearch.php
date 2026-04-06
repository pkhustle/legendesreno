<?php

class EL_AjaxSearch extends ET_Builder_Module {

	public $slug       = 'el_ajax_search';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-ajax-search',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name = esc_html__( 'Divi Ajax Search', 'divi-ajax-search' );
		$this->main_css_element = '%%order_class%%';
		add_filter( 'et_builder_processed_range_value', array( $this, 'das_builder_processed_range_value' ), 10, 3 );
		add_filter( 'et_late_global_assets_list', array( $this, 'das_late_assets' ), 10, 3 );
	}

	public function das_late_assets( $assets_list, $assets_args, $dynamic_assets ) {
		if ( function_exists( 'et_get_dynamic_assets_path' ) && function_exists( 'et_is_cpt' ) ) {
			$cpt_suffix = et_is_cpt() ? '_cpt' : '';
			$assets_list['et_icons_all'] = array(
				'css' => $assets_args['assets_prefix'] . '/css/icons_all.css',
			);
			$assets_list['et_icons_fa'] = array(
				'css' => $assets_args['assets_prefix'] . '/css/icons_fa_all.css',
			);
			$assets_list['et_icons_social'] = array(
				'css' => $assets_args['assets_prefix'] . '/css/icons_base_social.css',
			);
		}
		return $assets_list;
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content' => array(
						'title' => esc_html__( 'Configuration', 'divi-ajax-search' ),
					),
					'search_area' => array(
						'title' => esc_html__( 'Search Area', 'divi-ajax-search' ),
					),
					'display' => array(
						'title' => esc_html__( 'Display', 'divi-ajax-search' ),
					),
					'pagination' => array(
						'title' => esc_html__( 'Pagination', 'divi-ajax-search' ),
					),
					'scrollbar' => array(
						'title' => esc_html__( 'Scrollbar', 'divi-ajax-search' ),
					),
					'link' => array(
						'title' => esc_html__( 'Link', 'divi-ajax-search' ),
					),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'search_field_settings' => array(
						'title' => esc_html__( 'Search Field', 'divi-ajax-search' ),
					),
					'search_icon_settings' => array(
						'title' => esc_html__( 'Search Icon', 'divi-ajax-search' ),
					),
					'clear_icon_settings' => array(
						'title' => esc_html__( 'Clear Icon', 'divi-ajax-search' ),
					),
					'loader_settings' => array(
						'title' => esc_html__( 'Loader', 'divi-ajax-search' ),
					),
					'search_result_item' => array(
						'title' => esc_html__( 'Search Result Item', 'divi-ajax-search' ),
					),
					'search_result_item_text_settings' => array(
						'title' => esc_html__( 'Search Result Item Text', 'divi-ajax-search' ),
						'sub_toggles'   => array(
							'title' => array(
								'name' => esc_html__( 'Title', 'divi-ajax-search' ),
							),
							'excerpt' => array(
								'name' => esc_html__( 'Excerpt', 'divi-ajax-search' ),
							),
							'price' => array(
								'name' => esc_html__( 'Price', 'divi-ajax-search' ),
							),
							'no_result' => array(
								'name' => esc_html__( 'No Result', 'divi-ajax-search' ),
							),
						),
						'tabbed_subtoggles' => true,
					),
					'featured_image_settings' => array(
						'title' => esc_html__( 'Featured Image', 'divi-ajax-search' ),
					),
					'pagination' => array(
						'title' => esc_html__( 'Pagination', 'divi-ajax-search' ),
					),
					'show_result_link' => array(
						'title' => esc_html__( 'See All Results Link', 'divi-ajax-search' ),
					),
				),
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts' => array(
				'search_result_item_title' => array(
					'label'          => esc_html__( 'Title', 'divi-ajax-search' ),
					'font_size'      => array(
						'default_on_front' => '16px',
						'range_settings'   => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'line_height'    => array(
						'default_on_front' => '1.2em',
						'range_settings'   => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default_on_front' => '0px',
						'range_settings'   => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'hide_text_align' => true,
					'css'            => array(
						'main' => '%%order_class%% .el_ajax_search_item .el_ajax_search_item_title',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'search_result_item_text_settings',
					'sub_toggle' => 'title',
				),
				'search_result_item_excerpt' => array(
					'label'          => esc_html__( 'Excerpt', 'divi-ajax-search' ),
					'font_size'      => array(
						'default_on_front' => '14px',
						'range_settings'   => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'line_height'    => array(
						'default_on_front' => '1.5em',
						'range_settings'   => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default_on_front' => '0px',
						'range_settings'   => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'hide_text_align' => true,
					'css'            => array(
						'main' => '%%order_class%% .el_ajax_search_item .el_ajax_search_item_excerpt',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'search_result_item_text_settings',
					'sub_toggle' => 'excerpt',
				),
				'search_result_item_price' => array(
					'label'          => esc_html__( 'Price', 'divi-ajax-search' ),
					'font_size'      => array(
						'default_on_front' => '16px',
						'range_settings'   => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'line_height'    => array(
						'default_on_front' => '1.2em',
						'range_settings'   => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default_on_front' => '0px',
						'range_settings'   => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'hide_text_align' => true,
					'css'            => array(
						'main' => '%%order_class%% .el_ajax_search_item .el_ajax_search_item_price',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'search_result_item_text_settings',
					'sub_toggle' => 'price',
				),
				'no_result' => array(
					'label'          => esc_html__( 'No Result', 'divi-ajax-search' ),
					'font_size'      => array(
						'default_on_front' => '14px',
						'range_settings'   => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'line_height'    => array(
						'default_on_front' => '1.7em',
						'range_settings'   => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default_on_front' => '0px',
						'range_settings'   => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'css' => array(
						'main' => '%%order_class%% .el_ajax_search_no_results',
						'important' => 'all',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'search_result_item_text_settings',
					'sub_toggle' => 'no_result',
				),
				'show_result_link' => array(
					'label'          => esc_html__( 'See All Results Link', 'divi-ajax-search' ),
					'font_size'      => array(
						'default' => '14px',
						'range_settings'   => array(
							'min'  => '1',
							'max'  => '50',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'letter_spacing' => array(
						'default' => '0px',
						'range_settings'   => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'    => true,
					),
					'hide_text_align' => true,
					'css' => array(
						'main' => '%%order_class%% .el_ajax_search_result_link_wrap .el_ajax_search_result_link',
						'important' => 'all',
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'show_result_link',
				),
				'pagination_link' => array(
					'label'          => esc_html__( 'Pagination Link', 'divi-ajax-search' ),
					'font_size'      => array(
						'default'        => '16px',
						'range_settings' => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'line_height'    => array(
						'default'        => '1.5em',
						'range_settings' => array(
							'min'  => '0.1',
							'max'  => '10',
							'step' => '0.1',
						),
					),
					'letter_spacing' => array(
						'default'        => '0px',
						'range_settings' => array(
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						),
						'validate_unit'  => true,
					),
					'hide_text_color' => true,
					'hide_text_shadow' => true,
					'hide_text_align' => true,
					'css'            => array(
						'main'  => "{$this->main_css_element} .el_ajax_search_pagination li a",
						'text_align' => "{$this->main_css_element} .el_ajax_search_pagination_wrap"
					),
					'tab_slug'	=> 'advanced',
                    'toggle_slug' => 'pagination',
				),
			),
			'form_field' => array(
				'form_field' => array(
					'label' => esc_html__( 'Field', 'divi-ajax-search' ),
					'css' => array(
						'main' 			=> '%%order_class%% .el_ajax_search_field',
						'hover' 		=> '%%order_class%% .el_ajax_search_field:hover',
						'focus' 		=> '%%order_class%% .el_ajax_search_field:focus',
						'focus_hover' 	=> '%%order_class%% .el_ajax_search_field:focus:hover',
					),
					'font_field' => array(
						'css' => array(
							'main' => implode(', ', array(
								'%%order_class%% .el_ajax_search_field',
								'%%order_class%% .el_ajax_search_field::placeholder',
								'%%order_class%% .el_ajax_search_field::-webkit-input-placeholder',
								'%%order_class%% .el_ajax_search_field::-ms-input-placeholder',
								'%%order_class%% .el_ajax_search_field::-moz-placeholder',
							) ),
							'hover' => implode(', ', array(
								'%%order_class%% .el_ajax_search_field:',
								'%%order_class%% .el_ajax_search_field::placeholder',
								'%%order_class%% .el_ajax_search_field::-webkit-input-placeholder',
								'%%order_class%% .el_ajax_search_field::-ms-input-placeholder',
								'%%order_class%% .el_ajax_search_field::-moz-placeholder',
							) ),
							'focus' => implode(', ', array(
								'%%order_class%% .el_ajax_search_field',
								'%%order_class%% .el_ajax_search_field::placeholder',
								'%%order_class%% .el_ajax_search_field::-webkit-input-placeholder',
								'%%order_class%% .el_ajax_search_field::-ms-input-placeholder',
								'%%order_class%% .el_ajax_search_field::-moz-placeholder',
							) ),
							'focus_hover' => implode(', ', array(
								'%%order_class%% .el_ajax_search_field',
								'%%order_class%% .el_ajax_search_field::placeholder',
								'%%order_class%% .el_ajax_search_field::-webkit-input-placeholder',
								'%%order_class%% .el_ajax_search_field::-ms-input-placeholder',
								'%%order_class%% .el_ajax_search_field::-moz-placeholder',
							) ),
							'placeholder' => true,
						),
						'line_height'    => array(
							'default' => '1em',
						),
						'font_size'      => array(
							'default' => '14px',
						),
						'letter_spacing' => array(
							'default' => '0px',
						),
					),
					'margin_padding' => array(
						'use_margin' => false,
						'css'        => array(
							'padding' => '%%order_class%% .el_ajax_search_field',
						),
					),
					'box_shadow' => false,
					'border_styles' => array(
						'form_field' => array(
							'fields_after' => array(
								'use_focus_border_color' => false,
							),
							'css' => array(
								'main' => array(
									'border_radii'  => '%%order_class%% .el_ajax_search_field',
									'border_styles' => '%%order_class%% .el_ajax_search_field',
								),
								'important' => 'all',
							),
							'label_prefix' => esc_html__( 'Field', 'divi-ajax-search' ),
						),
					),
					'tab_slug' => 'advanced',
					'toggle_slug' => 'search_field_settings',
				),
			),
			'margin_padding' => array(
				'css' => array(
					'main'      => '%%order_class%%',
					'important' => 'all',
				),
			),
			'search_result_margin_padding' => array(
				'search_result_item_content' => array(
					'margin_padding' => array(
						'css' => array(
							'use_margin' => false,
							'padding'    => '%%order_class%% .el_ajax_search_results .el_ajax_search_items .el_ajax_search_item_content',
							'important'  => 'all',
						),
					),
				),
				'search_result_box' => array(
					'margin_padding' => array(
						'css' => array(
							'use_margin' => false,
							'padding'    => '%%order_class%% .el_ajax_search_results',
							'important'  => 'all',
						),
					),
				),
			),
			'max_width' => array(
				'default' => array(
					'css' => array(
						'main'             => '%%order_class%%',
						'module_alignment' => '%%order_class%%',
					),
				),
				'extra' => array(
					'featured_image' => array(
						'options' => array(
							'width' => array(
								'label'          => esc_html__( 'Featured Image Width', 'divi-ajax-search' ),
								'range_settings' => array(
									'min'  => 1,
									'max'  => 300,
									'step' => 1,
								),
								'hover'          => false,
								'default_unit'   => 'px',
								'default'		 => '85px',
								'default_tablet' => '',
								'default_phone'  => '',
								'show_if'      => array(
									'layout' => array( 'layout1' ),
								),
								'tab_slug'       => 'advanced',
								'toggle_slug'    => 'featured_image_settings',
							),
						),
						'use_max_width'        => false,
						'use_module_alignment' => false,
						'css'                  => array(
							'main'      => "{$this->main_css_element} .el_ajax_search_item_image",
							'important' => 'all',
						),
					),
				),
			),
			'height' => array(
				'default' => array(
					'css' => array(
						'main' => '%%order_class%%',
					),
				),
				'extra' => array(
					'featured_image' => array(
						'options' => array(
							'height' => array(
								'label'          => esc_html__( 'Featured Image Height', 'divi-ajax-search' ),
								'range_settings' => array(
									'min'  => 1,
									'max'  => 300,
									'step' => 1,
								),
								'hover'          => false,
								'default_unit'   => 'px',
								'default'	     => 'auto',
								'default_tablet' => '',
								'default_phone'  => '',
								'tab_slug'       => 'advanced',
								'toggle_slug'    => 'featured_image_settings',
							),
						),
						'use_max_height' => false,
						'use_min_height' => false,
						'css'            => array(
							'main'      => "{$this->main_css_element} .el_ajax_search_item_image",
							'important' => 'all',
						),
					),
				),
			),
			'borders' => array(
				'search_result_item' => array(
					'label_prefix' => esc_html__( 'Search Result Item', 'divi-ajax-search' ),
					'css'          => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .el_ajax_search_results .el_ajax_search_item',
							'border_styles' => '%%order_class%% .el_ajax_search_results .el_ajax_search_item',
							'important'     => 'all',
						),
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'search_result_item',
				),
				'search_result_box' => array(
					'label_prefix' => esc_html__( 'Result Box', 'divi-ajax-search' ),
					'css'          => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .el_ajax_search_results',
							'border_styles' => '%%order_class%% .el_ajax_search_results',
						),
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'border',
				),
				'show_result_link' => array(
					'label_prefix' => esc_html__( 'See All Results Link', 'divi-ajax-search' ),
					'css'          => array(
						'main' => array(
							'border_radii'  => '%%order_class%% .el_ajax_search_result_link_inner',
							'border_styles' => '%%order_class%% .el_ajax_search_result_link_inner',
						),
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'show_result_link',
				),
				'default' => array(
					'css' => array(
						'main' => array(
							'border_styles' => '%%order_class%%',
							'border_radii'  => '%%order_class%%',
						),
					),
				),
			),
			'box_shadow' => array(
				'search_result_item' => array(
					'label'       => esc_html__( 'Search Result Item - Box Shadow', 'divi-ajax-search' ),
					'css'         => array(
						'main' => '%%order_class%% .el_ajax_search_results .el_ajax_search_item',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'search_result_item',
				),
				'search_result_box' => array(
					'label'       => esc_html__( 'Result Box - Box Shadow', 'divi-ajax-search' ),
					'css'         => array(
						'main' => '%%order_class%% .el_ajax_search_results',
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'box_shadow',
				),
				'default' => array(
					'css' => array(
						'main' => '%%order_class%%',
					),
				),
			),
			'filters' => false,
			'text' => false,
			'link_options'  => false,
		);
	}

	public function get_fields() {
		$raw_post_types = get_post_types( array(
			'public' => true,
			'show_ui' => true,
			'exclude_from_search' => false,
		), 'objects' );

		$blocklist = array( 'et_pb_layout', 'layout', 'attachment' );

		$post_types = array();
		foreach ( $raw_post_types as $post_type ) {
			$is_blocklisted = in_array( $post_type->name, $blocklist );
			
			if ( ! $is_blocklisted && post_type_exists( $post_type->name ) ) {
				if ( isset( $post_type->label ) ) {
					$label = esc_html( $post_type->label );
				} elseif ( isset( $post_type->labels->name ) ) {
					$label = esc_html( $post_type->labels->name );
				} elseif ( isset( $post_type->labels->singular_name ) ) {
					$label = esc_html( $post_type->labels->singular_name );
				} else {
					$label = esc_html( $post_type->name );
				}
				$slug  	= sanitize_text_field( $post_type->name );
				$post_types[$slug] = esc_html( $label );
			}
		}

		if ( ! empty( $post_types ) ) {
			$post_types['all'] = esc_html__( 'All of the above', 'divi-ajax-search' );
		}

		$search_in = array( 
			'post_title'         => esc_html__( 'Title', 'divi-ajax-search' ),
			'post_content'       => esc_html__( 'Content', 'divi-ajax-search' ),
			'post_excerpt'       => esc_html__( 'Excerpt', 'divi-ajax-search' ),
			'taxonomies'         => esc_html__( 'Taxonomies', 'divi-ajax-search' ),
			'product_attributes' => esc_html__( 'Product Attributes', 'divi-ajax-search' ),
			'sku'                => esc_html__( 'Product SKU', 'divi-ajax-search' ),
		);

		$display_fields = array(
			'title'          => esc_html__( 'Title', 'divi-ajax-search' ),
			'excerpt'        => esc_html__( 'Excerpt', 'divi-ajax-search' ),
			'featured_image' => esc_html__( 'Featured Image', 'divi-ajax-search' ),
			'product_price'  => esc_html__( 'Product Price', 'divi-ajax-search' ),
		);

		return array_merge(
			array(
				'search_placeholder' => array(
					'label'            => esc_html__( 'Search Field Placeholder', 'divi-ajax-search' ),
					'type'             => 'text',
					'option_category'  => 'basic_option',
					'default_on_front' => esc_html__( 'Search', 'divi-ajax-search' ),
					'default'          => esc_html__( 'Search', 'divi-ajax-search' ),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can input the placeholder to be used for the search field.', 'divi-ajax-search' ),
				),
				'number_of_results' => array(
					'label'           => esc_html__( 'Search Result Number', 'divi-ajax-search' ),
					'type'            => 'text',
					'option_category' => 'basic_option',
					'default'         => '10',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'main_content',
					'description'     => esc_html__( 'Here you can input the number of items to be displayed in the search result. Input -1 for all.', 'divi-ajax-search' ),
				),
				'orderby' => array(
					'label'           => esc_html__( 'Order by', 'divi-ajax-search' ),
					'type'            => 'select',
					'option_category' => 'configuration',
					'options'  => array(
						'post_date' 	=> esc_html__( 'Date', 'divi-ajax-search' ),
						'post_modified'	=> esc_html__( 'Modified Date', 'divi-ajax-search' ),
						'post_title'   	=> esc_html__( 'Title', 'divi-ajax-search' ),
						'post_name'     => esc_html__( 'Slug', 'divi-ajax-search' ),
						'ID'       		=> esc_html__( 'ID', 'divi-ajax-search' ),
						'rand'     		=> esc_html__( 'Random', 'divi-ajax-search' ),
					),
					'default'         => 'post_date',
					'tab_slug'        => 'general',
					'toggle_slug'     => 'main_content',
					'description'     => esc_html__( 'Here you can choose the order type of your results.', 'divi-ajax-search' ),
				),
				'order' => array(
					'label'            => esc_html__( 'Order', 'divi-ajax-search' ),
					'type'             => 'select',
					'option_category'  => 'configuration',
					'options'          => array(
						'DESC' => esc_html__( 'DESC', 'divi-ajax-search' ),
						'ASC'  => esc_html__( 'ASC', 'divi-ajax-search' ),
					),
					'default'          => 'DESC',
					'show_if_not'      => array(
						'orderby' => 'rand',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'main_content',
					'description'      => esc_html__( 'Here you can choose the order of your results.', 'divi-ajax-search' ),
				),
				'no_result_text' => array(
					'label'           => esc_html__( 'No Result Text', 'divi-ajax-search' ),
					'type'            => 'text',
					'option_category' => 'basic_option',
					'default'         => esc_html__( 'No results found', 'divi-ajax-search' ),
					'tab_slug'        => 'general',
					'toggle_slug'     => 'main_content',
					'description'     => esc_html__( 'Here you can input the custom text to be displayed when no results found.', 'divi-ajax-search' ),
				),
				'search_in' => array(
					'label'            		=> esc_html__( 'Search in', 'divi-ajax-search' ),
					'type'             		=> 'multiple_checkboxes',
					'option_category'  		=> 'basic_option',
					'options'				=> $search_in,
					'default'				=> 'on|on|on|off|off|off',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'search_area',
					'description'      		=> esc_html__( 'Here you can choose where you would like to search in.', 'divi-ajax-search' ),
				),
				'include_post_types' => array(
					'label'            		=> esc_html__( 'Post Types', 'divi-ajax-search' ),
					'type'             		=> 'select',
					'option_category'  		=> 'basic_option',
					'options'				=> $post_types,
					'default'				=> 'post',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'search_area',
					'description'      		=> esc_html__( 'Here you can choose which post types you would like to search in.', 'divi-ajax-search' ),
				),
				'exclude_post_types' => array(
					'label'            		=> esc_html__( 'Exclude Post Types', 'divi-ajax-search' ),
					'type'           		=> 'text',
					'option_category'  		=> 'basic_option',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'search_area',
					'description'      		=> esc_html__( 'Here you can enter post types slug you want to exclude from search separated by commas.', 'divi-ajax-search' ),
				),
				'include_taxonomies' => array(
					'label'            		=> esc_html__( 'Include Taxonomies', 'divi-ajax-search' ),
					'type'           		=> 'text',
					'option_category'  		=> 'basic_option',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'search_area',
					'description'      		=> esc_html__( 'Here you can enter taxonomies slug you want to include in search separated by commas.', 'divi-ajax-search' ),
				),
				'exclude_taxonomies' => array(
					'label'            		=> esc_html__( 'Exclude Taxonomies', 'divi-ajax-search' ),
					'type'           		=> 'text',
					'option_category'  		=> 'basic_option',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'search_area',
					'description'      		=> esc_html__( 'Here you can enter taxonomies slug you want to exclude from search separated by commas.', 'divi-ajax-search' ),
				),
				'exclude_post_ids' => array(
					'label'            		=> esc_html__( 'Exclude Post IDs', 'divi-ajax-search' ),
					'type'           		=> 'text',
					'option_category'  		=> 'basic_option',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'search_area',
					'description'      		=> esc_html__( 'Here you can enter post ids you want to exclude from search separated by commas.', 'divi-ajax-search' ),
				),

				'layout' => array(
					'label' 			=> esc_html__( 'Layout', 'divi-ajax-search' ),
					'type'              => 'select',
					'option_category'   => 'configuration',
					'options'           => array(
						'layout1'	=> esc_html__( 'Layout 1', 'divi-ajax-search' ),
						'layout2'	=> esc_html__( 'Layout 2', 'divi-ajax-search' ),
						'layout3'	=> esc_html__( 'Layout 3', 'divi-ajax-search' ),
					),
					'default'			=> 'layout1',
					'default_on_front'	=> 'layout1',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'display',
					'description'       => esc_html__( 'Here you can select the layout to display the search result.', 'divi-ajax-search' ),
				),
				'show_search_icon' => array(
					'label'            		=> esc_html__( 'Show Search Icon', 'divi-ajax-search' ),
					'type'             		=> 'yes_no_button',
					'option_category'  		=> 'configuration',
					'options'          		=> array(
						'on'  => esc_html__( 'Yes', 'divi-ajax-search' ),
						'off' => esc_html__( 'No', 'divi-ajax-search' ),
					),
					'default'          		=> 'on',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'display',
					'description'      		=> esc_html__( 'This will turn the search icon on and off.', 'divi-ajax-search' ),
				),
				'show_clear_icon' => array(
					'label'            		=> esc_html__( 'Show Clear Icon', 'divi-ajax-search' ),
					'type'             		=> 'yes_no_button',
					'option_category'  		=> 'configuration',
					'options'          		=> array(
						'on'  => esc_html__( 'Yes', 'divi-ajax-search' ),
						'off' => esc_html__( 'No', 'divi-ajax-search' ),
					),
					'default'          		=> 'on',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'display',
					'description'      		=> esc_html__( 'This will turn the clear/close icon on and off, when text field has text or not.', 'divi-ajax-search' ),
				),
				'display_fields' => array(
					'label'            		=> esc_html__( 'Display Fields', 'divi-ajax-search' ),
					'type'             		=> 'multiple_checkboxes',
					'option_category'  		=> 'basic_option',
					'options'				=> $display_fields,
					'default'				=> 'on|on|on|off',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'display',
					'description'      		=> esc_html__( 'Here you can choose which fields you would like to display in search results.', 'divi-ajax-search' ),
				),
				'excerpt_length' => array(
					'label'             => esc_html__( 'Excerpt Length', 'divi-plus' ),
					'type'              => 'range',
					'option_category'  	=> 'layout',
					'range_settings'    => array(
						'min'   => '0',
						'max'   => '500',
						'step'  => '1',
					),
					'unitless'			=> true,
					'mobile_options'    => false,
					'default'			=> '',
					'default_on_front'	=> '',
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display',
					'description'       => esc_html__('The length of the post excerpt/content to display, select to the zero if you want to remove the limit.', 'divi-plus'),
				),
				'show_result_link' => array(
					'label'            		=> esc_html__( 'Enable See All Result Link', 'divi-ajax-search' ),
					'type'             		=> 'yes_no_button',
					'option_category'  		=> 'configuration',
					'options'          		=> array(
						'on'  => esc_html__( 'Yes', 'divi-ajax-search' ),
						'off' => esc_html__( 'No', 'divi-ajax-search' ),
					),
					'default'          		=> 'off',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'display',
					'description'      		=> esc_html__( 'Enable this to display the search result page link.', 'divi-ajax-search' ),
				),
				'result_link_text' => array(
					'label'           		=> esc_html__( 'See All Result Link Text', 'divi-ajax-search' ),
					'type'           		=> 'text',
					'option_category' 		=> 'basic_option',
					'default_on_front' 		=> esc_html__( 'See all results', 'divi-ajax-search' ),
					'default'		   		=> esc_html__( 'See all results', 'divi-ajax-search' ),
					'show_if'               => array(
						'show_result_link' => 'on',
					),
					'tab_slug'        		=> 'general',
					'toggle_slug'     		=> 'display',
					'description'     		=> esc_html__( 'Here you can input the placeholder to be used for the search field.', 'divi-ajax-search' ),
				),
				'number_of_columns' => array(
					'label'             => esc_html__( 'Number Of Columns', 'divi-ajax-search' ),
					'type'              => 'select',
					'option_category'   => 'configuration',
					'options'           => array(
						'1'   => esc_html( '1' ),
						'2'   => esc_html( '2' ),
						'3'   => esc_html( '3' ),
						'4'   => esc_html( '4' ),
						'5'   => esc_html( '5' ),
						'6'   => esc_html( '6' ),
						'7'   => esc_html( '7' ),
						'8'   => esc_html( '8' ),
						'9'   => esc_html( '9' ),
						'10'  => esc_html( '10' ),
					),
					'default'           => '1',
					'mobile_options'	=> true,
					'tab_slug'          => 'general',
					'toggle_slug'       => 'display',
					'description'       => esc_html__( 'Here you can select the number of columns to display result items.', 'divi-ajax-search' ),
				),
				'column_spacing' => array(
					'label'             => esc_html__( 'Column Spacing', 'divi-ajax-search' ),
					'type'              => 'range',
					'option_category'  	=> 'layout',
					'range_settings'    => array(
						'min'   => '0',
						'max'   => '100',
						'step'  => '1',
					),
					'fixed_unit'		=> 'px',
					'fixed_range'       => true,
					'validate_unit'		=> true,
					'mobile_options'    => true,
					'default'           => '10px',
					'tab_slug'        	=> 'general',
					'toggle_slug'     	=> 'display',
					'description'       => esc_html__( 'Increase or decrease spacing between columns.', 'divi-ajax-search' ),
				),
				'use_masonry' => array(
					'label'            		=> esc_html__( 'Use Masonry', 'divi-ajax-search' ),
					'type'             		=> 'yes_no_button',
					'option_category'  		=> 'configuration',
					'options'          		=> array(
						'on'  => esc_html__( 'Yes', 'divi-ajax-search' ),
						'off' => esc_html__( 'No', 'divi-ajax-search' ),
					),
					'default'          		=> 'off',
					'tab_slug'         		=> 'general',
					'toggle_slug'      		=> 'display',
					'description'      		=> esc_html__( 'Here you can select whether or not to display the results in masonry design appearance.', 'divi-ajax-search' ),
				),

				'show_pagination' => array(
					'label'            => esc_html__( 'Show Pagination', 'divi-ajax-search' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'No', 'divi-ajax-search' ),
						'on'  => esc_html__( 'Yes', 'divi-ajax-search' ),
					),
					'default'          => 'off',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'pagination',
					'description'      => esc_html__( 'Show Pagination or not.', 'divi-ajax-search' ),
				),
				'show_prev_next' => array(
					'label'            => esc_html__( 'Show Previous Next Links', 'divi-ajax-search' ),
					'type'             => 'yes_no_button',
					'option_category'  => 'configuration',
					'options'          => array(
						'off' => esc_html__( 'No', 'divi-ajax-search' ),
						'on'  => esc_html__( 'Yes', 'divi-ajax-search' ),
					),
					'show_if'      => array(
						'show_pagination' => 'on',
					),
					'default'          => 'off',
					'tab_slug'         => 'general',
					'toggle_slug'      => 'pagination',
					'description'      => esc_html__( 'Show Previous Next Links or not.', 'divi-ajax-search' ),
				),
				'next_text' => array(
					'label'            => esc_html__( 'Next Link Text', 'divi-ajax-search' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'		   => esc_html__( 'Next', 'divi-ajax-search' ),
					'show_if'      => array(
						'show_pagination' => 'on',
						'show_prev_next'  => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'pagination',
					'description'      => esc_html__( 'Here you can define Next Link text in numbered pagination.', 'divi-ajax-search' ),
				),
				'prev_text' => array(
					'label'            => esc_html__( 'Prev Link Text', 'divi-ajax-search' ),
					'type'             => 'text',
					'option_category'  => 'configuration',
					'default'		   => esc_html__( 'Prev', 'divi-ajax-search' ),
					'show_if'      => array(
						'show_pagination' => 'on',
						'show_prev_next'  => 'on',
					),
					'tab_slug'         => 'general',
					'toggle_slug'      => 'pagination',
					'description'      => esc_html__( 'Here you can define Previous Link text in numbered pagination.', 'divi-ajax-search' ),
				),

				'scrollbar' => array(
					'label'             => esc_html__( 'Scrollbar', 'divi-ajax-search' ),
					'type'              => 'select',
					'option_category'   => 'configuration',
					'options'           => array(
						'default'  	=> esc_html__( 'Show', 'divi-ajax-search' ),
						'hide' 		=> esc_html__( 'Hide', 'divi-ajax-search' ),
					),
					'default'           => 'default',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'scrollbar',
					'description'       => esc_html__( 'Here you can select whether to show or hide scrollbar. This is totally depends on the browser, we can not guarantee the result of it.', 'divi-ajax-search' ),
				),
				'url_new_window' => array(
					'label'             => esc_html__( 'Result Item Link Target', 'divi-ajax-search' ),
					'type'              => 'select',
					'option_category'   => 'configuration',
					'options'           => array(
						'off' => esc_html__( 'In The Same Window', 'divi-ajax-search' ),
						'on'  => esc_html__( 'In The New Tab', 'divi-ajax-search' ),
					),
					'default'           => 'off',
					'tab_slug'          => 'general',
					'toggle_slug'       => 'link',
					'description'       => esc_html__( 'Here you can choose whether or not the search result item opens the link in a new window.', 'divi-ajax-search' ),
				),

				'search_result_item_content_bg' => array(
					'label'            => esc_html__( 'Item Content Background', 'divi-ajax-search' ),
					'type'             => 'color-alpha',
					'default'          => '',
					'mobile_options'   => true,
					'hover'            => 'tabs',
					'tab_slug'         => 'advanced',
					'toggle_slug'      => 'search_result_item',
					'description'      => esc_html__( 'Here you can define a custom color to be used for the search icon.', 'divi-ajax-search' ),
				),
				'search_result_item_content_custom_padding' => array(
					'label'                 => esc_html__( 'Item Content Padding', 'divi-ajax-search' ),
					'type'                  => 'custom_padding',
					'option_category'       => 'layout',
					'mobile_options'        => true,
					'hover'                 => false,
					'default'			    => '10px|10px|10px|10px|true|true',
					'tab_slug'              => 'advanced',
					'toggle_slug'           => 'search_result_item',
					'description'           => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-ajax-search' ),
				),
				'search_result_box_custom_padding' => array(
					'label'                 => esc_html__( 'Search Result Box Padding', 'divi-ajax-search' ),
					'type'                  => 'custom_padding',
					'option_category'       => 'layout',
					'mobile_options'        => true,
					'hover'                 => false,
					'tab_slug'              => 'advanced',
					'toggle_slug'           => 'margin_padding',
					'description'           => esc_html__( 'Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.', 'divi-ajax-search' ),
				),
				'search_result_box_bg_color' => array(
					'label'                 => esc_html__( 'Search Result Box Background', 'divi-ajax-search' ),
					'type'                  => 'background-field',
					'base_name'             => 'search_result_box_bg',
					'context'               => 'search_result_box_bg_color',
					'option_category'       => 'button',
					'custom_color'          => true,
					'background_fields'     => $this->generate_background_options( 'search_result_box_bg', 'button', 'advanced', 'background', 'search_result_box_bg_color' ),
					'hover'                 => false,
					'mobile_options'        => true,
					'tab_slug'              => 'general',
					'toggle_slug'           => 'background',
					'description'           => esc_html__( 'Customize the background style of the search result box by adjusting the background color, gradient, and image.', 'divi-ajax-search' ),
				),
				'search_result_item_bg_color' => array(
					'label'                 => esc_html__( 'Search Result Item Background', 'divi-ajax-search' ),
					'type'                  => 'background-field',
					'base_name'             => 'search_result_item_bg',
					'context'               => 'search_result_item_bg_color',
					'option_category'       => 'button',
					'custom_color'          => true,
					'background_fields'     => $this->generate_background_options( 'search_result_item_bg', 'button', 'advanced', 'background', 'search_result_item_bg_color' ),
					'hover'                 => 'tabs',
					'mobile_options'        => true,
					'tab_slug'              => 'general',
					'toggle_slug'           => 'background',
					'description'           => esc_html__( 'Customize the background style of the search result item by adjusting the background color, gradient, and image.', 'divi-ajax-search' ),
				),
				'search_icon_font_size' => array(
					'label'                 => esc_html__( 'Search Icon Font Size', 'divi-ajax-search' ),
					'type'                  => 'range',
					'option_category'       => 'font_option',
					'range_settings'        => array(
						'min'   => '1',
						'max'   => '120',
						'step'  => '1',
					),
					'fixed_unit'			=> 'px',
					'validate_unit'			=> true,
					'show_if'               => array(
						'show_search_icon' => 'on',
					),
					'mobile_options'        => true,
					'default'               => '16px',
					'tab_slug'              => 'advanced',
					'toggle_slug'           => 'search_icon_settings',
					'description'           => esc_html__('Increase or decrease the icon size.', 'divi-ajax-search'),
				),
				'search_icon_color' => array(
					'label'            		=> esc_html__( 'Search Icon Color', 'divi-ajax-search' ),
					'type'             		=> 'color-alpha',
					'default'          		=> '#000',
					'show_if'          		=> array(
						'show_search_icon' => 'on',
					),
					'mobile_options'        => true,
					'hover'					=> 'tabs',
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'search_icon_settings',
					'description'      		=> esc_html__( 'Here you can define a custom color to be used for the search icon.', 'divi-ajax-search' ),
				),
				'clear_icon_font_size' => array(
					'label'                 => esc_html__( 'Clear Icon Font Size', 'divi-ajax-search' ),
					'type'                  => 'range',
					'option_category'       => 'font_option',
					'range_settings'        => array(
						'min'   => '1',
						'max'   => '120',
						'step'  => '1',
					),
					'fixed_unit'			=> 'px',
					'validate_unit'			=> true,
					'show_if'               => array(
						'show_clear_icon' => 'on',
					),
					'mobile_options'        => true,
					'default'               => '20px',
					'tab_slug'              => 'advanced',
					'toggle_slug'           => 'clear_icon_settings',
					'description'           => esc_html__('Increase or decrease the icon size.', 'divi-ajax-search'),
				),
				'clear_icon_color' => array(
					'label'            		=> esc_html__( 'Clear Icon Color', 'divi-ajax-search' ),
					'type'             		=> 'color-alpha',
					'default'          		=> '#000',
					'show_if'          		=> array(
						'show_clear_icon' => 'on',
					),
					'mobile_options'        => true,
					'hover'					=> 'tabs',
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'clear_icon_settings',
					'description'      		=> esc_html__( 'Here you can define a custom color to be used for the search icon.', 'divi-ajax-search' ),
				),
				'loader_size' => array(
					'label'                 => esc_html__( 'Loader Size', 'divi-ajax-search' ),
					'type'                  => 'range',
					'option_category'       => 'font_option',
					'range_settings'        => array(
						'min'   => '1',
						'max'   => '120',
						'step'  => '1',
					),
					'fixed_unit'			=> 'px',
					'validate_unit'			=> true,
					'mobile_options'        => true,
					'default'               => '16px',
					'tab_slug'              => 'advanced',
					'toggle_slug'           => 'loader_settings',
					'description'           => esc_html__('Increase or decrease the loader size.', 'divi-ajax-search'),
				),
				'loader_color' => array(
					'label'            		=> esc_html__( 'Loader Color', 'divi-ajax-search' ),
					'type'             		=> 'color-alpha',
					'default'          		=> '#000',
					'mobile_options'        => true,
					'hover'					=> 'tabs',
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'loader_settings',
					'description'      		=> esc_html__( 'Here you can define a custom color to be used for the loader.', 'divi-ajax-search' ),
				),
				'see_all_result_bg_color' => array(
					'label'            		=> esc_html__( 'Background Color', 'divi-ajax-search' ),
					'type'             		=> 'color-alpha',
					'default'          		=> '#f9f9f9',
					'show_if'          		=> array(
						'show_result_link' => 'on',
					),
					'mobile_options'        => false,
					'tab_slug'         		=> 'advanced',
					'toggle_slug'      		=> 'show_result_link',
					'description'      		=> esc_html__( 'Here you can define a custom color to be used for the search icon.', 'divi-ajax-search' ),
				),

				'pagination_background_color' => array(
					'label'        => esc_html__( 'Pagination Container Background Color', 'divi-ajax-search' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'default'	   => '',
					'hover'		   => 'tabs',
					'show_if'      => array(
						'show_pagination' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'pagination',
					'description'  => esc_html__( 'Here you can define a custom background color for the pagination container.', 'divi-ajax-search' ),
				),
				'pagination_link_background_color' => array(
					'label'        => esc_html__( 'Pagination Link Background Color', 'divi-ajax-search' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'default'	   => '#f4f4f4',
					'hover'		   => 'tabs',
					'show_if'      => array(
						'show_pagination' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'pagination',
					'description'  => esc_html__( 'Here you can define a custom background color for the pagination link.', 'divi-ajax-search' ),
				),
				'pagination_link_color' => array(
					'label'        => esc_html__( 'Pagination Link Color', 'divi-ajax-search' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'hover'		   => 'tabs',
					'default'	   => '#000',
					'show_if'      => array(
						'show_pagination' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'pagination',
					'description'  => esc_html__( 'Here you can define a custom color for the pagination link.', 'divi-ajax-search' ),
				),
				'active_pagination_link_background_color' => array(
					'label'        => esc_html__( 'Active Pagination Link Background Color', 'divi-ajax-search' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'default'	   => '#000',
					'hover'		   => 'tabs',
					'show_if'      => array(
						'show_pagination' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'pagination',
					'description'  => esc_html__( 'Here you can define a custom background color for the active pagination link.', 'divi-ajax-search' ),
				),
				'active_pagination_link_color' => array(
					'label'        => esc_html__( 'Active Pagination Link Color', 'divi-ajax-search' ),
					'type'         => 'color-alpha',
					'custom_color' => true,
					'default'	   => '#fff',
					'hover'		   => 'tabs',
					'show_if'      => array(
						'show_pagination' => 'on',
					),
					'tab_slug'     => 'advanced',
					'toggle_slug'  => 'pagination',
					'description'  => esc_html__( 'Here you can define a custom color for the active pagination link.', 'divi-ajax-search' ),
				),
			),
			$this->generate_background_options( 'search_result_box_bg', 'skip', 'general', 'background', 'search_result_box_bg_color' ),
			$this->generate_background_options( 'search_result_item_bg', 'skip', 'general', 'background', 'search_result_item_bg_color' )
		);
	}

	public function render( $attrs, $content, $render_slug ) {
		$search_placeholder	= $this->props['search_placeholder'];
		$orderby			= $this->props['orderby'];
		$order 				= $this->props['order'];
		$search_in 			= $this->props['search_in'];
		$include_post_types = $this->props['include_post_types'];
		$display_fields		= $this->props['display_fields'];
		$excerpt_length		= $this->props['excerpt_length'];
		$number_of_columns	= $this->props['number_of_columns'];
		$use_masonry		= $this->props['use_masonry'];
		$scrollbar 			= $this->props['scrollbar'];
		$show_search_icon	= $this->props['show_search_icon'];
		$show_clear_icon	= $this->props['show_clear_icon'];
		$url_new_window		= $this->props['url_new_window'];
		$number_of_results	= $this->props['number_of_results'];
		$no_result_text		= $this->props['no_result_text'];
		$exclude_post_ids	= $this->props['exclude_post_ids'];
		$exclude_post_types = $this->props['exclude_post_types'];
		$exclude_taxonomies = $this->props['exclude_taxonomies'];
		$include_taxonomies = $this->props['include_taxonomies'];
		$layout             = ! empty( $this->props['layout'] ) ? $this->props['layout'] : 'layout1';

		if ( 'all' === $include_post_types ) {
			$raw_post_types = get_post_types( array(
				'public'              => true,
				'show_ui'             => true,
				'exclude_from_search' => false,
			), 'objects' );

			$blocklist = array( 'et_pb_layout', 'layout', 'attachment' );
			$whitelisted_post_types = array();

			foreach ( $raw_post_types as $post_type ) {
				$is_blocklisted = in_array( $post_type->name, $blocklist );
				if ( ! $is_blocklisted && post_type_exists( $post_type->name ) ) {
					array_push( $whitelisted_post_types, $post_type->name );
				}
			}

			$include_post_types = implode( ',', $whitelisted_post_types );
		}

		$whitelisted_search_in 	= array( 'post_title', 'post_content', 'post_excerpt', 'taxonomies', 'product_attributes', 'sku' );
		$search_in = $this->process_multiple_checkboxes_value( $search_in, $whitelisted_search_in );

		$whitelisted_display_fields = array( 'title', 'excerpt', 'featured_image', 'product_price' );
		$display_fields = $this->process_multiple_checkboxes_value( $display_fields, $whitelisted_display_fields );

		$search_placeholder = '' === $search_placeholder ? esc_html__( 'Search', 'divi-ajax-search' ) : esc_attr( $search_placeholder );
		
		if ( 'on' === $use_masonry ) {
			wp_enqueue_script( 'elicus-isotope-script', ELICUS_DIVI_AJAX_SEARCH_PATH . 'scripts/isotope.pkgd.min.js', array( 'jquery' ), '3.0.6', true );
		}

		$wrap_class = array( 'el_ajax_search_field_wrap' );
		if ( 'on' === $show_search_icon ) {
			$wrap_class[] = esc_attr( 'el_ajax_search_icon' );
		}

		// close icon
		$clear_icon = '';
		if ( 'on' === $show_clear_icon ) {
			$clear_icon = sprintf( '<span class="el_ajax_clear_icon">M</span>' );
		}

		$search_result_page_link = '';
		$show_result_link = $this->props['show_result_link'];
		if ( 'on' === $show_result_link ) {
			$link = add_query_arg( array(
				'elajaxsearch'       => 'yes',
				'post_type'          => esc_attr( $include_post_types ),
				'search_in'          => esc_attr( $search_in ),
				'orderby'            => esc_attr( $orderby ),
				'order'              => esc_attr( $order ),
				'exclude_post_types' => esc_attr( $exclude_post_types ),
				'exclude_post_ids'   => esc_attr( $exclude_post_ids ),
				'exclude_taxonomies' => esc_attr( $exclude_taxonomies ),
				'include_taxonomies' => esc_attr( $include_taxonomies ),
			), home_url( '/' ) );
			$link = wp_nonce_url( $link, 'elicus-divi-ajax-search-nonce' );

			$link_text = ! empty( $this->props['result_link_text'] ) ? $this->props['result_link_text'] : esc_html__( 'See all results', 'divi-ajax-search' );
			$search_result_page_link = sprintf(
				'<div class="el_ajax_search_result_link_wrap"><p class="el_ajax_search_result_link_inner">
					<a class="el_ajax_search_result_link" href="%1$s">%2$s</a>
				</p></div>',
				esc_url( $link ),
				esc_html( $link_text )
			);

			$wrap_class[] = esc_attr( 'el_search_result_link_active' );
		}

		// Pagination
		$show_pagination = $this->props['show_pagination'];

		$pagination_wrap = '';
		if ( 'on' === $show_pagination ) {

			// enqueue script
			wp_enqueue_script( 'elicus-twbs-pagination-script', ELICUS_DIVI_AJAX_SEARCH_PATH . 'scripts/twbsPagination.min.js', array( 'jquery' ), '1.4.2', true );

			$data_props = array( 'show_prev_next' );
			if ( 'on' === $this->props['show_prev_next'] ) {
				array_push( $data_props, 'prev_text', 'next_text' );
			}

			$data_atts = $this->props_to_html_data_attrs( $data_props );
			$pagination_wrap = sprintf(
				'<div class="el_ajax_search_pagination_wrap">
					<ul class="el_ajax_search_pagination" data-current-page="1" %1$s style="display:none;"></ul>
				</div>',
				$data_atts
			);
		}

		$search_field_wrap  = sprintf(
			'<div class="el_ajax_search_wrap">
				<div class="%6$s">
					<input type="search" placeholder="%1$s" class="el_ajax_search_field" 
						data-search-in="%2$s" data-search-post-type="%3$s" data-display-fields="%4$s"
						data-url-new-window="%7$s" data-number-of-results="%8$s" data-no-result-text="%9$s"
						data-orderby="%10$s" data-order="%11$s" data-use-masonry="%12$s" 
						data-exclude-post-types="%14$s" data-exclude-post-ids="%15$s" 
						data-exclude-taxonomies="%16$s" data-include-taxonomies="%17$s"
						data-excerpt-length="%19$s" data-result-layout="%21$s"
					/>
					<input type="hidden" class="el_divi_ajax_search_nonce" value="%13$s" />
					%18$s
				</div>
				%22$s
				<div class="el_ajax_search_results_wrap el-result-layout-%21$s %5$s">%20$s</div>
			</div>',
			esc_attr( $search_placeholder ),
			esc_attr( $search_in ),
			esc_attr( $include_post_types ),
			esc_attr( $display_fields ),
			'featured_image' === $display_fields ? esc_attr( 'el_ajax_search_only_featured_image' ) : '',
			implode( ' ', $wrap_class ),
			esc_attr( $url_new_window ),
			intval( $number_of_results ),
			esc_attr( $no_result_text ),
			esc_attr( $orderby ),
			esc_attr( $order ),
			'on' === $use_masonry ? 'on' : 'off',
			wp_create_nonce( 'elicus-divi-ajax-search-nonce' ),
			esc_attr( $exclude_post_types ),
			esc_attr( $exclude_post_ids ),
			esc_attr( $exclude_taxonomies ),
			esc_attr( $include_taxonomies ),
			et_core_intentionally_unescaped( $clear_icon, 'html' ),
			esc_attr( $excerpt_length ),
			et_core_intentionally_unescaped( $search_result_page_link, 'html' ),
			esc_attr( $layout ),
			et_core_intentionally_unescaped( $pagination_wrap, 'html' )
		);

		/* Search Icon CSS */
		if ( 'on' === $show_search_icon ) {
			$search_icon_font_size 	= et_pb_responsive_options()->get_property_values( $this->props, 'search_icon_font_size' );
			$search_icon_color     	= et_pb_responsive_options()->get_property_values( $this->props, 'search_icon_color' );
			et_pb_responsive_options()->generate_responsive_css( $search_icon_font_size, '%%order_class%% .el_ajax_search_icon:after', 'font-size', $render_slug, '', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $search_icon_color, '%%order_class%% .el_ajax_search_icon:after', 'color', $render_slug, '', 'color' );
			$search_icon_color_hover    = $this->get_hover_value( 'search_icon_color' );
			if ( $search_icon_color_hover ) {
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .el_ajax_search_icon:hover:after',
					'declaration' => sprintf( 'color: %1$s;', esc_attr( $search_icon_color_hover ) ),
				) );
			}

			// Set clear button spacing
			if ( 'on' === $show_clear_icon ) {
				foreach ( $search_icon_font_size as $device => $font_size ) {
					if ( absint($font_size) > 30 ) {

						// Get media query
						$media_query = ( 'desktop' === $device ) ? 'min_width_981' : (
							( 'tablet' === $device ) ? '768_980' : (
								( 'phone' === $device ) ? 'max_width_767' : ''
							)
						);

						self::set_style( $render_slug, array(
							'selector'    => '%%order_class%% .el_ajax_search_field_wrap .el_ajax_clear_icon',
							'declaration' => sprintf( 'right: %1$spx !important;', absint($font_size) + 10 ),
							'media_query' => self::get_media_query( $media_query ),
						) );
					}
				}
			}
		}

		// Item content background color
		$item_bg_color = et_pb_responsive_options()->get_property_values( $this->props, 'search_result_item_content_bg' );
		et_pb_responsive_options()->generate_responsive_css( $item_bg_color, '%%order_class%% .el_ajax_search_results .el_ajax_search_items .el_ajax_search_item_content', 'background-color', $render_slug, '!important;', 'color' );

		/* Clear Icon CSS */
		if ( 'on' === $show_clear_icon ) {
			$clear_icon_font_size 	= et_pb_responsive_options()->get_property_values( $this->props, 'clear_icon_font_size' );
			$clear_icon_color     	= et_pb_responsive_options()->get_property_values( $this->props, 'clear_icon_color' );
			et_pb_responsive_options()->generate_responsive_css( $clear_icon_font_size, '%%order_class%% .el_ajax_clear_icon', 'font-size', $render_slug, '', 'range' );
			et_pb_responsive_options()->generate_responsive_css( $clear_icon_color, '%%order_class%% .el_ajax_clear_icon', 'color', $render_slug, '', 'color' );
			$clear_icon_color_hover    = $this->get_hover_value( 'clear_icon_color' );
			if ( $search_icon_color_hover ) {
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .el_ajax_clear_icon:hover',
					'declaration' => sprintf(
						'color: %1$s;',
						esc_attr( $clear_icon_color_hover )
					),
				) );
			}
		}

		// Only if layout 1, else 100% width by default
		if ( 'layout1' === $layout ) {
			$featured_image_width = et_pb_responsive_options()->get_property_values( $this->props, 'featured_image_width' );
			$content_width = array_map( array( $this, 'das_filter_content_width' ), $featured_image_width );
			et_pb_responsive_options()->generate_responsive_css( $content_width, '%%order_class%% .el_ajax_search_item_image + .el_ajax_search_item_content', 'width', $render_slug, '', 'range' );
		}

		/* Number Of Columns CSS */
		$number_of_columns 	= et_pb_responsive_options()->get_property_values( $this->props, 'number_of_columns' );
		$column_spacing 	= et_pb_responsive_options()->get_property_values( $this->props, 'column_spacing' );

		if ( et_pb_responsive_options()->is_responsive_enabled( $this->props, 'number_of_columns' ) ) {
			$number_of_columns['tablet'] = '' !== $number_of_columns['tablet'] ? $number_of_columns['tablet'] : $number_of_columns['desktop'];
			$number_of_columns['phone']  = '' !== $number_of_columns['phone'] ? $number_of_columns['phone'] : $number_of_columns['tablet'];
		} else {
			$number_of_columns['tablet'] = 2;
			$number_of_columns['phone']  = 1;
		}

		$column_spacing['tablet'] = '' !== $column_spacing['tablet'] ? $column_spacing['tablet'] : $column_spacing['desktop'];
		$column_spacing['phone']  = '' !== $column_spacing['phone'] ? $column_spacing['phone'] : $column_spacing['tablet'];

		$breakpoints 	= array( 'desktop', 'tablet', 'phone' );
		$width 			= array();

		foreach ( $breakpoints as $breakpoint ) {
			if ( 1 === absint( $number_of_columns[$breakpoint] ) ) {
				$width[$breakpoint] = '100%';
			} else {
				$divided_width 	= 100 / absint( $number_of_columns[$breakpoint] );
				if ( 0.0 !== floatval( $column_spacing[$breakpoint] ) ) {
					$gutter = floatval( ( floatval( $column_spacing[$breakpoint] ) * ( absint( $number_of_columns[$breakpoint] ) - 1 ) ) / absint( $number_of_columns[$breakpoint] ) );
					$width[$breakpoint] = 'calc(' . $divided_width . '% - ' . $gutter . 'px)';
				} else {
					$width[$breakpoint] = $divided_width . '%';
				}
			}
		}

		et_pb_responsive_options()->generate_responsive_css( $width, '%%order_class%% .el_ajax_search_isotope_item', 'width', $render_slug, '', 'range' );
		et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .el_ajax_search_isotope_item', array( 'margin-bottom' ), $render_slug, '', 'range' );

		if ( 'on' === $use_masonry ) {
			et_pb_responsive_options()->generate_responsive_css( $column_spacing, '%%order_class%% .el_ajax_search_isotope_item_gutter', 'width', $render_slug, '', 'range' );
		} else {
			foreach ( $number_of_columns as $device => $cols ) {
				if ( 'desktop' === $device ) {
					self::set_style( $render_slug, array(
						'selector'    => '%%order_class%% .el_ajax_search_isotope_item:not(:nth-child(' . absint( $cols ) . 'n+' . absint( $cols ) . '))',
						'declaration' => sprintf( 'margin-right: %1$s;', esc_attr( $column_spacing['desktop'] ) ),
						'media_query' => self::get_media_query( 'min_width_981' ),
					) );
					if ( '' !== $cols ) {
						self::set_style( $render_slug, array(
							'selector'    => '%%order_class%% .el_ajax_search_isotope_item:nth-child(' . absint( $cols ) . 'n+1)',
							'declaration' => 'clear: left;',
							'media_query' => self::get_media_query( 'min_width_981' ),
						) );
					}
				} elseif ( 'tablet' === $device ) {
					self::set_style( $render_slug, array(
						'selector'    => '%%order_class%% .el_ajax_search_isotope_item:not(:nth-child(' . absint( $cols ) . 'n+' . absint( $cols ) . '))',
						'declaration' => sprintf( 'margin-right: %1$s;', esc_attr( $column_spacing['tablet'] ) ),
						'media_query' => self::get_media_query( '768_980' ),
					) );
					if ( '' !== $cols ) {
						self::set_style( $render_slug, array(
							'selector'    => '%%order_class%% .el_ajax_search_isotope_item:nth-child(' . absint( $cols ) . 'n+1)',
							'declaration' => 'clear: left;',
							'media_query' => self::get_media_query( '768_980' ),
						) );
					}
				} elseif ( 'phone' === $device ) {
					self::set_style( $render_slug, array(
						'selector'    => '%%order_class%% .el_ajax_search_isotope_item:not(:nth-child(' . absint( $cols ) . 'n+' . absint( $cols ) . '))',
						'declaration' => sprintf( 'margin-right: %1$s;', esc_attr( $column_spacing['phone'] ) ),
						'media_query' => self::get_media_query( 'max_width_767' ),
					) );
					if ( '' !== $cols ) {
						self::set_style( $render_slug, array(
							'selector'    => '%%order_class%% .el_ajax_search_isotope_item:nth-child(' . absint( $cols ) . 'n+1)',
							'declaration' => 'clear: left;',
							'media_query' => self::get_media_query( 'max_width_767' ),
						) );
					}
				}
			}
		}

		/* Loader CSS */
		$loader_size_property  = array( 'width', 'height' );
		$loader_color_property = array( 'border-top-color', 'border-bottom-color' );
		$loader_size 	= et_pb_responsive_options()->get_property_values( $this->props, 'loader_size' );
		$loader_color   = et_pb_responsive_options()->get_property_values( $this->props, 'loader_color' );
		et_pb_responsive_options()->generate_responsive_css( $loader_size, '%%order_class%% .el_ajax_search_loader:after', $loader_size_property, $render_slug, '', 'range' );
		et_pb_responsive_options()->generate_responsive_css( $loader_color, '%%order_class%% .el_ajax_search_loader:after', $loader_color_property, $render_slug, '', 'color' );

		$loader_color_hover    = $this->get_hover_value( 'loader_color' );
		if ( $loader_color_hover ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .el_ajax_search_loader:hover:after',
				'declaration' => sprintf(
					'border-top-color: %1$s;border-bottom-color: %1$s;',
					esc_attr( $loader_color_hover )
				),
			) );
		}

		if ( 'on' === $show_pagination ) {
			$this->generate_styles( array(
				'base_attr_name' => 'pagination_background_color',
				'selector'       => '%%order_class%% .el_ajax_search_pagination',
				'hover_selector' => '%%order_class%% .el_ajax_search_pagination',
				'css_property'   => 'background-color',
				'render_slug'    => $render_slug,
				'type'           => 'color',
			) );
			$this->generate_styles( array(
				'base_attr_name' => 'pagination_link_background_color',
				'selector'       => '%%order_class%% .el_ajax_search_pagination li a',
				'hover_selector' => '%%order_class%% .el_ajax_search_pagination li a:hover',
				'css_property'   => 'background-color',
				'render_slug'    => $render_slug,
				'type'           => 'color',
			) );
			$this->generate_styles( array(
				'base_attr_name' => 'pagination_link_color',
				'selector'       => '%%order_class%% .el_ajax_search_pagination li a',
				'hover_selector' => '%%order_class%% .el_ajax_search_pagination li a:hover',
				'css_property'   => 'color',
				'render_slug'    => $render_slug,
				'type'           => 'color',
			) );
			$this->generate_styles( array(
				'base_attr_name' => 'active_pagination_link_background_color',
				'selector'       => '%%order_class%% .el_ajax_search_pagination li.active a',
				'hover_selector' => '%%order_class%% .el_ajax_search_pagination li.active a:hover',
				'css_property'   => 'background-color',
				'render_slug'    => $render_slug,
				'type'           => 'color',
			) );
			$this->generate_styles( array(
				'base_attr_name' => 'active_pagination_link_color',
				'selector'       => '%%order_class%% .el_ajax_search_pagination li.active a',
				'hover_selector' => '%%order_class%% .el_ajax_search_pagination li.active a:hover',
				'css_property'   => 'color',
				'render_slug'    => $render_slug,
				'type'           => 'color',
			) );
		}

		// Show result link
		if ( isset( $this->props['show_result_link'] ) && 'on' === $this->props['show_result_link'] ) {
			// Background color
			if ( ! empty( $this->props['see_all_result_bg_color'] ) ) {
				self::set_style( $render_slug, array(
					'selector'    => '%%order_class%% .el_ajax_search_result_link_wrap .el_ajax_search_result_link_inner',
					'declaration' => sprintf( 'background-color: %1$s;', esc_attr( $this->props['see_all_result_bg_color'] ) ),
				) );
			}
		}

		/* Scrollbar CSS */
		if ( 'hide' === $scrollbar ) {
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .el_ajax_search_results::-webkit-scrollbar',
				'declaration' => 'display: none;',
			) );
			self::set_style( $render_slug, array(
				'selector'    => '%%order_class%% .el_ajax_search_results',
				'declaration' => 'scrollbar-width: none;',
			) );
		}

		$backgrounds = array(
			'search_result_box_bg' => array(
				'normal' => "{$this->main_css_element} .el_ajax_search_results",
				'hover'  => "{$this->main_css_element} .el_ajax_search_results:hover",
			),
			'search_result_item_bg' => array(
				'normal' => "{$this->main_css_element} .el_ajax_search_item",
				'hover'  => "{$this->main_css_element} .el_ajax_search_item:hover",
			),
		);
		
		if ( ET_BUILDER_PRODUCT_VERSION >= '4.16.0' ) {
			foreach ( $backgrounds as $basename => $css_element ) {
				$bg_args = array(
					'base_prop_name'                => $basename,
					'props'                         => $this->props,
					'important'                     => ' !important',
					'fields_definition'             => $this->fields_unprocessed,
					'selector'                      => $css_element['normal'],
					'selector_hover'                => $css_element['hover'],
					'function_name'                 => $render_slug,
					'has_background_color_toggle'   => false,
					'use_background_color'          => true,
					'use_background_image'          => true,
					'use_background_color_reset'    => true,
					'use_background_video'          => false,
					'use_background_pattern'        => false,
					'use_background_mask'           => false,
					'use_background_image_parallax' => false,
					'prop_name_aliases'             => array(
						"use_{$basename}_color_gradient" => "{$basename}_use_color_gradient",
						"{$basename}" => "{$basename}_color",
					),
				);

				// Process background style.
				et_pb_background_options()->get_background_style( $bg_args );
			}
		} else {
			$this->process_custom_background( $backgrounds, $render_slug );
		}

		$this->process_advanced_margin_padding_css( $this, $render_slug, $this->margin_padding );
		
		return $search_field_wrap;
	}

	public function das_filter_content_width( $width ) {
		if ( '' !== $width ) {
			return 'calc(100% - ' . $width . ')';
		}

		return $width;
	}

	public function das_builder_processed_range_value( $result, $range, $range_string ) {
		if ( false !== strpos( $result, '0calc' ) ) {
			return $range;
		}
		return $result;
	}

	public function process_multiple_checkboxes_value( $value, $values = array() ) {
		if ( empty( $values ) && ! is_array( $values ) ) {
			return '';
		}
		
		$new_values = array();
		$value      = explode( '|', $value );
		foreach( $value as $key => $val ) {
			if ( 'on' === strtolower( $val ) ) {
				array_push( $new_values, $values[$key] );
			}
		}
		return implode( ',', $new_values );
	}

	public function process_advanced_margin_padding_css( $module, $function_name, $margin_padding ) {
		$utils           = ET_Core_Data_Utils::instance();
		$all_values      = $module->props;
		$advanced_fields = $module->advanced_fields;

		// Disable if module doesn't set advanced_fields property and has no VB support.
		if ( ! $module->has_vb_support() && ! $module->has_advanced_fields ) {
			return;
		}

		$allowed_advanced_fields = array( 'search_result_margin_padding' );
		foreach ( $allowed_advanced_fields as $advanced_field ) {
			if ( ! empty( $advanced_fields[ $advanced_field ] ) ) {
				foreach ( $advanced_fields[ $advanced_field ] as $label => $form_field ) {
					$margin_key  = "{$label}_custom_margin";
					$padding_key = "{$label}_custom_padding";
					if ( '' !== $utils->array_get( $all_values, $margin_key, '' ) || '' !== $utils->array_get( $all_values, $padding_key, '' ) ) {
						$settings = $utils->array_get( $form_field, 'margin_padding', array() );
						// Ensure main selector exists.
						$form_field_margin_padding_css = $utils->array_get( $settings, 'css.main', '' );
						if ( empty( $form_field_margin_padding_css ) ) {
							$utils->array_set( $settings, 'css.main', $utils->array_get( $form_field, 'css.main', '' ) );
						}

						$margin_padding->update_styles( $module, $label, $settings, $function_name, $advanced_field );
					}
				}
			}
		}
	}

	public function process_custom_background( $backgrounds, $function_name ) {

		foreach ( $backgrounds as $basename => $css_element ) {

			// Place to store processed background. It will be compared with the smaller device
			// background processed value to avoid rendering the same styles.
			$processed_background_color = '';
			$processed_background_image = '';
			$processed_background_blend = '';

			// Store background images status because the process is extensive.
			$background_image_status = array(
				'desktop' => false,
				'tablet'  => false,
				'phone'   => false,
			);

			// Background Options Styling.
			foreach ( et_pb_responsive_options()->get_modes() as $device ) {
				$background_base_name = $basename;
				$background_prefix    = "{$basename}_";
				$background_style     = '';
				$is_desktop           = 'desktop' === $device;
				$suffix               = ! $is_desktop ? "_{$device}" : '';

				$background_color_style = '';
				$background_image_style = '';
				$background_images      = array();

				$has_background_color_gradient         = false;
				$has_background_image                  = false;
				$has_background_gradient_and_image     = false;
				$is_background_color_gradient_disabled = false;
				$is_background_image_disabled          = false;

				$background_color_gradient_overlays_image = 'off';

				// Ensure responsive is active.
				if ( ! $is_desktop && ! et_pb_responsive_options()->is_responsive_enabled( $this->props, "{$basename}_color" ) ) {
					continue;
				}

				// A. Background Gradient.
				$use_background_color_gradient = et_pb_responsive_options()->get_inheritance_background_value( $this->props, "{$background_prefix}use_color_gradient", $device, $background_base_name, $this->fields_unprocessed );

				if ( 'on' === $use_background_color_gradient ) {
					$background_color_gradient_overlays_image = et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_overlays_image{$suffix}", '', true );

					$gradient_properties = array(
						'type'             => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_type{$suffix}", '', true ),
						'direction'        => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_direction{$suffix}", '', true ),
						'radial_direction' => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_direction_radial{$suffix}", '', true ),
						'color_start'      => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_start{$suffix}", '', true ),
						'color_end'        => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_end{$suffix}", '', true ),
						'start_position'   => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_start_position{$suffix}", '', true ),
						'end_position'     => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_end_position{$suffix}", '', true ),
					);

					// Save background gradient into background images list.
					$background_images[] = $this->get_gradient( $gradient_properties );

					// Flag to inform BG Color if current module has Gradient.
					$has_background_color_gradient = true;
				} elseif ( 'off' === $use_background_color_gradient ) {
					$is_background_color_gradient_disabled = true;
				}

				// B. Background Image.
				$background_image = et_pb_responsive_options()->get_inheritance_background_value( $this->props, "{$background_prefix}image", $device, $background_base_name, $this->fields_unprocessed );
				$parallax         = et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}parallax{$suffix}", 'off' );

				// BG image and parallax status.
				$is_background_image_active         = '' !== $background_image && 'on' !== $parallax;
				$background_image_status[ $device ] = $is_background_image_active;

				if ( $is_background_image_active ) {
					// Flag to inform BG Color if current module has Image.
					$has_background_image = true;

					// Check previous BG image status. Needed to get the correct value.
					$is_prev_background_image_active = true;
					if ( ! $is_desktop ) {
						$is_prev_background_image_active = 'tablet' === $device ? $background_image_status['desktop'] : $background_image_status['tablet'];
					}

					// Size.
					$background_size_default = ET_Builder_Element::$_->array_get( $this->fields_unprocessed, "{$background_prefix}size.default", '' );
					$background_size         = et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}size{$suffix}", $background_size_default, ! $is_prev_background_image_active );

					if ( '' !== $background_size ) {
						$background_style .= sprintf(
							'background-size: %1$s; ',
							esc_html( $background_size )
						);
					}

					// Position.
					$background_position_default = ET_Builder_Element::$_->array_get( $this->fields_unprocessed, "{$background_prefix}position.default", '' );
					$background_position         = et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}position{$suffix}", $background_position_default, ! $is_prev_background_image_active );

					if ( '' !== $background_position ) {
						$background_style .= sprintf(
							'background-position: %1$s; ',
							esc_html( str_replace( '_', ' ', $background_position ) )
						);
					}

					// Repeat.
					$background_repeat_default = ET_Builder_Element::$_->array_get( $this->fields_unprocessed, "{$background_prefix}repeat.default", '' );
					$background_repeat         = et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}repeat{$suffix}", $background_repeat_default, ! $is_prev_background_image_active );

					if ( '' !== $background_repeat ) {
						$background_style .= sprintf(
							'background-repeat: %1$s; ',
							esc_html( $background_repeat )
						);
					}

					// Blend.
					$background_blend_default = ET_Builder_Element::$_->array_get( $this->fields_unprocessed, "{$background_prefix}blend.default", '' );
					$background_blend         = et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}blend{$suffix}", $background_blend_default, ! $is_prev_background_image_active );
					$background_blend_inherit = et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}blend{$suffix}", '', true );

					if ( '' !== $background_blend_inherit ) {
						// Don't print the same image blend style.
						if ( '' !== $background_blend ) {
							$background_style .= sprintf(
								'background-blend-mode: %1$s; ',
								esc_html( $background_blend )
							);
						}

						// Reset - If background has image and gradient, force background-color: initial.
						if ( $has_background_color_gradient && $has_background_image && $background_blend_inherit !== $background_blend_default ) {
							$has_background_gradient_and_image = true;
							$background_color_style            = 'initial';
							$background_style                 .= 'background-color: initial; ';
						}

						$processed_background_blend = $background_blend;
					}

					// Only append background image when the image is exist.
					$background_images[] = sprintf( 'url(%1$s)', esc_html( $background_image ) );
				} elseif ( '' === $background_image ) {
					// Reset - If background image is disabled, ensure we reset prev background blend mode.
					if ( '' !== $processed_background_blend ) {
						$background_style          .= 'background-blend-mode: normal; ';
						$processed_background_blend = '';
					}

					$is_background_image_disabled = true;
				}

				if ( ! empty( $background_images ) ) {
					// The browsers stack the images in the opposite order to what you'd expect.
					if ( 'on' !== $background_color_gradient_overlays_image ) {
						$background_images = array_reverse( $background_images );
					}

					// Set background image styles only it's different compared to the larger device.
					$background_image_style = join( ', ', $background_images );
					if ( $processed_background_image !== $background_image_style ) {
						$background_style .= sprintf(
							'background-image: %1$s !important;',
							esc_html( $background_image_style )
						);
					}
				} elseif ( ! $is_desktop && $is_background_color_gradient_disabled && $is_background_image_disabled ) {
					// Reset - If background image and gradient are disabled, reset current background image.
					$background_image_style = 'initial';
					$background_style      .= 'background-image: initial !important;';
				}

				// Save processed background images.
				$processed_background_image = $background_image_style;

				// C. Background Color.
				if ( ! $has_background_gradient_and_image ) {
					// Background color `initial` was added by default to reset button background
					// color when user disable it on mobile preview mode. However, it should
					// be applied only when the background color is really disabled because user
					// may use theme customizer to setup global button background color. We also
					// need to ensure user still able to disable background color on mobile.
					$background_color_enable  = ET_Builder_Element::$_->array_get( $this->props, "{$background_prefix}enable_color{$suffix}", '' );
					$background_color_initial = 'off' === $background_color_enable && ! $is_desktop ? 'initial' : '';

					$background_color       = et_pb_responsive_options()->get_inheritance_background_value( $this->props, "{$background_prefix}color", $device, $background_base_name, $this->fields_unprocessed );
					$background_color       = '' !== $background_color ? $background_color : $background_color_initial;
					$background_color_style = $background_color;

					if ( '' !== $background_color && $processed_background_color !== $background_color ) {
						$background_style .= sprintf(
							'background-color: %1$s; ',
							esc_html( $background_color )
						);
					}
				}

				// Save processed background color.
				$processed_background_color = $background_color_style;

				// Print background gradient and image styles.
				if ( '' !== $background_style ) {
					$background_style_attrs = array(
						'selector'    => $css_element['normal'],
						'declaration' => rtrim( $background_style ),
						'priority'    => $this->_style_priority,
					);

					// Add media query attribute to background style attrs.
					if ( 'desktop' !== $device ) {
						$current_media_query                   = 'tablet' === $device ? 'max_width_980' : 'max_width_767';
						$background_style_attrs['media_query'] = ET_Builder_Element::get_media_query( $current_media_query );
					}

					ET_Builder_Element::set_style( $function_name, $background_style_attrs );
				}
			}

			// Background Hover.
			if ( et_builder_is_hover_enabled( "{$basename}_color", $this->props ) ) {

				$background_base_name    = $basename;
				$background_prefix       = "{$basename}_";
				$background_images_hover = array();
				$background_hover_style  = '';

				$has_background_color_gradient_hover         = false;
				$has_background_image_hover                  = false;
				$has_background_gradient_and_image_hover     = false;
				$is_background_color_gradient_hover_disabled = false;
				$is_background_image_hover_disabled          = false;

				$background_color_gradient_overlays_image_desktop = et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_overlays_image", 'off', true );

				$gradient_properties_desktop = array(
					'type'             => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_type", '', true ),
					'direction'        => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_direction", '', true ),
					'radial_direction' => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_direction_radial", '', true ),
					'color_start'      => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_start", '', true ),
					'color_end'        => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_end", '', true ),
					'start_position'   => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_start_position", '', true ),
					'end_position'     => et_pb_responsive_options()->get_any_value( $this->props, "{$background_prefix}color_gradient_end_position", '', true ),
				);

				$background_color_gradient_overlays_image_hover = 'off';

				// Background Gradient Hover.
				// This part is little bit different compared to other hover implementation. In
				// this case, hover is enabled on the background field, not on the each of those
				// fields. So, built in function get_value() doesn't work in this case.
				// Temporarily, we need to fetch the the value from get_raw_value().
				$use_background_color_gradient_hover = et_pb_responsive_options()->get_inheritance_background_value( $this->props, "{$background_prefix}use_color_gradient", 'hover', $background_base_name, $this->fields_unprocessed );

				if ( 'on' === $use_background_color_gradient_hover ) {
					// Desktop value as default.
					$background_color_gradient_type_desktop             = ET_Builder_Element::$_->array_get( $gradient_properties_desktop, 'type', '' );
					$background_color_gradient_direction_desktop        = ET_Builder_Element::$_->array_get( $gradient_properties_desktop, 'direction', '' );
					$background_color_gradient_radial_direction_desktop = ET_Builder_Element::$_->array_get( $gradient_properties_desktop, 'radial_direction', '' );
					$background_color_gradient_color_start_desktop      = ET_Builder_Element::$_->array_get( $gradient_properties_desktop, 'color_start', '' );
					$background_color_gradient_color_end_desktop        = ET_Builder_Element::$_->array_get( $gradient_properties_desktop, 'color_end', '' );
					$background_color_gradient_start_position_desktop   = ET_Builder_Element::$_->array_get( $gradient_properties_desktop, 'start_position', '' );
					$background_color_gradient_end_position_desktop     = ET_Builder_Element::$_->array_get( $gradient_properties_desktop, 'end_position', '' );

					// Hover value.
					$background_color_gradient_type_hover             = et_pb_hover_options()->get_raw_value( "{$background_prefix}color_gradient_type", $this->props, $background_color_gradient_type_desktop );
					$background_color_gradient_direction_hover        = et_pb_hover_options()->get_raw_value( "{$background_prefix}color_gradient_direction", $this->props, $background_color_gradient_direction_desktop );
					$background_color_gradient_direction_radial_hover = et_pb_hover_options()->get_raw_value( "{$background_prefix}color_gradient_direction_radial", $this->props, $background_color_gradient_radial_direction_desktop );
					$background_color_gradient_start_hover            = et_pb_hover_options()->get_raw_value( "{$background_prefix}color_gradient_start", $this->props, $background_color_gradient_color_start_desktop );
					$background_color_gradient_end_hover              = et_pb_hover_options()->get_raw_value( "{$background_prefix}color_gradient_end", $this->props, $background_color_gradient_color_end_desktop );
					$background_color_gradient_start_position_hover   = et_pb_hover_options()->get_raw_value( "{$background_prefix}color_gradient_start_position", $this->props, $background_color_gradient_start_position_desktop );
					$background_color_gradient_end_position_hover     = et_pb_hover_options()->get_raw_value( "{$background_prefix}color_gradient_end_position", $this->props, $background_color_gradient_end_position_desktop );
					$background_color_gradient_overlays_image_hover   = et_pb_hover_options()->get_raw_value( "{$background_prefix}color_gradient_overlays_image", $this->props, $background_color_gradient_overlays_image_desktop );

					$has_background_color_gradient_hover = true;

					$gradient_values_hover = array(
						'type'             => '' !== $background_color_gradient_type_hover ? $background_color_gradient_type_hover : $background_color_gradient_type_desktop,
						'direction'        => '' !== $background_color_gradient_direction_hover ? $background_color_gradient_direction_hover : $background_color_gradient_direction_desktop,
						'radial_direction' => '' !== $background_color_gradient_direction_radial_hover ? $background_color_gradient_direction_radial_hover : $background_color_gradient_radial_direction_desktop,
						'color_start'      => '' !== $background_color_gradient_start_hover ? $background_color_gradient_start_hover : $background_color_gradient_color_start_desktop,
						'color_end'        => '' !== $background_color_gradient_end_hover ? $background_color_gradient_end_hover : $background_color_gradient_color_end_desktop,
						'start_position'   => '' !== $background_color_gradient_start_position_hover ? $background_color_gradient_start_position_hover : $background_color_gradient_start_position_desktop,
						'end_position'     => '' !== $background_color_gradient_end_position_hover ? $background_color_gradient_end_position_hover : $background_color_gradient_end_position_desktop,
					);

					$background_images_hover[] = $this->get_gradient( $gradient_values_hover );
				} elseif ( 'off' === $use_background_color_gradient_hover ) {
					$is_background_color_gradient_hover_disabled = true;
				}

				// Background Image Hover.
				// This part is little bit different compared to other hover implementation. In
				// this case, hover is enabled on the background field, not on the each of those
				// fields. So, built in function get_value() doesn't work in this case.
				// Temporarily, we need to fetch the the value from get_raw_value().
				$background_image_hover = et_pb_responsive_options()->get_inheritance_background_value( $this->props, "{$background_prefix}image", 'hover', $background_base_name, $this->fields_unprocessed );
				$parallax_hover         = et_pb_hover_options()->get_raw_value( "{$background_prefix}parallax", $this->props );

				if ( '' !== $background_image_hover && null !== $background_image_hover && 'on' !== $parallax_hover ) {
					// Flag to inform BG Color if current module has Image.
					$has_background_image_hover = true;

					// Size.
					$background_size_hover   = et_pb_hover_options()->get_raw_value( "{$background_prefix}size", $this->props );
					$background_size_desktop = ET_Builder_Element::$_->array_get( $this->props, "{$background_prefix}size", '' );
					$is_same_background_size = $background_size_hover === $background_size_desktop;
					if ( empty( $background_size_hover ) && ! empty( $background_size_desktop ) ) {
						$background_size_hover = $background_size_desktop;
					}

					if ( ! empty( $background_size_hover ) && ! $is_same_background_size ) {
						$background_hover_style .= sprintf(
							'background-size: %1$s; ',
							esc_html( $background_size_hover )
						);
					}

					// Position.
					$background_position_hover   = et_pb_hover_options()->get_raw_value( "{$background_prefix}position", $this->props );
					$background_position_desktop = ET_Builder_Element::$_->array_get( $this->props, "{$background_prefix}position", '' );
					$is_same_background_position = $background_position_hover === $background_position_desktop;
					if ( empty( $background_position_hover ) && ! empty( $background_position_desktop ) ) {
						$background_position_hover = $background_position_desktop;
					}

					if ( ! empty( $background_position_hover ) && ! $is_same_background_position ) {
						$background_hover_style .= sprintf(
							'background-position: %1$s; ',
							esc_html( str_replace( '_', ' ', $background_position_hover ) )
						);
					}

					// Repeat.
					$background_repeat_hover   = et_pb_hover_options()->get_raw_value( "{$background_prefix}repeat", $this->props );
					$background_repeat_desktop = ET_Builder_Element::$_->array_get( $this->props, "{$background_prefix}repeat", '' );
					$is_same_background_repeat = $background_repeat_hover === $background_repeat_desktop;
					if ( empty( $background_repeat_hover ) && ! empty( $background_repeat_desktop ) ) {
						$background_repeat_hover = $background_repeat_desktop;
					}

					if ( ! empty( $background_repeat_hover ) && ! $is_same_background_repeat ) {
						$background_hover_style .= sprintf(
							'background-repeat: %1$s; ',
							esc_html( $background_repeat_hover )
						);
					}

					// Blend.
					$background_blend_hover   = et_pb_hover_options()->get_raw_value( "{$background_prefix}blend", $this->props );
					$background_blend_default = ET_Builder_Element::$_->array_get( $this->fields_unprocessed, "{$background_prefix}blend.default", '' );
					$background_blend_desktop = ET_Builder_Element::$_->array_get( $this->props, "{$background_prefix}blend", '' );
					$is_same_background_blend = $background_blend_hover === $background_blend_desktop;
					if ( empty( $background_blend_hover ) && ! empty( $background_blend_desktop ) ) {
						$background_blend_hover = $background_blend_desktop;
					}

					if ( ! empty( $background_blend_hover ) ) {
						if ( ! $is_same_background_blend ) {
							$background_hover_style .= sprintf(
								'background-blend-mode: %1$s; ',
								esc_html( $background_blend_hover )
							);
						}

						// Force background-color: initial.
						if ( $has_background_color_gradient_hover && $has_background_image_hover && $background_blend_hover !== $background_blend_default ) {
							$has_background_gradient_and_image_hover = true;
							$background_hover_style                 .= 'background-color: initial !important;';
						}
					}

					// Only append background image when the image exists.
					$background_images_hover[] = sprintf( 'url(%1$s)', esc_html( $background_image_hover ) );
				} elseif ( '' === $background_image_hover ) {
					$is_background_image_hover_disabled = true;
				}

				if ( ! empty( $background_images_hover ) ) {
					// The browsers stack the images in the opposite order to what you'd expect.
					if ( 'on' !== $background_color_gradient_overlays_image_hover ) {
						$background_images_hover = array_reverse( $background_images_hover );
					}

					$background_hover_style .= sprintf(
						'background-image: %1$s !important;',
						esc_html( join( ', ', $background_images_hover ) )
					);
				} elseif ( $is_background_color_gradient_hover_disabled && $is_background_image_hover_disabled ) {
					$background_hover_style .= 'background-image: initial !important;';
				}

				// Background Color Hover.
				if ( ! $has_background_gradient_and_image_hover ) {
					$background_color_hover = et_pb_responsive_options()->get_inheritance_background_value( $this->props, "{$background_prefix}color", 'hover', $background_base_name, $this->fields_unprocessed );
					$background_color_hover = '' !== $background_color_hover ? $background_color_hover : 'transparent';

					if ( '' !== $background_color_hover ) {
						$background_hover_style .= sprintf(
							'background-color: %1$s !important; ',
							esc_html( $background_color_hover )
						);
					}
				}

				// Print background hover gradient and image styles.
				if ( '' !== $background_hover_style ) {
					$background_hover_style_attrs = array(
						'selector'    => $css_element['hover'],
						'declaration' => rtrim( $background_hover_style ),
						'priority'    => $this->_style_priority,
					);

					ET_Builder_Element::set_style( $function_name, $background_hover_style_attrs );
				}
			}
		}
	}
}

new EL_AjaxSearch;