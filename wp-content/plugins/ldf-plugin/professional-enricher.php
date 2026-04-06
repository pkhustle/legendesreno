<?php
/**
 * Professional AI Enrichment Plugin
 * 
 * Enriches professional posts with:
 * - City extraction from address
 * - Google Places photos
 * - Review ratings from Site Reviews
 * - AI-generated business description
 */

if (!defined('ABSPATH')) {
    exit;
}

class LDF_Professional_Enricher {
    
    // ACF Field Names - Adjust these if your field names differ
    const ACF_ADDRESS = 'full-address';
    const ACF_CITY = 'city';
    const ACF_PHOTOS = 'image_gallery';
    const ACF_PLACE_ID = 'place_id';
    const ACF_QUALITY_AVG = 'average_rating';
    const ACF_TIMELINESS_AVG = 'timeliness_avg';
    const ACF_PROFESSIONALISM_AVG = 'professionalism_avg';
    const ACF_VALUE_AVG = 'value_for_money_avg';
    
    // Site Reviews custom field names
    const REVIEW_QUALITY = 'quality_of_work';
    const REVIEW_TIMELINESS = 'timeliness';
    const REVIEW_PROFESSIONALISM = 'professionalism';
    const REVIEW_VALUE = 'value_for_money';
    
    private $gemini_api_key;
    private $google_api_key;
    
    public function __construct() {
        $this->google_api_key = defined('GOOGLE_PLACES_API_KEY') ? GOOGLE_PLACES_API_KEY : '';
        $this->gemini_api_key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
        
        // Admin hooks
        add_action('admin_footer-edit.php', array($this, 'add_bulk_action_js'));
        add_action('admin_action_enrich_professionals', array($this, 'handle_bulk_action'));
        add_filter('bulk_actions-edit-professional', array($this, 'add_bulk_action'));
        add_filter('post_row_actions', array($this, 'add_row_action'), 10, 2);
        
        // AJAX handlers
        add_action('wp_ajax_ldf_enrich_professional', array($this, 'ajax_enrich_professional'));
        add_action('wp_ajax_ldf_enrich_bulk', array($this, 'ajax_enrich_bulk'));
        
        // Meta box
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
    }
    
    /**
     * Add bulk action dropdown option
     */
    public function add_bulk_action($bulk_actions) {
        $bulk_actions['enrich_professionals'] = __('Enrich with AI', 'ldf-plugin');
        return $bulk_actions;
    }
    
    /**
     * Add row action link
     */
    public function add_row_action($actions, $post) {
        if ($post->post_type === 'professional') {
            $actions['enrich'] = sprintf(
                '<a href="#" class="ldf-enrich-single" data-post-id="%d">%s</a>',
                $post->ID,
                __('Enrich', 'ldf-plugin')
            );
        }
        return $actions;
    }
    
    /**
     * Add meta box to professional edit screen
     */
    public function add_meta_box() {
        add_meta_box(
            'ldf_enrich_box',
            __('AI Enrichment', 'ldf-plugin'),
            array($this, 'render_meta_box'),
            'professional',
            'side',
            'default'
        );
    }
    
    /**
     * Render meta box content
     */
    public function render_meta_box($post) {
        wp_nonce_field('ldf_enrich_single', 'ldf_enrich_nonce');
        ?>
        <div id="ldf-enrich-status"></div>
        <button type="button" class="button button-primary button-large" id="ldf-enrich-single-btn" style="width:100%;">
            <?php _e('Enrich with AI', 'ldf-plugin'); ?>
        </button>
        <p class="description">
            <?php _e('Fetches data from Google Places, extracts review ratings, and generates an AI description.', 'ldf-plugin'); ?>
        </p>
        
        <script>
        jQuery(document).ready(function($) {
            $('#ldf-enrich-single-btn').on('click', function() {
                var $btn = $(this);
                var $status = $('#ldf-enrich-status');
                
                $btn.prop('disabled', true).text('<?php _e('Enriching...', 'ldf-plugin'); ?>');
                $status.html('<div class="notice notice-info"><p><?php _e('Processing...', 'ldf-plugin'); ?></p></div>');
                
                $.post(ajaxurl, {
                    action: 'ldf_enrich_professional',
                    post_id: <?php echo $post->ID; ?>,
                    nonce: $('#ldf_enrich_nonce').val()
                }, function(response) {
                    if (response.success) {
                        $status.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $status.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                        $btn.prop('disabled', false).text('<?php _e('Enrich with AI', 'ldf-plugin'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Handle bulk action redirect
     */
    public function handle_bulk_action() {
        if (isset($_REQUEST['post']) && is_array($_REQUEST['post'])) {
            $post_ids = array_map('intval', $_REQUEST['post']);
            $redirect_url = add_query_arg(array(
                'ldf_bulk_enrich' => '1',
                'post_ids' => implode(',', $post_ids)
            ), admin_url('edit.php?post_type=professional'));
            
            wp_redirect($redirect_url);
            exit;
        }
    }
    
    /**
     * Add JavaScript for bulk action processing
     */
    public function add_bulk_action_js() {
        global $post_type;
        if ($post_type !== 'professional') return;
        
        // Check if bulk enrich was triggered
        if (isset($_GET['ldf_bulk_enrich']) && isset($_GET['post_ids'])) {
            $post_ids = array_map('intval', explode(',', sanitize_text_field($_GET['post_ids'])));
            ?>
            <div id="ldf-bulk-progress" class="notice notice-info" style="position:relative; padding:20px;">
                <h3><?php _e('Enriching Professionals...', 'ldf-plugin'); ?></h3>
                <div id="ldf-progress-bar" style="background:#ccc; height:20px; border-radius:3px; overflow:hidden;">
                    <div id="ldf-progress-fill" style="background:#2271b1; height:100%; width:0%; transition:width 0.3s;"></div>
                </div>
                <p id="ldf-progress-text">0 / <?php echo count($post_ids); ?></p>
                <div id="ldf-progress-details" style="max-height:200px; overflow-y:auto; margin-top:10px;"></div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                var postIds = <?php echo json_encode($post_ids); ?>;
                var total = postIds.length;
                var completed = 0;
                
                function enrichNext(index) {
                    if (index >= total) {
                        $('#ldf-bulk-progress').removeClass('notice-info').addClass('notice-success');
                        $('#ldf-bulk-progress h3').text('<?php _e('Enrichment Complete!', 'ldf-plugin'); ?>');
                        setTimeout(function() {
                            window.location.href = '<?php echo admin_url('edit.php?post_type=professional'); ?>';
                        }, 2000);
                        return;
                    }
                    
                    var postId = postIds[index];
                    
                    $.post(ajaxurl, {
                        action: 'ldf_enrich_professional',
                        post_id: postId,
                        nonce: '<?php echo wp_create_nonce('ldf_enrich_professional'); ?>'
                    }, function(response) {
                        completed++;
                        var percent = Math.round((completed / total) * 100);
                        $('#ldf-progress-fill').css('width', percent + '%');
                        $('#ldf-progress-text').text(completed + ' / ' + total);
                        
                        var status = response.success ? '✓' : '✗';
                        var className = response.success ? 'success' : 'error';
                        var message = response.data.message || (response.success ? 'Success' : 'Failed');
                        
                        $('#ldf-progress-details').append(
                            '<div style="color:' + (response.success ? 'green' : 'red') + ';">' +
                            status + ' Post #' + postId + ': ' + message +
                            '</div>'
                        );
                        
                        enrichNext(index + 1);
                    }).fail(function() {
                        completed++;
                        $('#ldf-progress-details').append(
                            '<div style="color:red;">✗ Post #' + postId + ': Network error</div>'
                        );
                        enrichNext(index + 1);
                    });
                }
                
                enrichNext(0);
            });
            </script>
            <?php
        }
        
        // Add click handler for row actions
        ?>
        <script>
        jQuery(document).ready(function($) {
            $(document).on('click', '.ldf-enrich-single', function(e) {
                e.preventDefault();
                var postId = $(this).data('post-id');
                var $link = $(this);
                
                if (!confirm('<?php _e('Enrich this professional with AI data?', 'ldf-plugin'); ?>')) {
                    return;
                }
                
                $link.text('<?php _e('Enriching...', 'ldf-plugin'); ?>');
                
                $.post(ajaxurl, {
                    action: 'ldf_enrich_professional',
                    post_id: postId,
                    nonce: '<?php echo wp_create_nonce('ldf_enrich_professional'); ?>'
                }, function(response) {
                    if (response.success) {
                        $link.text('✓ ' + response.data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $link.text('✗ ' + response.data.message);
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for single professional enrichment
     */
    public function ajax_enrich_professional() {
        check_ajax_referer('ldf_enrich_professional', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ldf-plugin')));
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id || get_post_type($post_id) !== 'professional') {
            wp_send_json_error(array('message' => __('Invalid post', 'ldf-plugin')));
        }
        
        $result = $this->enrich_professional($post_id);
        
        if ($result['success']) {
            wp_send_json_success(array('message' => $result['message']));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    /**
     * Main enrichment function
     */
    private function enrich_professional($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return array('success' => false, 'message' => 'Post not found');
        }
        
        $updated = array();
        $errors = array();
        
        // 1. Get or fetch Place ID from Google Places
        $place_id = get_field(self::ACF_PLACE_ID, $post_id);
        
        if (empty($place_id)) {
            $result = $this->fetch_place_id($post_id);
            if ($result['success']) {
                $place_id = $result['place_id'];
                update_field(self::ACF_PLACE_ID, $place_id, $post_id);
                $updated[] = 'Place ID';
            } else {
                $errors[] = 'Place ID: ' . $result['message'];
            }
        }
        
        // 2. Fetch Google Places details if we have a Place ID
        $google_data = null;
        if (!empty($place_id)) {
            $result = $this->fetch_place_details($place_id);
            if ($result['success']) {
                $google_data = $result['data'];
                
                // Extract city
                if (!empty($google_data['city'])) {
                    update_field(self::ACF_CITY, $google_data['city'], $post_id);
                    $updated[] = 'City';
                }
                
                // Set featured image if none exists
                if (!has_post_thumbnail($post_id) && !empty($google_data['photos'][0])) {
                    $attachment_id = $this->upload_google_photo($google_data['photos'][0], $post_id);
                    if ($attachment_id) {
                        set_post_thumbnail($post_id, $attachment_id);
                        $updated[] = 'Featured Image';
                    }
                }
                
                // Add photos to gallery
                $existing_photos = get_field(self::ACF_PHOTOS, $post_id);
                if (empty($existing_photos) && !empty($google_data['photos'])) {
                    $photo_ids = array();
                    foreach (array_slice($google_data['photos'], 0, 5) as $photo_ref) {
                        $attachment_id = $this->upload_google_photo($photo_ref, $post_id);
                        if ($attachment_id) {
                            $photo_ids[] = $attachment_id;
                        }
                    }
                    if (!empty($photo_ids)) {
                        update_field(self::ACF_PHOTOS, $photo_ids, $post_id);
                        $updated[] = 'Photos (' . count($photo_ids) . ')';
                    }
                }
            } else {
                $errors[] = 'Google data: ' . $result['message'];
            }
        }
        
        // 3. Extract review ratings from Site Reviews
        if (function_exists('glsr_get_reviews')) {
            $result = $this->extract_review_ratings($post_id);
            if ($result['success']) {
                foreach ($result['ratings'] as $field => $value) {
                    update_field($field, $value, $post_id);
                }
                $updated[] = 'Review Ratings';
            }
        }
        
        // 4. Generate AI description
        if (empty($post->post_content) || strlen(trim($post->post_content)) < 50) {
            $result = $this->generate_description($post_id, $google_data);
            if ($result['success']) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_content' => $result['content']
                ));
                $updated[] = 'AI Description';
            } else {
                $errors[] = 'Description: ' . $result['message'];
            }
        }
        
        // Build result message
        $message = '';
        if (!empty($updated)) {
            $message .= 'Updated: ' . implode(', ', $updated);
        }
        if (!empty($errors)) {
            $message .= (empty($updated) ? '' : '. ') . 'Errors: ' . implode(', ', $errors);
        }
        
        return array(
            'success' => !empty($updated),
            'message' => !empty($message) ? $message : 'No updates needed'
        );
    }
    
    /**
     * Fetch Place ID from Google Places Text Search
     */
    private function fetch_place_id($post_id) {
        if (empty($this->google_api_key)) {
            return array('success' => false, 'message' => 'No Google API key');
        }
        
        $post = get_post($post_id);
        $address = get_field(self::ACF_ADDRESS, $post_id);
        
        if (empty($address)) {
            return array('success' => false, 'message' => 'No address');
        }
        
        $query = trim($post->post_title . ' ' . $address);
        
        $url = add_query_arg(array(
            'query' => $query,
            'key' => $this->google_api_key
        ), 'https://maps.googleapis.com/maps/api/place/textsearch/json');
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return array('success' => false, 'message' => "HTTP Error {$response_code}");
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array('success' => false, 'message' => 'Invalid JSON from Google API');
        }
        
        if (isset($body['status']) && $body['status'] !== 'OK') {
            $err_msg = isset($body['error_message']) ? ' - ' . $body['error_message'] : '';
            return array('success' => false, 'message' => 'API Status: ' . $body['status'] . $err_msg);
        }
        
        if (!empty($body['results'][0]['place_id'])) {
            return array(
                'success' => true,
                'place_id' => $body['results'][0]['place_id']
            );
        }
        
        return array('success' => false, 'message' => 'Not found in Google Places');
    }
    
    /**
     * Fetch Place Details from Google Places API
     */
    private function fetch_place_details($place_id) {
        if (empty($this->google_api_key)) {
            return array('success' => false, 'message' => 'No Google API key');
        }
        
        $url = add_query_arg(array(
            'place_id' => $place_id,
            'fields' => 'name,formatted_address,address_components,photos,reviews,rating',
            'key' => $this->google_api_key
        ), 'https://maps.googleapis.com/maps/api/place/details/json');
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return array('success' => false, 'message' => "HTTP Error {$response_code}");
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array('success' => false, 'message' => 'Invalid JSON from Google API');
        }
        
        if (isset($body['status']) && $body['status'] !== 'OK') {
            $err_msg = isset($body['error_message']) ? ' - ' . $body['error_message'] : '';
            return array('success' => false, 'message' => 'API Status: ' . $body['status'] . $err_msg);
        }
        
        if (empty($body['result'])) {
            return array('success' => false, 'message' => 'No details found');
        }
        
        $result = $body['result'];
        $data = array();
        
        // Extract city from address_components
        if (!empty($result['address_components'])) {
            $city_types = array('locality', 'postal_town', 'sublocality_level_1', 'administrative_area_level_3');
            foreach ($city_types as $type) {
                foreach ($result['address_components'] as $component) {
                    if (in_array($type, $component['types'])) {
                        $data['city'] = $component['long_name'];
                        break 2;
                    }
                }
            }
        }
        
        // Get photo references
        if (!empty($result['photos'])) {
            $data['photos'] = array();
            foreach ($result['photos'] as $photo) {
                if (!empty($photo['photo_reference'])) {
                    $data['photos'][] = $photo['photo_reference'];
                }
            }
        }
        
        // Get reviews
        if (!empty($result['reviews'])) {
            $data['reviews'] = $result['reviews'];
        }
        
        $data['rating'] = $result['rating'] ?? null;
        
        return array('success' => true, 'data' => $data);
    }
    
    /**
     * Upload Google Places photo to WordPress media library
     */
    private function upload_google_photo($photo_reference, $post_id) {
        if (empty($this->google_api_key)) {
            return false;
        }
        
        $url = add_query_arg(array(
            'photoreference' => $photo_reference,
            'maxwidth' => 1200,
            'key' => $this->google_api_key
        ), 'https://maps.googleapis.com/maps/api/place/photo');
        
        // Download image
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200 && $response_code !== 302) {
            return false;
        }
        
        $content_type = wp_remote_retrieve_header($response, 'content-type');
        if (strpos($content_type, 'image/') === false) {
            return false;
        }
        
        $image_data = wp_remote_retrieve_body($response);
        
        if (empty($image_data)) {
            return false;
        }
        
        // Upload to media library
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $upload_dir = wp_upload_dir();
        if (wp_mkdir_p($upload_dir['path']) === false) {
            return false; // Cannot create/verify upload directory
        }
        
        $safe_ref = md5($photo_reference . time());
        $filename = 'google-place-' . $safe_ref . '.jpg';
        $filepath = wp_unique_filename($upload_dir['path'], $filename);
        $full_filepath = $upload_dir['path'] . '/' . $filepath;
        
        if (file_put_contents($full_filepath, $image_data) === false) {
            return false; // Failed to write file
        }
        
        $filetype = wp_check_filetype($filename, null);
        
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $full_filepath, $post_id);
        
        if (!is_wp_error($attachment_id)) {
            $attach_data = wp_generate_attachment_metadata($attachment_id, $full_filepath);
            wp_update_attachment_metadata($attachment_id, $attach_data);
            return $attachment_id;
        }
        
        return false;
    }
    
    /**
     * Extract review ratings from Site Reviews plugin
     */
    private function extract_review_ratings($post_id) {
        $reviews = glsr_get_reviews(array(
            'assigned_posts' => $post_id,
            'per_page' => 100
        ));
        
        if (empty($reviews->reviews)) {
            return array('success' => false, 'message' => 'No reviews found');
        }
        
        $totals = array(
            self::REVIEW_QUALITY => array(),
            self::REVIEW_TIMELINESS => array(),
            self::REVIEW_PROFESSIONALISM => array(),
            self::REVIEW_VALUE => array()
        );
        
        foreach ($reviews->reviews as $review) {
            // Get custom field ratings
            foreach ($totals as $field => $values) {
                $rating = get_post_meta($review->ID, '_custom_' . $field, true);
                if (!empty($rating) && is_numeric($rating)) {
                    $totals[$field][] = floatval($rating);
                }
            }
        }
        
        $ratings = array();
        if (!empty($totals[self::REVIEW_QUALITY])) {
            $ratings[self::ACF_QUALITY_AVG] = round(array_sum($totals[self::REVIEW_QUALITY]) / count($totals[self::REVIEW_QUALITY]), 1);
        }
        if (!empty($totals[self::REVIEW_TIMELINESS])) {
            $ratings[self::ACF_TIMELINESS_AVG] = round(array_sum($totals[self::REVIEW_TIMELINESS]) / count($totals[self::REVIEW_TIMELINESS]), 1);
        }
        if (!empty($totals[self::REVIEW_PROFESSIONALISM])) {
            $ratings[self::ACF_PROFESSIONALISM_AVG] = round(array_sum($totals[self::REVIEW_PROFESSIONALISM]) / count($totals[self::REVIEW_PROFESSIONALISM]), 1);
        }
        if (!empty($totals[self::REVIEW_VALUE])) {
            $ratings[self::ACF_VALUE_AVG] = round(array_sum($totals[self::REVIEW_VALUE]) / count($totals[self::REVIEW_VALUE]), 1);
        }
        
        return array('success' => true, 'ratings' => $ratings);
    }
    
    /**
     * Generate AI description using Gemini API
     */
    private function generate_description($post_id, $google_data = null) {
        if (empty($this->gemini_api_key)) {
            return array('success' => false, 'message' => 'Gemini API Key not available');
        }
        
        $post = get_post($post_id);
        $city = get_field(self::ACF_CITY, $post_id);
        $address = get_field(self::ACF_ADDRESS, $post_id);
        
        // Get taxonomy terms (specialties)
        $specialties = wp_get_post_terms($post_id, 'specialty-type', array('fields' => 'names'));
        $specialty_text = !empty($specialties) ? implode(', ', $specialties) : '';
        
        // Get review ratings
        $quality = get_field(self::ACF_QUALITY_AVG, $post_id);
        $timeliness = get_field(self::ACF_TIMELINESS_AVG, $post_id);
        $professionalism = get_field(self::ACF_PROFESSIONALISM_AVG, $post_id);
        $value = get_field(self::ACF_VALUE_AVG, $post_id);
        
        // Build context prompt
        $description = $post->post_title;
        if (!empty($specialty_text)) {
            $description .= " specializes in " . $specialty_text;
        }
        if (!empty($city)) {
            $description .= " located in " . $city;
        }
        if (!empty($address)) {
            $description .= " at " . $address;
        }
        
        // Add ratings context
        $ratings_text = '';
        if (!empty($quality)) $ratings_text .= "Quality of Work: {$quality}/5. ";
        if (!empty($timeliness)) $ratings_text .= "Timeliness: {$timeliness}/5. ";
        if (!empty($professionalism)) $ratings_text .= "Professionalism: {$professionalism}/5. ";
        if (!empty($value)) $ratings_text .= "Value for Money: {$value}/5. ";
        
        if (!empty($ratings_text)) {
            $description .= ". Customer ratings: " . $ratings_text;
        }
        
        // Add Google reviews context if available
        if (!empty($google_data['reviews'])) {
            $review_snippets = array();
            foreach (array_slice($google_data['reviews'], 0, 3) as $review) {
                if (!empty($review['text'])) {
                    $review_snippets[] = substr($review['text'], 0, 150);
                }
            }
            if (!empty($review_snippets)) {
                $description .= " Reviews highlight: " . implode('. ', $review_snippets);
            }
        }
        
        $prompt = "Rédigez une description d'entreprise professionnelle de 150 à 300 mots, entièrement en FRANÇAIS, pour le professionnel ou l'entreprise suivante en fonction de ce contexte. N'utilisez pas de formules de politesse ou d'introduction, fournissez uniquement le texte de la description : \n\n" . $description;
        
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $this->gemini_api_key;
        
        $payload = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $prompt)
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.7
            )
        );
        
        $args = array(
            'body'        => json_encode($payload),
            'timeout'     => 20,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(
                'Content-Type' => 'application/json',
            ),
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        
        if ($response_code === 200 && !empty($data['candidates'][0]['content']['parts'][0]['text'])) {
            return array(
                'success' => true,
                'content' => trim($data['candidates'][0]['content']['parts'][0]['text'])
            );
        }
        
        $err_msg = isset($data['error']['message']) ? $data['error']['message'] : 'API returned no content';
        return array('success' => false, 'message' => 'Gemini API Error: ' . $err_msg);
    }
}

// Initialize the enricher
new LDF_Professional_Enricher();
