<?php
class EL_DiviAjaxSearch extends DiviExtension {

    /**
     * The gettext domain for the extension's translations.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $gettext_domain = 'divi-ajax-search';

    /**
     * The extension's WP Plugin name.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $name = 'divi-ajax-search';

    /**
     * The extension's version
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $version = ELICUS_DIVI_AJAX_SEARCH_VERSION;

    /**
     * DAS_DiviAjaxSearch constructor.
     *
     * @param string $name
     * @param array  $args
     */
    public function __construct( $name = 'divi-ajax-search', $args = array() ) {
        $this->plugin_dir     = plugin_dir_path( __FILE__ );
        $this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );
        $this->_frontend_js_data    = array(
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'ajaxnonce' => wp_create_nonce( 'elicus-divi-ajax-search-nonce' ),
        );

        parent::__construct( $name, $args );

        $this->plugin_setup();

        add_action( 'wp_ajax_el_ajax_search_results', array( $this, 'el_ajax_search_results' ) );
        add_action( 'wp_ajax_nopriv_el_ajax_search_results', array( $this, 'el_ajax_search_results' ) );
        add_filter( 'nonce_user_logged_out', array( $this, 'el_ajax_search_update_nonce' ), 10, 2 );
        add_filter( 'et_pb_module_shortcode_attributes', array( $this, 'el_ajax_search_migrate_gradient' ), 10, 5 );

        add_filter( 'posts_request', array( $this, 'el_ajax_search_search_result_page_query' ), 10, 2 );
    }

    public function el_ajax_search_update_nonce( $uid , $action = -1  ) {
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && ! is_user_logged_in() && $action === 'elicus-divi-ajax-search-nonce' ) {
            return get_current_user_id();
        }
        return $uid;
    }

    /**
     * plugin setup function.
     *
     *@since 1.0.0
     */
    public function plugin_setup() {
        require_once plugin_dir_path( __FILE__ ) . 'functions.php';
    }

    public function el_ajax_search_exists_and_is_not_empty( $key, $array ) {
        if ( ! array_key_exists( $key, $array ) ) {
            return false;
        }

        return ! empty( $array[ $key ] );
    }

    public function el_ajax_search_migrate_gradient( $props, $attrs, $render_slug, $_address, $content ) {
        if ( 'el_ajax_search' === $render_slug && ET_BUILDER_PRODUCT_VERSION >= '4.16.0' ) {
            $basenames = array(
                'search_result_box_bg',
                'search_result_item_bg',
            );
            foreach( $basenames as $basename ) {
                if ( isset( $props["{$basename}_color_gradient_stops"] ) && '#2b87da 0%|#29c4a9 100%' === $props["{$basename}_color_gradient_stops"] ) {
                    if (
                        $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_start", $props ) &&
                        $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_start_position", $props ) &&
                        $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_end", $props ) &&
                        $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_end_position", $props )
                    ) {
                        // Strip percent signs and round to nearest int for our calculations.
                        $pos_start      = round( floatval( $props["{$basename}_color_gradient_start_position"] ) );
                        $pos_start_unit = trim( $props["{$basename}_color_gradient_start_position"], ',. 0..9' );
                        $pos_end        = round( floatval( $props["{$basename}_color_gradient_end_position"] ) );
                        $pos_end_unit   = trim( $props["{$basename}_color_gradient_end_position"], ',. 0..9' );

                        // Our sliders use percent values, but pixel values might be manually set.
                        $pos_units_match = ( $pos_start_unit === $pos_end_unit );

                        // If (and ONLY if) both values use the same unit of measurement,
                        // adjust the end position value to be no smaller than the start.
                        if ( $pos_units_match && $pos_end < $pos_start ) {
                            $pos_end = $pos_start;
                        }

                        // Prepare to receive the new gradient settings.
                        $new_values = array(
                            'start' => $props["{$basename}_color_gradient_start"] . ' ' . $pos_start . $pos_start_unit,
                            'end'   => $props["{$basename}_color_gradient_end"] . ' ' . $pos_end . $pos_end_unit,
                        );
                        
                        $props["{$basename}_color_gradient_stops"] = implode( '|', $new_values );
                        
                        $is_responsive_enabled = et_pb_responsive_options()->is_responsive_enabled( $props, "{$basename}_color" );
                        if ( $is_responsive_enabled ) {
                            if ( $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_start_tablet", $props ) ) {
                                $start_color = $props[ "{$basename}_color_gradient_start_tablet" ];
                            } else {
                                $start_color = $props[ "{$basename}_color_gradient_start" ];
                            }

                            if ( $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_end_tablet", $props ) ) {
                                $end_color = $props[ "{$basename}_color_gradient_end_tablet" ];
                            } else {
                                $end_color = $props[ "{$basename}_color_gradient_end" ];
                            }

                            if ( $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_start_position_tablet", $props ) ) {
                                $pos_start = $props[ "{$basename}_color_gradient_start_position_tablet" ];
                                $pos_start_unit = trim( $props[ "{$basename}_color_gradient_start_position_tablet" ], ',. 0..9' );
                            } else {
                                $pos_start = $props[ "{$basename}_color_gradient_start_position" ];
                                $pos_start_unit = trim( $props[ "{$basename}_color_gradient_start_position" ], ',. 0..9' );
                            }

                            if ( $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_end_position_tablet", $props ) ) {
                                $pos_end = $props[ "{$basename}_color_gradient_end_position_tablet" ];
                                $pos_end_unit = trim( $props[ "{$basename}_color_gradient_end_position_tablet" ], ',. 0..9' );
                            } else {
                                $pos_end = $props[ "{$basename}_color_gradient_end_position" ];
                                $pos_end_unit = trim( $props[ "{$basename}_color_gradient_end_position" ], ',. 0..9' );
                            }

                            // Our sliders use percent values, but pixel values might be manually set.
                            $pos_units_match = ( $pos_start_unit === $pos_end_unit );

                            // If (and ONLY if) both values use the same unit of measurement,
                            // adjust the end position value to be no smaller than the start.
                            if ( $pos_units_match && $pos_end < $pos_start ) {
                                $pos_end = $pos_start;
                            }

                            // Prepare to receive the new gradient settings.
                            $new_values = array(
                                'start' => $start_color . ' ' . $pos_start . $pos_start_unit,
                                'end'   => $end_color . ' ' . $pos_end . $pos_end_unit,
                            );

                            $props["{$basename}_color_gradient_stops_tablet"] = implode( '|', $new_values );

                            if ( $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_start_phone", $props ) ) {
                                $start_color = $props["{$basename}_color_gradient_start_phone"];
                            }

                            if ( $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_end_phone", $props ) ) {
                                $end_color = $props["{$basename}_color_gradient_end_phone"];
                            }

                            if ( $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_start_position_phone", $props ) ) {
                                $pos_start = $props["{$basename}_color_gradient_start_position_phone"];
                                $pos_start_unit = trim( $props["{$basename}_color_gradient_start_position_phone"], ',. 0..9' );
                            }

                            if ( $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_end_position_phone", $props ) ) {
                                $pos_end = $props["{$basename}_color_gradient_end_position_phone"];
                                $pos_end_unit = trim( $props["{$basename}_color_gradient_end_position_phone"], ',. 0..9' );
                            }

                            // Our sliders use percent values, but pixel values might be manually set.
                            $pos_units_match = ( $pos_start_unit === $pos_end_unit );

                            // If (and ONLY if) both values use the same unit of measurement,
                            // adjust the end position value to be no smaller than the start.
                            if ( $pos_units_match && $pos_end < $pos_start ) {
                                $pos_end = $pos_start;
                            }

                            // Prepare to receive the new gradient settings.
                            $new_values = array(
                                'start' => $start_color . ' ' . $pos_start . $pos_start_unit,
                                'end'   => $end_color . ' ' . $pos_end . $pos_end_unit,
                            );

                            $props["{$basename}_color_gradient_stops_phone"] = implode( '|', $new_values );
                        }

                        $is_hover_enabled = et_pb_hover_options()->is_enabled( "{$basename}_color", $props );
                        if ( $is_hover_enabled ) {
                            if ( isset(
                                 $props[ "{$basename}_color_gradient_start__hover" ],
                                 $props[ "{$basename}_color_gradient_start_position__hover" ],
                                 $props[ "{$basename}_color_gradient_end__hover" ],
                                 $props[ "{$basename}_color_gradient_end_position__hover" ]
                                )
                            ) {
                                // Strip percent signs and round to nearest int for our calculations.
                                $pos_start      = round( floatval( $props[ "{$basename}_color_gradient_start_position__hover" ] ) );
                                $pos_start_unit = trim( $props[ "{$basename}_color_gradient_start_position__hover" ], ',. 0..9' );
                                $pos_end        = round( floatval( $props[ "{$basename}_color_gradient_end_position__hover" ] ) );
                                $pos_end_unit   = trim( $props[ "{$basename}_color_gradient_end_position__hover" ], ',. 0..9' );

                                // Our sliders use percent values, but pixel values might be manually set.
                                $pos_units_match = ( $pos_start_unit === $pos_end_unit );

                                // If (and ONLY if) both values use the same unit of measurement,
                                // adjust the end position value to be no smaller than the start.
                                if ( $pos_units_match && $pos_end < $pos_start ) {
                                    $pos_end = $pos_start;
                                }

                                // Prepare to receive the new gradient settings.
                                $new_values = array(
                                    'start' => $props[ "{$basename}_color_gradient_start__hover" ] . ' ' . $pos_start . $pos_start_unit,
                                    'end'   => $props[ "{$basename}_color_gradient_end__hover" ] . ' ' . $pos_end . $pos_end_unit,
                                );

                                $props[ "{$basename}_color_gradient_stops__hover" ] = implode( '|', $new_values );
                            }
                        }
                    }
                }
                if ( $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_type", $props ) && 'radial' === $props["{$basename}_color_gradient_type"] ) {
                    $props[ "{$basename}_color_gradient_type" ] = 'circular';
                    $is_responsive_enabled = et_pb_responsive_options()->is_responsive_enabled( $props, "{$basename}_color" );
                    if ( $is_responsive_enabled ) {
                        foreach ( array( 'tablet', 'phone' ) as $device ) {
                            if ( $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_type_{$device}", $props ) && 'radial' === $props[ "{$basename}_color_gradient_type_{$device}" ] ) {
                                $props[ "{$basename}_color_gradient_type_{$device}" ] = 'circular';
                            }
                        }
                    }
                    $is_hover_enabled = et_pb_hover_options()->is_enabled( "{$basename}_color", $props );
                    if ( $is_hover_enabled ) {
                        if ( $this->el_ajax_search_exists_and_is_not_empty( "{$basename}_color_gradient_type__hover", $props ) && 'radial' === $props[ "{$basename}_color_gradient_type__hover" ] ) {
                            $props["{$basename}_color_gradient_type__hover"] = 'circular';
                        }
                    }
                }
            }
        }

        return $props;
    }

    public function el_ajax_search_results() {
        if ( ! isset( $_POST['divi_ajax_search_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['divi_ajax_search_nonce'] ) ), 'elicus-divi-ajax-search-nonce' ) ) {
            return;
        }

        global $wpdb;

        $search             = isset( $_POST['search'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['search'] ) ) ) : '';
        $post_types         = isset( $_POST['post_types'] ) ? sanitize_text_field( wp_unslash( $_POST['post_types'] ) ) : '';
        $search_in          = isset( $_POST['search_in'] ) ? sanitize_text_field( wp_unslash( $_POST['search_in'] ) ) : '';
        $display_fields     = isset( $_POST['display_fields'] ) ? sanitize_text_field( wp_unslash( $_POST['display_fields'] ) ) : '';
        $url_new_window     = isset( $_POST['url_new_window'] ) ? sanitize_text_field( wp_unslash( $_POST['url_new_window'] ) ) : 'off';
        $number_of_results  = isset( $_POST['number_of_results'] ) ? intval( wp_unslash( $_POST['number_of_results'] ) ) : '10';
        $no_result_text     = isset( $_POST['no_result_text'] ) ? sanitize_text_field( wp_unslash( $_POST['no_result_text'] ) ) : 'No result found';
        $orderby            = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : 'post_date';
        $order              = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'DESC';
        $masonry            = isset( $_POST['masonry'] ) ? sanitize_text_field( wp_unslash( $_POST['masonry'] ) ) : 'off';
        $exclude_post_ids   = isset( $_POST['exclude_post_ids'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['exclude_post_ids'] ) ) ) : '';
        $exclude_post_types = isset( $_POST['exclude_post_types'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['exclude_post_types'] ) ) ) : '';
        $exclude_taxonomies = isset( $_POST['exclude_taxonomies'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['exclude_taxonomies'] ) ) ) : '';
        $include_taxonomies = isset( $_POST['include_taxonomies'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['include_taxonomies'] ) ) ) : '';
        $excerpt_length     = isset( $_POST['excerpt_length'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['excerpt_length'] ) ) ) : '';
        $result_layout      = isset( $_POST['result_layout'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['result_layout'] ) ) ) : 'layout1';
        $page               = isset( $_POST['page'] ) ? absint( wp_unslash( $_POST['page'] ) ) : 1;

        if ( empty( $search ) ) {
            echo '';
            exit();
        }

        $link_target = 'on' === $url_new_window ? esc_attr( '_blank' ) : esc_attr( '_self' );

        $offset = 0;
        if ( $page > 1 ) {
            $offset = ( $page - 1 ) * $number_of_results + $offset;
        }

        // Create the args
        $args = array(
            'search'             => $search,
            'post_types'         => $post_types,
            'search_in'          => $search_in,
            'orderby'            => $orderby,
            'order'              => $order,
            'exclude_post_ids'   => $exclude_post_ids,
            'exclude_post_types' => $exclude_post_types,
            'exclude_taxonomies' => $exclude_taxonomies,
            'include_taxonomies' => $include_taxonomies,
            'number_of_results'  => $number_of_results,
            'fields'             => 'DISTINCT ID, post_parent',
            'offset'             => $offset
        );

        // Create new query
        $query = el_divi_ajax_create_search_query( $args );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
        $results = $wpdb->get_results( $query, ARRAY_A );

        // Get pagination with default pages.
        $return_data = array();
        if ( ! $results || empty( $results ) ) {
            $output  = '<div class="el_ajax_search_no_results">';
            $output .= esc_html( $no_result_text );
            $output .= '</div>';

            $return_data['total_pages'] = 0;
            $return_data['html'] = et_core_intentionally_unescaped( $output, 'html' );
            wp_send_json( $return_data );
            exit();
        }

        // Get the total page.
        if ( 1 === $page ) {
            $args['number_of_results'] = -1;
            $args['fields']            = 'count( DISTINCT ID ) AS total_count';

            $count_query = el_divi_ajax_create_search_query( $args );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
            $count_result = $wpdb->get_var( $count_query );

            $return_data['total_pages'] = 0;
            if ( $count_result > 0 ) {
                $return_data['total_pages'] = intval( ceil( $count_result / $number_of_results ) );
            }
        }

        $whitelisted_display_fields = array( 'title', 'excerpt', 'featured_image', 'product_price' );
        $display_fields             = explode( ',', $display_fields );
        foreach ( $display_fields as $key => $display_field ) {
            if ( ! in_array( $display_field, $whitelisted_display_fields, true ) ) {
                unset( $display_fields[ $key ] );
            }
        }

        if ( empty( $display_fields ) ) {
            $display_fields = array( 'title' );
        }

        $masonry_class = 'on' === $masonry ? ' el_ajax_search_result_masonry' : '';

        $output  = '<div class="el_ajax_search_results' . $masonry_class . '">';
        $output .= '<div class="el_ajax_search_items">';

        if ( 'on' === $masonry ) {
            $output .= '<div class="el_ajax_search_isotope_item_gutter"></div>';
        }

        $results = array_unique( array_column( $results, 'ID' ) );
        foreach ( $results as $id ) {
            $post_id    = absint( $id );
            $post_title = get_the_title( $post_id );

            if ( 'product' === get_post_type( $post_id ) ) {
                $product            = wc_get_product( $post_id );
                $product_visibility = $product->get_catalog_visibility();
                if ( ! in_array( $product_visibility, array( 'search', 'visible' ) ) ) {
                    continue;
                }
            } else {
                $product = '';
            }

            $output .= '<div class="el_ajax_search_isotope_item">';
            if ( file_exists( $this->plugin_dir . 'modules/AjaxSearch/layouts/' . sanitize_title_with_dashes( $result_layout ) . '.php' ) ) {
                include $this->plugin_dir . 'modules/AjaxSearch/layouts/' . sanitize_title_with_dashes( $result_layout ) . '.php';
            } else {
                include $this->plugin_dir . 'modules/AjaxSearch/layouts/layout1.php';
            }

            $output .= '</div>';
        }
        $output .= '</div>';
        $output .= '</div>';

        $return_data['html'] = et_core_intentionally_unescaped( $output, 'html' );

        wp_send_json( $return_data );
        exit();
    }

    public function el_ajax_search_search_result_page_query( $sql, $query ) {
        // If not plugin search.
        if ( empty( $_GET['dpajaxsearch'] ) || 'yes' !== $_GET['dpajaxsearch'] || ! $query->is_main_query() || is_admin() ) {
            return $sql;
        }

        // verify nonce.
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( trim( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) ), 'elicus-divi-ajax-search-nonce' ) ) {
            return $sql;
        }

        $paged    = $query->get( 'paged' );
        $per_page = $query->get( 'posts_per_page' );
        $offset   = 0;
        if ( $paged > 1 ) {
            $offset = ( $paged - 1 ) * $per_page + $offset;
        }

        // Create the args.
        $args = array(
            'search'             => isset( $_GET['s'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) : '',
            'post_types'         => isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '',
            'search_in'          => isset( $_GET['search_in'] ) ? sanitize_text_field( wp_unslash( $_GET['search_in'] ) ) : '',
            'orderby'            => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'post_date',
            'order'              => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC',
            'exclude_post_ids'   => isset( $_GET['exclude_post_ids'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['exclude_post_ids'] ) ) ) : '',
            'exclude_post_types' => isset( $_GET['exclude_post_types'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['exclude_post_types'] ) ) ) : '',
            'exclude_taxonomies' => isset( $_GET['exclude_taxonomies'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['exclude_taxonomies'] ) ) ) : '',
            'include_taxonomies' => isset( $_GET['include_taxonomies'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['include_taxonomies'] ) ) ) : '',
            'number_of_results'  => $per_page,
            'offset'             => $offset,
        );

        // Create new query.
        $new_query = el_divi_ajax_create_search_query( $args );

        return $new_query;
    }
    
}

new EL_DiviAjaxSearch;