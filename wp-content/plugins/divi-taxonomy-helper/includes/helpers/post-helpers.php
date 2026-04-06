<?php
/**
 * Get All WP Registered Post Types
 * @return array
 */
if (!function_exists('pac_dth_get_registered_post_types')):
    function pac_dth_get_registered_post_types()
    {
        $post_types = [];
        $lists = wp_list_pluck(get_post_types(['public' => true], 'objects'), 'label', 'name');
        foreach ($lists as $key => $value) {
            if (!in_array($key, ['page', 'attachment'])) {
                $post_types[$key] = $value;
            }
        }

        return array_filter($post_types);
    }
endif;
/**
 * Get All WP Registered Taxonomies
 *
 * @param $exclude
 *
 * @return array
 */
if (!function_exists('pac_dth_get_registered_taxonomies')):
    function pac_dth_get_registered_taxonomies($exclude = [])
    {
        $taxonomies_list = [];
        $default_exclude = ['layout_category', 'layout_tag'];
        $exclude = wp_parse_args($exclude, $default_exclude);
        $taxonomies = get_taxonomies(['public' => true, 'show_ui' => true,], 'objects');
        if (!empty($taxonomies)) {
            foreach ($taxonomies as $key => $values) {
                if (!in_array($key, $exclude)) {
                    $taxonomies_list[$key] = ucwords($values->label);
                }
            }
        }

        return array_filter($taxonomies_list);
    }
endif;
/**
 * Get Taxonomies By Object
 *
 * @param $object
 *
 * @return array
 */
if (!function_exists('pac_dth_get_taxonomies_by_object')):
    function pac_dth_get_taxonomies_by_object($object)
    {
        $taxonomies = [];
        $taxonomy_objects = get_object_taxonomies($object, 'objects');
        if (!empty($taxonomy_objects)) {
            foreach ($taxonomy_objects as $key => $values) {
                if (!in_array($key, ['post_format', 'product_type', 'product_visibility', 'product_shipping_class'])) {
                    if (substr($key, 0, 3) === 'pa_') {
                        continue;
                    }
                    $taxonomies[$values->name] = ucwords($values->label);
                }
            }
        }

        return array_filter($taxonomies);
    }
endif;
/**
 * Get Terms By Taxonomy
 *
 * @param $taxonomy
 *
 * @return array
 */
if (!function_exists('pac_dth_get_terms_by_taxonomy')):
    function pac_dth_get_terms_by_taxonomy($taxonomy, $show_count = true)
    {
        $data = [];
        $terms = get_terms(['orderby' => 'name', 'order' => 'ASC', 'taxonomy' => $taxonomy, 'hide_empty' => false]);
        if (!empty($terms)) {
            foreach ($terms as $parent_term) {
                if (0 === $parent_term->parent) {
                    $term_id = $parent_term->term_id;
                    $term_index = $parent_term->slug.'_'.$term_id;
                    if ($show_count) {
                        $data[$term_index] = sprintf('%s (%d)', ucwords($parent_term->name), $parent_term->count);
                    } else {
                        $data[$term_index] = sprintf('%s', ucwords($parent_term->name));
                    }
                    $child_data = pac_dth_get_child_terms($term_id, $taxonomy, $show_count);
                    if (!empty($child_data)) {
                        $data = array_merge($data, $child_data);
                    }
                }
            }
        }

        return array_filter($data);
    }
endif;
/**
 * Get Child Terms
 *
 * @param $parent_term_id
 * @param $taxonomy
 *
 * @return array
 */
if (!function_exists('pac_dth_get_child_terms')):
    function pac_dth_get_child_terms($parent_term_id, $taxonomy, $show_count = true)
    {
        $data = [];
        $childrens = get_term_children($parent_term_id, $taxonomy);
        $counter = 1;
        foreach ($childrens as $children) {
            $children_term = get_term($children);
            $child_term_id = $children_term->term_id;
            $child_term_index = $children_term->slug.'_'.$child_term_id;
            if ($show_count) {
                $data[$child_term_index] = sprintf('%s %s (%d)', str_repeat('-', $counter), ucwords($children_term->name), $children_term->count);
            } else {
                $data[$child_term_index] = sprintf('%s %s ', str_repeat('-', $counter), ucwords($children_term->name));
            }
            $child_data = pac_dth_get_child_terms($child_term_id, $taxonomy);
            if (!empty($child_data)) {
                $data = array_merge($data, $child_data);
                $counter++;
            }
        }

        return array_filter($data);
    }
endif;
/**
 * Get Top Level Terms
 *
 * @param $terms
 * @param $taxonomy
 *
 * @return array
 */
if (!function_exists('pac_dth_get_top_level_terms')):
    function pac_dth_get_top_level_terms($terms, $taxonomy)
    {
        $top_level_terms = [];
        foreach ($terms as $term_id) {
            $child = get_term($term_id);
            if (0 === $child->parent) {
                $top_level_terms[] = $term_id;
            }
        }
        foreach ($top_level_terms as $top_level_term_id) {
            $term_children = get_term_children($top_level_term_id, $taxonomy);
            foreach ($term_children as $term_id) {
                if ($top_level_term_id === get_term($term_id)->parent && in_array($term_id, $terms)) {
                    $top_level_terms[] = $term_id;
                }
            }
        }

        return $top_level_terms;
    }
endif;
/**
 * @param $url
 *
 * @return int|WP_Post
 */
if (!function_exists('pac_dth_get_attachment_id')):
    function pac_dth_get_attachment_id($url)
    {
        $attachment_id = 0;
        $dir = wp_upload_dir();
        if (str_contains($url, $dir['baseurl'].'/')) { // Is URL in uploads directory?
            $file = basename($url);
            // phpcs:disable
            $query_args = [
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'fields' => 'ids',
                'meta_query' => [
                    [
                        'value' => $file,
                        'compare' => 'LIKE',
                        'key' => '_wp_attachment_metadata',
                    ],
                ]
            ];
            $query = new WP_Query($query_args);
            // phpcs:enable
            if ($query->have_posts()) {
                foreach ($query->posts as $post_id) {
                    $meta = wp_get_attachment_metadata($post_id);
                    $original_file = basename($meta['file']);
                    $cropped_image_files = wp_list_pluck($meta['sizes'], 'file');
                    if ($original_file === $file || in_array($file, $cropped_image_files)) {
                        $attachment_id = $post_id;
                        break;
                    }
                }
            }
        }

        return $attachment_id;
    }
endif;
/**
 * Is Taxonomy Page
 *
 * @param $taxonomy
 *
 * @return array
 */
if (!function_exists('pac_dth_get_is_taxonomy_page')):
    function pac_dth_get_is_taxonomy_page($taxonomy)
    {
        $is_category = 'category' === $taxonomy && is_category();
        $is_tag = !$is_category && 'post_tag' === $taxonomy && is_tag();
        $is_tax = !$is_category && !$is_tag && is_tax($taxonomy);
        if ($is_category || $is_tag || $is_tax) {
            return true;
        }

        return false;
    }
endif;
