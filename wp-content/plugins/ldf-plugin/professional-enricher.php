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

    // Email/pipeline meta and settings
    const OPTION_GROUP = 'ldf_professional_email_settings_group';
    const OPTION_NAME = 'ldf_professional_email_settings';
    const META_EMAIL_SENT = '_ldf_email_sent';
    const META_EMAIL_SENT_AT = '_ldf_email_sent_at';
    const META_EMAIL_SENT_TO = '_ldf_email_sent_to';
    const META_EMAIL_LAST_ERROR = '_ldf_email_last_error';
    const META_PIPELINE_STAGE = '_ldf_pipeline_stage';

    const DEFAULT_EMAIL_FIELD_KEY = 'email';
    
    // Site Reviews custom field names
    const REVIEW_QUALITY = 'quality_of_work';
    const REVIEW_TIMELINESS = 'timeliness';
    const REVIEW_PROFESSIONALISM = 'professionalism';
    const REVIEW_VALUE = 'value_for_money';
    
    private $gemini_api_key;
    private $google_api_key;

    private $pipeline_stages = array('New', 'Contacted', 'Follow-up', 'Qualified', 'Won', 'Lost');
    
    public function __construct() {
        $this->google_api_key = defined('GOOGLE_PLACES_API_KEY') ? GOOGLE_PLACES_API_KEY : '';
        $this->gemini_api_key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
        
        // Admin hooks
        add_action('admin_footer-edit.php', array($this, 'add_bulk_action_js'));
        add_action('admin_action_enrich_professionals', array($this, 'handle_bulk_action'));
        add_action('admin_action_ldf_send_professional_emails', array($this, 'handle_bulk_email_action'));
        add_filter('bulk_actions-edit-professional', array($this, 'add_bulk_action'));
        add_filter('post_row_actions', array($this, 'add_row_action'), 10, 2);
        add_filter('manage_professional_posts_columns', array($this, 'add_admin_columns'));
        add_action('manage_professional_posts_custom_column', array($this, 'render_admin_columns'), 10, 2);
        
        // AJAX handlers
        add_action('wp_ajax_ldf_enrich_professional', array($this, 'ajax_enrich_professional'));
        add_action('wp_ajax_ldf_send_professional_email', array($this, 'ajax_send_professional_email'));
        add_action('wp_ajax_ldf_preview_professional_email', array($this, 'ajax_preview_professional_email'));
        
        // Meta box
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post_professional', array($this, 'save_professional_meta'));

        // Settings / board
        add_action('admin_menu', array($this, 'register_admin_pages'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add bulk action dropdown option
     */
    public function add_bulk_action($bulk_actions) {
        $bulk_actions['enrich_professionals'] = __('Enrich with AI', 'ldf-plugin');
        $bulk_actions['ldf_send_professional_emails'] = __('Send predefined email', 'ldf-plugin');
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
            $actions['ldf_send_email'] = sprintf(
                '<a href="#" class="ldf-send-email-single" data-post-id="%d">%s</a>',
                $post->ID,
                __('Send Email', 'ldf-plugin')
            );
        }
        return $actions;
    }

    /**
     * Add admin columns to the professional list table
     */
    public function add_admin_columns($columns) {
        $new_columns = array();

        foreach ($columns as $key => $label) {
            $new_columns[$key] = $label;

            if ($key === 'title') {
                $new_columns['ldf_pipeline_stage'] = __('Pipeline Stage', 'ldf-plugin');
                $new_columns['ldf_email_sent'] = __('Email Sent', 'ldf-plugin');
            }
        }

        return $new_columns;
    }

    /**
     * Render admin columns content
     */
    public function render_admin_columns($column, $post_id) {
        if ($column === 'ldf_pipeline_stage') {
            echo esc_html($this->get_pipeline_stage($post_id));
            return;
        }

        if ($column === 'ldf_email_sent') {
            $sent = get_post_meta($post_id, self::META_EMAIL_SENT, true);
            $sent_at = get_post_meta($post_id, self::META_EMAIL_SENT_AT, true);
            $sent_to = get_post_meta($post_id, self::META_EMAIL_SENT_TO, true);
            $error = get_post_meta($post_id, self::META_EMAIL_LAST_ERROR, true);

            if ($sent) {
                echo '<strong style="color:green;">' . esc_html__('Yes', 'ldf-plugin') . '</strong>';
                if (!empty($sent_at)) {
                    echo '<br><small>' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), intval($sent_at))) . '</small>';
                }
                if (!empty($sent_to)) {
                    echo '<br><small>' . esc_html($sent_to) . '</small>';
                }
            } else {
                echo '<span style="color:#666;">' . esc_html__('No', 'ldf-plugin') . '</span>';
                if (!empty($error)) {
                    echo '<br><small style="color:#b32d2e;">' . esc_html($error) . '</small>';
                }
            }
        }
    }

    /**
     * Register admin pages under Professionals
     */
    public function register_admin_pages() {
        add_submenu_page(
            'edit.php?post_type=professional',
            __('Professional Email Settings', 'ldf-plugin'),
            __('Email Settings', 'ldf-plugin'),
            'manage_options',
            'ldf-professional-email-settings',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'edit.php?post_type=professional',
            __('Professional Pipeline Board', 'ldf-plugin'),
            __('Pipeline Board', 'ldf-plugin'),
            'edit_posts',
            'ldf-professional-pipeline-board',
            array($this, 'render_pipeline_board_page')
        );
    }

    /**
     * Register settings for the predefined email template
     */
    public function register_settings() {
        register_setting(
            self::OPTION_GROUP,
            self::OPTION_NAME,
            array($this, 'sanitize_settings')
        );

        add_settings_section(
            'ldf_professional_email_main',
            __('Predefined Email Template', 'ldf-plugin'),
            '__return_false',
            'ldf-professional-email-settings'
        );

        add_settings_field(
            'email_field_key',
            __('Email Field Key', 'ldf-plugin'),
            array($this, 'render_email_field_key_field'),
            'ldf-professional-email-settings',
            'ldf_professional_email_main'
        );

        add_settings_field(
            'subject',
            __('Email Subject', 'ldf-plugin'),
            array($this, 'render_subject_field'),
            'ldf-professional-email-settings',
            'ldf_professional_email_main'
        );

        add_settings_field(
            'body',
            __('Email Body', 'ldf-plugin'),
            array($this, 'render_body_field'),
            'ldf-professional-email-settings',
            'ldf_professional_email_main'
        );
    }

    /**
     * Sanitize settings before save
     */
    public function sanitize_settings($input) {
        return array(
            'email_field_key' => sanitize_key($input['email_field_key'] ?? self::DEFAULT_EMAIL_FIELD_KEY),
            'subject' => sanitize_text_field($input['subject'] ?? ''),
            'body' => wp_kses_post($input['body'] ?? ''),
        );
    }

    public function render_email_field_key_field() {
        $settings = $this->get_email_settings();
        ?>
        <input type="text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[email_field_key]" value="<?php echo esc_attr($settings['email_field_key']); ?>" class="regular-text" />
        <p class="description"><?php esc_html_e('ACF/meta field key that contains the recipient email on professional posts.', 'ldf-plugin'); ?></p>
        <?php
    }

    public function render_subject_field() {
        $settings = $this->get_email_settings();
        ?>
        <input type="text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[subject]" value="<?php echo esc_attr($settings['subject']); ?>" class="regular-text" />
        <p class="description"><?php esc_html_e('Available placeholders: {name}, {stage}, {site_name}', 'ldf-plugin'); ?></p>
        <?php
    }

    public function render_body_field() {
        $settings = $this->get_email_settings();
        ?>
        <textarea name="<?php echo esc_attr(self::OPTION_NAME); ?>[body]" rows="10" class="large-text"><?php echo esc_textarea($settings['body']); ?></textarea>
        <p class="description"><?php esc_html_e('Available placeholders: {name}, {stage}, {site_name}', 'ldf-plugin'); ?></p>
        <?php
    }

    public function render_settings_page() {
        $sample_professionals = get_posts(array(
            'post_type' => 'professional',
            'post_status' => array('publish', 'draft', 'pending', 'private'),
            'numberposts' => 50,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Professional Email Settings', 'ldf-plugin'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_GROUP);
                do_settings_sections('ldf-professional-email-settings');
                submit_button();
                ?>
            </form>

            <hr style="margin:24px 0;">
            <h2><?php esc_html_e('Email Preview', 'ldf-plugin'); ?></h2>
            <p><?php esc_html_e('Preview how the email will render for a specific professional before sending it.', 'ldf-plugin'); ?></p>
            <p>
                <select id="ldf-settings-preview-post" style="min-width:280px;">
                    <option value=""><?php esc_html_e('Select a professional', 'ldf-plugin'); ?></option>
                    <?php foreach ($sample_professionals as $professional) : ?>
                        <option value="<?php echo esc_attr($professional->ID); ?>"><?php echo esc_html(get_the_title($professional->ID)); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="button button-secondary" id="ldf-settings-preview-btn"><?php esc_html_e('Preview Email', 'ldf-plugin'); ?></button>
            </p>
            <div id="ldf-settings-preview-output" style="display:none; background:#fff; border:1px solid #dcdcde; border-radius:8px; padding:16px; max-width:900px;">
                <p><strong><?php esc_html_e('Recipient:', 'ldf-plugin'); ?></strong> <span id="ldf-settings-preview-recipient"></span></p>
                <p><strong><?php esc_html_e('Subject:', 'ldf-plugin'); ?></strong> <span id="ldf-settings-preview-subject"></span></p>
                <div>
                    <strong><?php esc_html_e('Body:', 'ldf-plugin'); ?></strong>
                    <div id="ldf-settings-preview-body" style="margin-top:8px; padding:12px; background:#f6f7f7; border-radius:6px;"></div>
                </div>
            </div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#ldf-settings-preview-btn').on('click', function() {
                var postId = $('#ldf-settings-preview-post').val();

                if (!postId) {
                    alert('<?php echo esc_js(__('Please select a professional to preview.', 'ldf-plugin')); ?>');
                    return;
                }

                $.post(ajaxurl, {
                    action: 'ldf_preview_professional_email',
                    post_id: postId,
                    nonce: '<?php echo wp_create_nonce('ldf_preview_professional_email'); ?>'
                }, function(response) {
                    if (!response.success) {
                        alert(response.data.message || '<?php echo esc_js(__('Unable to preview email.', 'ldf-plugin')); ?>');
                        return;
                    }

                    $('#ldf-settings-preview-recipient').text(response.data.recipient || '—');
                    $('#ldf-settings-preview-subject').text(response.data.subject || '—');
                    $('#ldf-settings-preview-body').html(response.data.body || '<em>—</em>');
                    $('#ldf-settings-preview-output').show();
                });
            });
        });
        </script>
        <?php
    }

    public function render_pipeline_board_page() {
        $professionals = get_posts(array(
            'post_type' => 'professional',
            'post_status' => array('publish', 'draft', 'pending', 'private'),
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        $grouped = array_fill_keys($this->pipeline_stages, array());

        foreach ($professionals as $professional) {
            $stage = $this->get_pipeline_stage($professional->ID);
            if (!isset($grouped[$stage])) {
                $grouped[$stage] = array();
            }
            $grouped[$stage][] = $professional;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Professional Pipeline Board', 'ldf-plugin'); ?></h1>
            <p><?php esc_html_e('Kanban view of professionals grouped by pipeline stage.', 'ldf-plugin'); ?></p>
            <div style="display:flex; gap:16px; align-items:flex-start; overflow-x:auto; padding-top:12px;">
                <?php foreach ($this->pipeline_stages as $stage) : ?>
                    <div style="min-width:260px; background:#f6f7f7; border:1px solid #dcdcde; border-radius:8px; padding:12px;">
                        <h2 style="margin-top:0; font-size:16px;"><?php echo esc_html($stage); ?> <span style="color:#666; font-weight:normal;">(<?php echo count($grouped[$stage]); ?>)</span></h2>
                        <?php if (empty($grouped[$stage])) : ?>
                            <p style="color:#666;"><?php esc_html_e('No professionals', 'ldf-plugin'); ?></p>
                        <?php else : ?>
                            <?php foreach ($grouped[$stage] as $professional) : ?>
                                <?php $sent = get_post_meta($professional->ID, self::META_EMAIL_SENT, true); ?>
                                <div style="background:#fff; border:1px solid #dcdcde; border-radius:6px; padding:10px; margin-bottom:10px; box-shadow:0 1px 1px rgba(0,0,0,.04);">
                                    <strong><a href="<?php echo esc_url(get_edit_post_link($professional->ID)); ?>"><?php echo esc_html(get_the_title($professional->ID)); ?></a></strong>
                                    <div style="margin-top:6px; color:#666; font-size:12px;">
                                        <?php echo $sent ? '<span style="color:green;">' . esc_html__('Email sent', 'ldf-plugin') . '</span>' : esc_html__('Email not sent', 'ldf-plugin'); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
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
        wp_nonce_field('ldf_professional_meta', 'ldf_professional_meta_nonce');
        $current_stage = $this->get_pipeline_stage($post->ID);
        $sent = get_post_meta($post->ID, self::META_EMAIL_SENT, true);
        $sent_at = get_post_meta($post->ID, self::META_EMAIL_SENT_AT, true);
        $sent_to = get_post_meta($post->ID, self::META_EMAIL_SENT_TO, true);
        ?>
        <p>
            <label for="ldf_pipeline_stage"><strong><?php _e('Pipeline Stage', 'ldf-plugin'); ?></strong></label><br>
            <select name="ldf_pipeline_stage" id="ldf_pipeline_stage" style="width:100%;">
                <?php foreach ($this->pipeline_stages as $stage) : ?>
                    <option value="<?php echo esc_attr($stage); ?>" <?php selected($current_stage, $stage); ?>><?php echo esc_html($stage); ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p class="description" style="margin-bottom:12px;">
            <?php if ($sent) : ?>
                <span style="color:green;"><?php _e('Last email sent', 'ldf-plugin'); ?></span>
                <?php if (!empty($sent_at)) : ?>
                    <br><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), intval($sent_at))); ?>
                <?php endif; ?>
                <?php if (!empty($sent_to)) : ?>
                    <br><?php echo esc_html($sent_to); ?>
                <?php endif; ?>
            <?php else : ?>
                <?php _e('No email sent yet.', 'ldf-plugin'); ?>
            <?php endif; ?>
        </p>

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
     * Save custom professional meta from edit screen
     */
    public function save_professional_meta($post_id) {
        if (!isset($_POST['ldf_professional_meta_nonce']) || !wp_verify_nonce($_POST['ldf_professional_meta_nonce'], 'ldf_professional_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $stage = isset($_POST['ldf_pipeline_stage']) ? sanitize_text_field(wp_unslash($_POST['ldf_pipeline_stage'])) : 'New';
        if (!in_array($stage, $this->pipeline_stages, true)) {
            $stage = 'New';
        }

        update_post_meta($post_id, self::META_PIPELINE_STAGE, $stage);
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
     * Handle bulk email action redirect
     */
    public function handle_bulk_email_action() {
        if (isset($_REQUEST['post']) && is_array($_REQUEST['post'])) {
            $post_ids = array_map('intval', $_REQUEST['post']);
            $redirect_url = add_query_arg(array(
                'ldf_bulk_email_preview' => '1',
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

        if (isset($_GET['ldf_bulk_email_preview']) && isset($_GET['post_ids'])) {
            $post_ids = array_map('intval', explode(',', sanitize_text_field($_GET['post_ids'])));
            $preview_items = array();

            foreach ($post_ids as $post_id) {
                $preview = $this->get_email_preview_data($post_id);
                $preview_items[] = $preview;
            }
            ?>
            <div id="ldf-bulk-email-preview" class="notice notice-info" style="position:relative; padding:20px;">
                <h3><?php _e('Preview Emails Before Sending', 'ldf-plugin'); ?></h3>
                <p><?php _e('Review the rendered emails below, then confirm to send them.', 'ldf-plugin'); ?></p>
                <div style="max-height:360px; overflow-y:auto; background:#fff; border:1px solid #dcdcde; border-radius:6px; padding:12px;">
                    <?php foreach ($preview_items as $item) : ?>
                        <div style="padding:12px 0; border-bottom:1px solid #eee;">
                            <p style="margin:0 0 6px;"><strong><?php echo esc_html($item['name']); ?></strong></p>
                            <p style="margin:0 0 6px;"><strong><?php esc_html_e('Recipient:', 'ldf-plugin'); ?></strong> <?php echo esc_html($item['recipient'] ?: '—'); ?></p>
                            <p style="margin:0 0 6px;"><strong><?php esc_html_e('Subject:', 'ldf-plugin'); ?></strong> <?php echo esc_html($item['subject'] ?: '—'); ?></p>
                            <div style="background:#f6f7f7; border-radius:6px; padding:10px;"><?php echo $item['body'] ? wp_kses_post($item['body']) : '<em>' . esc_html__('No preview available', 'ldf-plugin') . '</em>'; ?></div>
                            <?php if (!$item['can_send'] && !empty($item['message'])) : ?>
                                <p style="color:#b32d2e; margin:8px 0 0;"><?php echo esc_html($item['message']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p style="margin-top:12px;">
                    <button type="button" class="button button-primary" id="ldf-confirm-bulk-email-send"><?php esc_html_e('Confirm and Send', 'ldf-plugin'); ?></button>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=professional')); ?>" class="button"><?php esc_html_e('Cancel', 'ldf-plugin'); ?></a>
                </p>
            </div>

            <div id="ldf-bulk-email-progress" class="notice notice-info" style="position:relative; padding:20px; display:none;">
                <h3><?php _e('Sending Emails...', 'ldf-plugin'); ?></h3>
                <div style="background:#ccc; height:20px; border-radius:3px; overflow:hidden;">
                    <div id="ldf-email-progress-fill" style="background:#2271b1; height:100%; width:0%; transition:width 0.3s;"></div>
                </div>
                <p id="ldf-email-progress-text">0 / <?php echo count($post_ids); ?></p>
                <div id="ldf-email-progress-details" style="max-height:200px; overflow-y:auto; margin-top:10px;"></div>
            </div>
            <script>
            jQuery(document).ready(function($) {
                var postIds = <?php echo json_encode($post_ids); ?>;
                var total = postIds.length;
                var completed = 0;

                function sendNext(index) {
                    if (index >= total) {
                        $('#ldf-bulk-email-progress').removeClass('notice-info').addClass('notice-success');
                        $('#ldf-bulk-email-progress h3').text('<?php _e('Email Sending Complete!', 'ldf-plugin'); ?>');
                        setTimeout(function() {
                            window.location.href = '<?php echo admin_url('edit.php?post_type=professional'); ?>';
                        }, 2000);
                        return;
                    }

                    var postId = postIds[index];

                    $.post(ajaxurl, {
                        action: 'ldf_send_professional_email',
                        post_id: postId,
                        nonce: '<?php echo wp_create_nonce('ldf_send_professional_email'); ?>'
                    }, function(response) {
                        completed++;
                        var percent = Math.round((completed / total) * 100);
                        $('#ldf-email-progress-fill').css('width', percent + '%');
                        $('#ldf-email-progress-text').text(completed + ' / ' + total);

                        var status = response.success ? '✓' : '✗';
                        var message = response.data.message || (response.success ? 'Success' : 'Failed');

                        $('#ldf-email-progress-details').append(
                            '<div style="color:' + (response.success ? 'green' : 'red') + ';">' +
                            status + ' Post #' + postId + ': ' + message +
                            '</div>'
                        );

                        sendNext(index + 1);
                    }).fail(function() {
                        completed++;
                        $('#ldf-email-progress-details').append('<div style="color:red;">✗ Post #' + postId + ': Network error</div>');
                        sendNext(index + 1);
                    });
                }

                $('#ldf-confirm-bulk-email-send').on('click', function() {
                    $('#ldf-bulk-email-preview').hide();
                    $('#ldf-bulk-email-progress').show();
                    sendNext(0);
                });
            });
            </script>
            <?php
        }
        
        // Add click handler for row actions
        ?>
        <script>
        jQuery(document).ready(function($) {
            if (!$('#ldf-email-preview-modal').length) {
                $('body').append(
                    '<div id="ldf-email-preview-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:99999;">' +
                        '<div style="max-width:820px; margin:6vh auto; background:#fff; border-radius:8px; padding:20px; max-height:88vh; overflow:auto;">' +
                            '<h2><?php echo esc_js(__('Email Preview', 'ldf-plugin')); ?></h2>' +
                            '<p><strong><?php echo esc_js(__('Recipient:', 'ldf-plugin')); ?></strong> <span id="ldf-preview-recipient"></span></p>' +
                            '<p><strong><?php echo esc_js(__('Subject:', 'ldf-plugin')); ?></strong> <span id="ldf-preview-subject"></span></p>' +
                            '<div id="ldf-preview-body" style="background:#f6f7f7; border-radius:6px; padding:12px; margin-bottom:16px;"></div>' +
                            '<div style="display:flex; gap:8px; justify-content:flex-end;">' +
                                '<button type="button" class="button" id="ldf-preview-cancel"><?php echo esc_js(__('Cancel', 'ldf-plugin')); ?></button>' +
                                '<button type="button" class="button button-primary" id="ldf-preview-confirm"><?php echo esc_js(__('Send Email', 'ldf-plugin')); ?></button>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
            }

            function closePreviewModal() {
                $('#ldf-email-preview-modal').hide();
                $('#ldf-preview-confirm').off('click');
            }

            $('#ldf-preview-cancel').on('click', function() {
                closePreviewModal();
            });

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

            $(document).on('click', '.ldf-send-email-single', function(e) {
                e.preventDefault();
                var postId = $(this).data('post-id');
                var $link = $(this);
                $.post(ajaxurl, {
                    action: 'ldf_preview_professional_email',
                    post_id: postId,
                    nonce: '<?php echo wp_create_nonce('ldf_preview_professional_email'); ?>'
                }, function(response) {
                    if (!response.success) {
                        alert(response.data.message || '<?php echo esc_js(__('Unable to preview this email.', 'ldf-plugin')); ?>');
                        return;
                    }

                    $('#ldf-preview-recipient').text(response.data.recipient || '—');
                    $('#ldf-preview-subject').text(response.data.subject || '—');
                    $('#ldf-preview-body').html(response.data.body || '<em>—</em>');
                    $('#ldf-email-preview-modal').show();

                    $('#ldf-preview-confirm').on('click', function() {
                        closePreviewModal();
                        $link.text('<?php _e('Sending...', 'ldf-plugin'); ?>');

                        $.post(ajaxurl, {
                            action: 'ldf_send_professional_email',
                            post_id: postId,
                            nonce: '<?php echo wp_create_nonce('ldf_send_professional_email'); ?>'
                        }, function(sendResponse) {
                            if (sendResponse.success) {
                                $link.text('✓ ' + sendResponse.data.message);
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                $link.text('✗ ' + sendResponse.data.message);
                            }
                        });
                    });
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
     * AJAX handler for sending a predefined email
     */
    public function ajax_send_professional_email() {
        check_ajax_referer('ldf_send_professional_email', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ldf-plugin')));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id || get_post_type($post_id) !== 'professional') {
            wp_send_json_error(array('message' => __('Invalid post', 'ldf-plugin')));
        }

        $result = $this->send_predefined_email($post_id);

        if ($result['success']) {
            wp_send_json_success(array('message' => $result['message']));
        }

        wp_send_json_error(array('message' => $result['message']));
    }

    /**
     * AJAX handler for previewing a predefined email
     */
    public function ajax_preview_professional_email() {
        check_ajax_referer('ldf_preview_professional_email', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ldf-plugin')));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id || get_post_type($post_id) !== 'professional') {
            wp_send_json_error(array('message' => __('Invalid post', 'ldf-plugin')));
        }

        $preview = $this->get_email_preview_data($post_id);

        if (!$preview['can_send']) {
            wp_send_json_error(array('message' => $preview['message']));
        }

        wp_send_json_success(array(
            'recipient' => $preview['recipient'],
            'subject' => $preview['subject'],
            'body' => $preview['body'],
        ));
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
     * Send the predefined email to a professional
     */
    private function send_predefined_email($post_id) {
        $settings = $this->get_email_settings();
        $recipient = $this->get_professional_email($post_id, $settings['email_field_key']);

        if (empty($recipient) || !is_email($recipient)) {
            update_post_meta($post_id, self::META_EMAIL_LAST_ERROR, __('No valid email address found', 'ldf-plugin'));
            return array('success' => false, 'message' => __('No valid email address found', 'ldf-plugin'));
        }

        if (empty($settings['subject']) || empty(trim(wp_strip_all_tags($settings['body'])))) {
            update_post_meta($post_id, self::META_EMAIL_LAST_ERROR, __('Email template is not configured', 'ldf-plugin'));
            return array('success' => false, 'message' => __('Email template is not configured', 'ldf-plugin'));
        }

        $subject = $this->replace_email_placeholders($settings['subject'], $post_id);
        $body = nl2br($this->replace_email_placeholders($settings['body'], $post_id));
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $sent = wp_mail($recipient, $subject, $body, $headers);

        if (!$sent) {
            update_post_meta($post_id, self::META_EMAIL_LAST_ERROR, __('wp_mail failed to send the email', 'ldf-plugin'));
            return array('success' => false, 'message' => __('Email failed to send', 'ldf-plugin'));
        }

        update_post_meta($post_id, self::META_EMAIL_SENT, 1);
        update_post_meta($post_id, self::META_EMAIL_SENT_AT, time());
        update_post_meta($post_id, self::META_EMAIL_SENT_TO, sanitize_email($recipient));
        delete_post_meta($post_id, self::META_EMAIL_LAST_ERROR);

        $current_stage = $this->get_pipeline_stage($post_id);
        if ($current_stage === 'New') {
            update_post_meta($post_id, self::META_PIPELINE_STAGE, 'Contacted');
        }

        return array('success' => true, 'message' => __('Email sent', 'ldf-plugin'));
    }

    /**
     * Build preview data for a professional email
     */
    private function get_email_preview_data($post_id) {
        $settings = $this->get_email_settings();
        $recipient = $this->get_professional_email($post_id, $settings['email_field_key']);

        if (empty($recipient) || !is_email($recipient)) {
            return array(
                'can_send' => false,
                'message' => __('No valid email address found', 'ldf-plugin'),
                'name' => get_the_title($post_id),
                'recipient' => '',
                'subject' => '',
                'body' => '',
            );
        }

        if (empty($settings['subject']) || empty(trim(wp_strip_all_tags($settings['body'])))) {
            return array(
                'can_send' => false,
                'message' => __('Email template is not configured', 'ldf-plugin'),
                'name' => get_the_title($post_id),
                'recipient' => $recipient,
                'subject' => '',
                'body' => '',
            );
        }

        return array(
            'can_send' => true,
            'message' => '',
            'name' => get_the_title($post_id),
            'recipient' => $recipient,
            'subject' => $this->replace_email_placeholders($settings['subject'], $post_id),
            'body' => nl2br($this->replace_email_placeholders($settings['body'], $post_id)),
        );
    }

    /**
     * Resolve professional email from ACF/meta
     */
    private function get_professional_email($post_id, $field_key) {
        $email = '';

        if (function_exists('get_field')) {
            $email = get_field($field_key, $post_id);
        }

        if (empty($email)) {
            $email = get_post_meta($post_id, $field_key, true);
        }

        if (empty($email) && $field_key !== self::DEFAULT_EMAIL_FIELD_KEY) {
            if (function_exists('get_field')) {
                $email = get_field(self::DEFAULT_EMAIL_FIELD_KEY, $post_id);
            }
            if (empty($email)) {
                $email = get_post_meta($post_id, self::DEFAULT_EMAIL_FIELD_KEY, true);
            }
        }

        return is_string($email) ? trim($email) : '';
    }

    /**
     * Get email settings with defaults
     */
    private function get_email_settings() {
        $defaults = array(
            'email_field_key' => self::DEFAULT_EMAIL_FIELD_KEY,
            'subject' => '',
            'body' => '',
        );

        $settings = get_option(self::OPTION_NAME, array());
        return wp_parse_args(is_array($settings) ? $settings : array(), $defaults);
    }

    /**
     * Get current pipeline stage for a professional
     */
    private function get_pipeline_stage($post_id) {
        $stage = get_post_meta($post_id, self::META_PIPELINE_STAGE, true);

        if (empty($stage) || !in_array($stage, $this->pipeline_stages, true)) {
            return 'New';
        }

        return $stage;
    }

    /**
     * Replace placeholders in subject/body
     */
    private function replace_email_placeholders($text, $post_id) {
        $replacements = array(
            '{name}' => get_the_title($post_id),
            '{stage}' => $this->get_pipeline_stage($post_id),
            '{site_name}' => get_bloginfo('name'),
        );

        return strtr((string) $text, $replacements);
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
