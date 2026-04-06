<?php
/**
 * The Template for displaying Layout 1
 *
 * @author      Elicus Technologies <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2024 Elicus Technologies Private Limited
 * @version     1.0.1
 */

$output .= '<div class="el_ajax_search_item">';
	$output .= '<a href="' . esc_url( get_permalink( $post_id ) ) . '" class="el_ajax_search_item_link" target="' . esc_attr( $link_target ) . '">';
		
		$post_thumbnail = '';
		if ( in_array( 'featured_image', $display_fields ) ) {
			// has_post_thumbnail return true even image is deleted.
			$post_thumbnail = get_the_post_thumbnail( $post_id, 'medium' );
			if ( ! empty( $post_thumbnail ) ) {
				$output .= '<div class="el_ajax_search_item_image">';
				$output .= et_core_intentionally_unescaped( $post_thumbnail, 'html' );
				$output .= '</div>';
			}
		}
		if (
			( in_array( 'title', $display_fields ) && ! empty( $post_title ) ) ||
			in_array( 'excerpt', $display_fields ) || in_array( 'product_price', $display_fields )
		) {
			$classes  = 'el_ajax_search_item_content';
			$classes .= empty( $post_thumbnail ) ? ' el_ajax_item_no_image' : '';
			
			$output .= '<div class="' . esc_attr( $classes ) . '">';
			if ( ! empty( $post_title ) && in_array( 'title', $display_fields ) ) {
				$output .= '<h4 class="el_ajax_search_item_title">' . esc_html( $post_title ) . '</h4>';
			}
			if ( in_array( 'product_price', $display_fields ) && isset( $product ) && ! empty( $product ) ) {
				$output .= '<p class="el_ajax_search_item_price">' . et_core_intentionally_unescaped( $product->get_price_html(), 'html' ) . '</p>';
			}
			if ( in_array( 'excerpt', $display_fields ) ) {
				if ( has_excerpt( $post_id ) && '' !== trim( get_the_excerpt( $post_id ) ) ) {
					$excerpt = wp_strip_all_tags( strip_shortcodes( get_the_excerpt( $post_id ) ) );
					if ( $excerpt_length > 0 ) {
						$excerpt = el_divi_ajax_trim_content( $excerpt, $excerpt_length );
					}
				} else {
					$excerpt_length = ! empty( $excerpt_length ) ? $excerpt_length : 100;
					$excerpt        = wp_strip_all_tags( strip_shortcodes( el_divi_ajax_search_truncate_post( $excerpt_length, false, $post_id, true ) ) );
				}
				if ( '' !== trim( $excerpt ) ) {
					$output .= '<p class="el_ajax_search_item_excerpt">' . esc_html( $excerpt ) . '</p>';
				}
			}
			$output .= '</div>';
		}
	$output .= '</a>';
$output .= '</div>';