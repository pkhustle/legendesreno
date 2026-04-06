<?php
/**
 * Get Aspect Ratio CSS Property
 *
 * @param $aspect_ratio
 *
 * @return string
 */
if (!function_exists('pac_dth_get_aspect_ratio_css')):
    function pac_dth_get_aspect_ratio_css($aspect_ratio)
    {
        $aspect_ratios = [
            '1__1' => '100%',
            '16__9' => '56.25%',
            '4__3' => '75%',
            '3__2' => '66.66%',
            '9__16' => '177.77%',
            '3__4' => '133.33%',
            '2__3' => '150%',
        ];
        if (array_key_exists($aspect_ratio, $aspect_ratios)) {
            $aspect_ratio_css = "position: relative;width: 100%;display:block;padding-top:$aspect_ratios[$aspect_ratio];";
        } else {
            $aspect_ratio_css = '';
        }

        return $aspect_ratio_css;
    }
endif;
/**
 * Add Custom Media Query
 *
 * @param $media_queries
 *
 * @return mixed
 */
if (!function_exists('pac_dth_maybe_custom_media_queries')):
    function pac_dth_maybe_custom_media_queries($media_queries)
    {
        // Tablet
        $media_queries['pac_dth_min_768_max_980'] = '@media only screen and (min-width: 768px) and (max-width: 980px)';
        // Phone
        $media_queries['pac_dth_max_767'] = '@media only screen and (max-width: 767px)';
        // Tablet & Phone
        $media_queries['pac_dth_max_980'] = '@media only screen and (max-width: 980px)';

        return $media_queries;
    }

    add_filter('et_builder_media_queries', 'pac_dth_maybe_custom_media_queries');
endif;
/**
 * Process Multiple Checkboxs
 *
 * @param $checkboxes
 * @param $array_to_match
 *
 * @return array
 */
if (!function_exists('pac_dth_process_multiple_checkboxes')):
    function pac_dth_process_multiple_checkboxes($checkboxes, $array_to_match)
    {
        $processed_data = [];
        $checkboxes = explode('|', $checkboxes);
        foreach ($checkboxes as $key => $val) {
            if (isset($array_to_match[$key]) && 'on' === strtolower($val)) {
                $processed_data[] = $array_to_match[$key];
                // array_push($processed_data, $array_to_match[$key]);
            }
        }

        return $processed_data;
    }
endif;
/**
 * Get Margin,Padding,Border Props
 *
 * @param $props
 * @param $top
 * @param $right
 * @param $bottom
 * @param $left
 *
 * @return string[]
 */
if (!function_exists('pac_dth_get_mpb_props')):
    function pac_dth_get_mpb_props($props, $top = '0', $right = '0', $bottom = '0', $left = '0')
    {
        $responsive_props = ['desktop' => '', 'tablet' => '', 'phone' => ''];
        $default_values = [0 => $top, 1 => $right, 2 => $bottom, 3 => $left];
        foreach ($props as $device => $prop) {
            if (!empty($prop)) {
                $prop_array = explode('|', $prop);
                foreach ($prop_array as $key => $value) {
                    if (!in_array($value, ['true', 'false', 'on', 'off'])) {
                        $prop_array[$key] = !empty($value) ? $value : (isset($default_values[$key]) ? $default_values[$key] : 0);
                    } else {
                        unset($prop_array[$key]);
                    }
                }
                $responsive_props[$device] = implode(' ', $prop_array);
            } else {
                $responsive_props[$device] = '';
            }
        }

        return $responsive_props;
    }
endif;
/**
 * Get Flex Alignments
 *
 * @param array $alignment
 *
 * @return string[]
 */
if (!function_exists('pac_dth_get_flex_alignment')):
    function pac_dth_get_flex_alignment(array $alignment)
    {
        $default = '';

        return array_map(
            function ($v) use (&$default) {
                if (empty($v)) {
                    $v = $default;
                }
                $default = $v;
                if ($v == 'center') {
                    $v = 'center';
                } elseif ($v == 'right') {
                    $v = 'flex-end';
                } else {
                    $v = 'flex-start';
                }

                return $v;
            },
            $alignment
        );
    }
endif;
/**
 * Get Colors
 *
 * @param $devices
 *
 * @return array
 */
if (!function_exists('pac_dth_get_global_color')):
    function pac_dth_get_global_color($devices)
    {
        $global_colors = [];
        foreach ($devices as $device => $value) {
            if (!empty($value)) {
                if (strpos($value, 'gcid-') !== false) {
                    $global_color_info = et_builder_get_global_color_info($value);
                    $global_colors[$device] = esc_attr($global_color_info['color']);
                } else {
                    $global_colors[$device] = $value;
                }
            }

        }

        return $global_colors;
    }
endif;