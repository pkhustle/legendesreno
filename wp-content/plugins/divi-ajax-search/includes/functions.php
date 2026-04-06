<?php
if ( ! function_exists( 'el_divi_ajax_search_strip_shortcodes' ) ) {
    function el_divi_ajax_search_strip_shortcodes( $content, $truncate_post_based_shortcodes_only = false ) {
        global $shortcode_tags;

        $content = trim( $content );

        $strip_content_shortcodes = array(
            'et_pb_code',
            'et_pb_fullwidth_code',
            'dipl_modal',
            'el_modal_popup',
        );

        // list of post-based shortcodes.
        if ( $truncate_post_based_shortcodes_only ) {
            $strip_content_shortcodes = array(
                'et_pb_post_slider',
                'et_pb_fullwidth_post_slider',
                'et_pb_blog',
                'et_pb_blog_extras',
                'et_pb_comments',
            );
        }

        foreach ( $strip_content_shortcodes as $shortcode_name ) {
            $regex = sprintf(
                '(\[%1$s[^\]]*\][^\[]*\[\/%1$s\]|\[%1$s[^\]]*\])',
                esc_html( $shortcode_name )
            );

            $content = preg_replace( $regex, '', $content );
        }

        // do not proceed if we need to truncate post-based shortcodes only.
        if ( $truncate_post_based_shortcodes_only ) {
            return $content;
        }

        $shortcode_tag_names = array();
        foreach ( $shortcode_tags as $shortcode_tag_name => $shortcode_tag_cb ) {
            if ( 0 !== strpos( $shortcode_tag_name, 'et_pb_' ) ) {
                continue;
            }

            $shortcode_tag_names[] = $shortcode_tag_name;
        }

        $et_shortcodes = implode( '|', $shortcode_tag_names );

        $regex_opening_shortcodes = sprintf( '(\[(%1$s)[^\]]+\])', esc_html( $et_shortcodes ) );
        $regex_closing_shortcodes = sprintf( '(\[\/(%1$s)\])', esc_html( $et_shortcodes ) );

        $content = preg_replace( $regex_opening_shortcodes, '', $content );
        $content = preg_replace( $regex_closing_shortcodes, '', $content );

        return et_core_intentionally_unescaped( $content, 'html' );
    }
}

if ( ! function_exists( 'el_divi_ajax_search_truncate_post' ) ) {
    function el_divi_ajax_search_truncate_post( $amount, $do_print = true, $post_id = '', $strip_shortcodes = false ) {
        global $shortname;

        if ( '' === $post_id ) {
            return '';
        }

        $post_object = get_post( $post_id );

        $post_excerpt = '';
        $post_excerpt = apply_filters( 'the_excerpt', $post_object->post_excerpt );
        if ( 'on' === et_get_option( $shortname . '_use_excerpt' ) && '' !== $post_excerpt ) {
            if ( $do_print ) {
                echo et_core_intentionally_unescaped( $post_excerpt, 'html' );
            } else {
                return et_core_intentionally_unescaped( $post_excerpt, 'html' );
            }
        } else {
            // get the post content.
            $truncate = $post_object->post_content;

            // remove caption shortcode from the post content.
            $truncate = preg_replace( '@\[caption[^\]]*?\].*?\[\/caption]@si', '', $truncate );

            // remove post nav shortcode from the post content.
            $truncate = preg_replace( '@\[et_pb_post_nav[^\]]*?\].*?\[\/et_pb_post_nav]@si', '', $truncate );

            // Remove audio shortcode from post content to prevent unwanted audio file on the excerpt
            // due to unparsed audio shortcode.
            $truncate = preg_replace( '@\[audio[^\]]*?\].*?\[\/audio]@si', '', $truncate );

            // Remove embed shortcode from post content.
            $truncate = preg_replace( '@\[embed[^\]]*?\].*?\[\/embed]@si', '', $truncate );

            if ( $strip_shortcodes ) {
                $truncate = el_divi_ajax_search_strip_shortcodes( $truncate );
            } else {
                // apply content filters.
                $truncate = apply_filters( 'the_content', $truncate );
            }

            // decide if we need to append dots at the end of the string.
            if ( strlen( $truncate ) <= $amount ) {
                $echo_out = '';
            } else {
                $echo_out = '...';
                if ( $amount > 3 ) {
                    $amount = $amount - 3;
                }
            }

            // trim text to a certain number of characters, also remove spaces from the end of a string ( space counts as a character ).
            $truncate = rtrim( et_wp_trim_words( $truncate, $amount, '' ) );

            // remove the last word to make sure we display all words correctly.
            if ( '' !== $echo_out ) {
                $new_words_array = (array) explode( ' ', $truncate );
                array_pop( $new_words_array );

                $truncate = implode( ' ', $new_words_array );

                // append dots to the end of the string.
                if ( '' !== $truncate ) {
                    $truncate .= $echo_out;
                }
            }

            if ( $do_print ) {
                echo et_core_intentionally_unescaped( $truncate, 'html' );
            } else {
                return et_core_intentionally_unescaped( $truncate, 'html' );
            }
        }
    }
}

/**
 * Limit the excerpt/content
 */
if ( ! function_exists( 'el_divi_ajax_trim_content' ) ) {
    function el_divi_ajax_trim_content( $content, $amount = 100, $end_width = '...' ) {

        // decide if we need to append dots at the end of the string.
        if ( strlen( $content ) <= $amount ) {
            $echo_out = '';
        } else {
            $echo_out = $end_width;
            if ( $amount > 3 ) {
                $amount = $amount - 3;
            }
        }

        // trim text to a certain number of characters, also remove spaces from the end of a string ( space counts as a character ).
        $truncate = rtrim( et_wp_trim_words( $content, $amount, '' ) );

        // remove the last word to make sure we display all words correctly.
        if ( '' !== $echo_out ) {
            $new_words_array = (array) explode( ' ', $truncate );
            array_pop( $new_words_array );

            $truncate = implode( ' ', $new_words_array );

            // append dots to the end of the string.
            if ( '' !== $truncate ) {
                $truncate .= $echo_out;
            }
        }

        return et_core_intentionally_unescaped( $truncate, 'html' );
    }
}

/**
 * Create search query based on params
 */
if ( ! function_exists( 'el_divi_ajax_create_search_query' ) ) {
    function el_divi_ajax_create_search_query( $args ) {
        global $wpdb;
        $table  = $wpdb->prefix . 'posts';

        $defaults = array(
            'search'             => '',
            'post_types'         => '',
            'search_in'          => '',
            'orderby'            => '',
            'order'              => '',
            'exclude_post_types' => '',
            'exclude_post_ids'   => '',
            'exclude_taxonomies' => '',
            'include_taxonomies' => '',
            'number_of_results'  => '',
            'offset'             => '',
            'fields'             => 'DISTINCT SQL_CALC_FOUND_ROWS ' . $table . '.*'
        );

        $args = wp_parse_args( $args, $defaults );
        foreach ( $defaults as $key => $default ) {
            ${$key} = sanitize_text_field( et_()->array_get( $args, $key, $default ) );
        }

        $raw_post_types = get_post_types( array(
            'public' => true,
            'show_ui' => true,
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

        $post_types = explode( ',', $post_types );
        foreach( $post_types as $key => $post_type ) {
            if ( ! in_array( $post_type, $whitelisted_post_types, true ) ) {
                unset( $post_types[$key] );
            }
        }

        //Filter to exclude or include post types
        $exclude_post_types             = explode( ',', $exclude_post_types );
        $exclude_post_types             = array_map( 'trim', $exclude_post_types );
        $exclude_post_types_by_filter   = apply_filters( 'das_exclude_post_types', array() );
        $exclude_post_types             = array_merge( $exclude_post_types, $exclude_post_types_by_filter );
        $post_types                     = array_diff( $post_types, $exclude_post_types );

        $whitelisted_search_in = array( 'post_title', 'post_content', 'post_excerpt', 'taxonomies', 'product_attributes', 'sku' );
        $search_in = explode( ',', $search_in );
        foreach( $search_in as $key => $in ) {
            if ( ! in_array( $in, $whitelisted_search_in, true ) ) {
                unset( $search_in[$key] );
            }
        }

        $whitelisted_orderby = array( 'post_date', 'post_modified', 'post_title', 'post_name', 'ID', 'rand' );
        if ( ! in_array( $orderby, $whitelisted_orderby, true ) ) {
            $orderby = 'post_date';
        }

        $whitelisted_order = array( 'ASC', 'DESC' );
        if ( ! in_array( $order, $whitelisted_order, true ) ) {
            $order = 'DESC';
        }

        if ( 'rand' === $orderby ) {
            $orderby    = 'RAND()';
            $order      = '';
        }

        if ( in_array( 'product', $post_types, true ) && 
            ( in_array( 'product_attributes', $search_in, true ) || in_array( 'sku', $search_in, true ) ) ) {
            array_push( $post_types, 'product_variation' );
        }

        $search_term    = '%' . $wpdb->esc_like( $search ) . '%';
        $join           = '';
        $where_search   = '';
        $query_operator = '';

        if ( in_array('sitepress-multilingual-cms/sitepress.php', apply_filters( 'active_plugins', get_option('active_plugins') ) ) ) { 
            $wpml_table = $wpdb->prefix . 'icl_translations';
            $join .= " INNER JOIN $wpml_table das_wpiclt ON ($wpdb->posts.ID = das_wpiclt.element_id) ";
        }

        // if title is selected in search in.
        if ( in_array( 'post_title', $search_in, true ) ) {
            // $where_search .= $wpdb->prepare( "($wpdb->posts.post_title LIKE %s)", $search_term );
            $where_search   .= $wpdb->prepare( 
                "(
                    $wpdb->posts.post_title LIKE %s AND 
                        ( $wpdb->posts.post_type != 'product_variation' OR $wpdb->posts.post_parent = 0 )
                    OR
                    ( $wpdb->posts.ID IN (
                        SELECT titlep.post_parent FROM $wpdb->posts AS titlep
                            WHERE titlep.post_title LIKE %s AND titlep.post_type = 'product_variation' 
                        AND titlep.post_parent > 0
                    ) )
                )",
                $search_term,
                $search_term
            );
            $query_operator  = ' OR ';
        }
        
        // if content is selected in search in.
        if ( in_array( 'post_content', $search_in, true ) ) {
            $where_search   .= $query_operator;
            // $where_search   .= $wpdb->prepare( "($wpdb->posts.post_content LIKE %s)", $search_term );
            $where_search   .= $wpdb->prepare(
                "(
                    $wpdb->posts.post_content LIKE %s AND
                        ( $wpdb->posts.post_type != 'product_variation' OR $wpdb->posts.post_parent = 0 )
                    OR
                    ( $wpdb->posts.ID IN (
                        SELECT contentp.post_parent FROM $wpdb->posts AS contentp
                            WHERE contentp.post_title LIKE %s AND contentp.post_type = 'product_variation'
                        AND contentp.post_parent > 0
                    ) )
                )",
                $search_term,
                $search_term
            );
            $query_operator  = ' OR ';
        }

        // if excerpt is selected in search in.
        if ( in_array( 'post_excerpt', $search_in, true ) ) {
            $where_search   .= $query_operator;
            //$where_search .= $wpdb->prepare( "( $wpdb->posts.post_excerpt LIKE %s )", $search_term );
            $where_search   .= $wpdb->prepare(
                "(
                    $wpdb->posts.post_excerpt LIKE %s AND
                        ( $wpdb->posts.post_type != 'product_variation' OR $wpdb->posts.post_parent = 0 )
                    OR
                    ( $wpdb->posts.ID IN (
                        SELECT excerptp.post_parent FROM $wpdb->posts AS excerptp
                            WHERE excerptp.post_excerpt LIKE %s AND excerptp.post_type = 'product_variation'
                        AND excerptp.post_parent > 0
                    ) )
                )",
                $search_term,
                $search_term
            );
            $query_operator  = ' OR ';
        }

        // if sku is selected in search in.
        // if ( in_array( 'product', $post_types, true ) && in_array( 'sku', $search_in, true ) ) {
        //  $join          .= " LEFT JOIN $wpdb->postmeta das_pm ON ($wpdb->posts.ID = das_pm.post_id) ";
        //  $where_search  .= $query_operator;
        //  $where_search  .= $wpdb->prepare( '(das_pm.meta_key = %s AND das_pm.meta_value LIKE %s)', '_sku', $search_term );
        //  $query_operator = ' OR ';
        // }

        // if sku is selected in search in.
        if ( in_array( 'product', $post_types, true ) && in_array( 'sku', $search_in, true ) ) {
            $join          .= " LEFT JOIN $wpdb->postmeta das_pm ON ($wpdb->posts.ID = das_pm.post_id) ";
            $where_search  .= $query_operator;
            $where_search  .= $wpdb->prepare(
                "(
                    ( das_pm.meta_key = '_sku' AND das_pm.meta_value LIKE %s AND $wpdb->posts.post_parent = 0 )
                        OR
                    ( $wpdb->posts.ID IN (
                        SELECT sku_p.post_parent FROM $wpdb->posts AS sku_p 
                            LEFT JOIN $wpdb->postmeta AS sku_pm ON sku_pm.post_id = sku_p.ID
                        WHERE sku_p.post_parent > 0 AND sku_p.post_type = 'product_variation' 
                            AND sku_pm.meta_key = '_sku' AND sku_pm.meta_value LIKE %s
                    ) )
                )",
                $search_term,
                $search_term
            );
            $query_operator = ' OR ';
        }

        $taxonomies = get_object_taxonomies( $post_types );

        if ( in_array( 'product', $post_types, true ) && in_array( 'product_attributes', $search_in, true ) && ! empty( $taxonomies ) ) {
            $pattern    = '/^pa_/';
            $attributes = preg_grep( $pattern, $taxonomies );
            if ( $attributes && ! empty( $attributes ) ) {
                $tax_operator = '';
                foreach ( $attributes as $attribute ) {
                    $where_search   .= $query_operator . $tax_operator;
                    $where_search   .= $wpdb->prepare( '(das_tt.taxonomy = %s AND das_t.name LIKE %s)', esc_sql( $attribute ), $search_term );
                    $query_operator  = '';
                    $tax_operator    = ' OR ';
                }
                $query_operator = ' OR ';
            }
        }

        // Add exclude from search
        if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
            $all_term_ids     = wc_get_product_visibility_term_ids();
            $exclude_term_ids = isset( $all_term_ids['exclude-from-search'] ) ? $all_term_ids['exclude-from-search'] : '';
            if ( ! empty( $exclude_term_ids ) ) {
                $where_search .= $wpdb->prepare( " AND $wpdb->posts.ID NOT IN ( SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id IN (%s) )", $exclude_term_ids );
            }
        }

        // if custom taxonomies is selected in search in.
        if ( in_array( 'taxonomies', $search_in, true ) && ! empty( $taxonomies ) ) {
            $taxonomies            = array_unique( $taxonomies );
            $pattern               = '/^pa_/';
            $attributes            = preg_grep( $pattern, $taxonomies );
            $exclude               = array( 'product_visibility', 'product_shipping_class', 'product_type' );
            $exclude               = array_merge( $exclude, $attributes );
            $exclude_taxonomies    = explode( ',', $exclude_taxonomies );
            $exclude_taxonomies    = array_map( 'trim', $exclude_taxonomies );
            $exclude               = array_merge( $exclude, $exclude_taxonomies );
            $exclude_new           = apply_filters( 'das_exclude_taxonomies', $exclude );
            if ( is_array( $exclude_new ) && ! empty( $exclude_new) ) {
                $exclude = array_merge( $exclude, $exclude_new );
            }
            $custom_tax = array_diff( $taxonomies, $exclude );
            if ( $custom_tax && ! empty( $custom_tax ) ) {
                $tax_operator = '';
                foreach ( $custom_tax as $taxonomy ) {
                    $where_search   .= $query_operator . $tax_operator;
                    $where_search   .= $wpdb->prepare( '(das_tt.taxonomy = %s AND das_t.name LIKE %s)', esc_sql( $taxonomy ), $search_term );
                    $query_operator  = '';
                    $tax_operator    = ' OR ';
                }
                $query_operator = ' OR ';
            }
        }

        if ( ! empty( $taxonomies ) && ( 
            in_array( 'product_attributes', $search_in, true ) ||
            in_array( 'taxonomies', $search_in, true )
        ) ) {
            $join .= " LEFT JOIN $wpdb->term_relationships das_tr ON ($wpdb->posts.ID = das_tr.object_id) ";
            $join .= " LEFT JOIN $wpdb->term_taxonomy das_tt ON (das_tr.term_taxonomy_id = das_tt.term_taxonomy_id) ";
            $join .= " LEFT JOIN $wpdb->terms das_t ON (das_tt.term_id = das_t.term_id) ";
        }

        $where_post_type    = '';
        $query_operator     = '';
        foreach( $post_types as $post_type ) {
            $where_post_type   .= $query_operator;
            $where_post_type   .= $wpdb->prepare( "($wpdb->posts.post_type = %s)", esc_sql( $post_type ) );
            $query_operator     = ' OR ';
        }

        $order_clause = " ORDER BY {$orderby} {$order} ";

        $limit = '';
        if ( -1 !== $number_of_results ) {
            $limit = ' LIMIT '. esc_sql( absint( $number_of_results ) );
            if ( ! empty( $offset ) ) {
                $limit .= ' OFFSET ' . esc_sql( absint( $offset ) );
            }
        }

        //Filter to exclude post ids
        $exclude_post_ids_by_filter = apply_filters( 'das_exclude_post_ids', array() );
        $exclude_post_ids = explode( ',', $exclude_post_ids );
        $exclude_post_ids = array_map( 'trim', $exclude_post_ids );
        $exclude_post_ids = array_merge( $exclude_post_ids, $exclude_post_ids_by_filter );
        $exclude_post_ids = array_unique( array_map( 'intval', $exclude_post_ids ) );
        $exclude_post_ids = implode( ",", array_map( 'esc_sql', $exclude_post_ids ) );

        $where      = "(" . $where_search . ") AND (" . $where_post_type . ") AND post_status = 'publish' ";
        if ( $exclude_post_ids ) {
            $where .= "AND ID NOT IN (" . $exclude_post_ids . ") ";
        }

        if ( in_array('sitepress-multilingual-cms/sitepress.php', apply_filters('active_plugins', get_option('active_plugins') ) ) ) { 
            $where .= " AND das_wpiclt.language_code = '". ICL_LANGUAGE_CODE ."' ";
        }

        return "SELECT {$fields} FROM " . $table . $join . " WHERE " . $where . $order_clause . $limit;
    }
}