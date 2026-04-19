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
    const ACF_WEBSITE = 'website';
    const ACF_EMAIL = 'email';
    const ACF_PHONE = 'phone';
    const ACF_ADDRESS = 'full-address';
    const ACF_CITY = 'city';
    const ACF_LOGO = 'logo';
    const ACF_PHOTOS = 'image_gallery';
    const ACF_PLACE_ID = 'place_id';
    const ACF_OWNER_NAME = 'proprietaire';
    const ACF_AWARDS = 'awards_and_recognitions';
    const ACF_CERTIFICATIONS = 'certifications';
    const ACF_SERVICE_AREAS = 'service_areas';
    const ACF_YEARS_IN_BUSINESS = 'years_in_business';
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
    const META_NORMALIZED_WEBSITE = '_ldf_normalized_website';
    const META_NORMALIZED_EMAIL = '_ldf_normalized_email';
    const META_NORMALIZED_PHONE = '_ldf_normalized_phone';
    const META_NORMALIZED_TITLE = '_ldf_normalized_title';
    const IMPORT_RESULT_TRANSIENT_PREFIX = 'ldf_professional_import_result_';

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
        add_action('admin_post_ldf_import_professional_from_urls', array($this, 'handle_import_professional_from_urls'));
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

        add_submenu_page(
            'edit.php?post_type=professional',
            __('Import Professional from URLs', 'ldf-plugin'),
            __('Import from URLs', 'ldf-plugin'),
            'edit_posts',
            'ldf-professional-import',
            array($this, 'render_import_page')
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
     * Render importer page for creating professionals from URLs
     */
    public function render_import_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'ldf-plugin'));
        }

        $result = get_transient($this->get_import_result_transient_key());
        if ($result) {
            delete_transient($this->get_import_result_transient_key());
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Import Professional from URLs', 'ldf-plugin'); ?></h1>
            <p><?php esc_html_e('Paste one or two public URLs. The importer will scrape available business information, create a new Professional record, save matching fields, and then run the existing enrichment flow when possible.', 'ldf-plugin'); ?></p>

            <?php if (!empty($result)) : ?>
                <div class="notice notice-<?php echo !empty($result['success']) ? 'success' : 'error'; ?>">
                    <p><strong><?php echo esc_html($result['message']); ?></strong></p>
                    <?php if (!empty($result['details'])) : ?>
                        <ul style="list-style:disc; margin-left:20px;">
                            <?php foreach ($result['details'] as $detail) : ?>
                                <li><?php echo esc_html($detail); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($result['edit_link'])) : ?>
                        <p><a class="button button-primary" href="<?php echo esc_url($result['edit_link']); ?>"><?php esc_html_e('Edit Imported Professional', 'ldf-plugin'); ?></a></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width:900px; background:#fff; padding:24px; border:1px solid #dcdcde; border-radius:8px;">
                <input type="hidden" name="action" value="ldf_import_professional_from_urls" />
                <?php wp_nonce_field('ldf_import_professional_from_urls', 'ldf_import_professional_nonce'); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="ldf_import_url_1"><?php esc_html_e('URL 1', 'ldf-plugin'); ?></label></th>
                            <td>
                                <input type="url" class="regular-text code" style="width:100%;" id="ldf_import_url_1" name="ldf_import_url_1" placeholder="https://example.com" required />
                                <p class="description"><?php esc_html_e('Primary source URL, usually the company website or profile page.', 'ldf-plugin'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ldf_import_url_2"><?php esc_html_e('URL 2', 'ldf-plugin'); ?></label></th>
                            <td>
                                <input type="url" class="regular-text code" style="width:100%;" id="ldf_import_url_2" name="ldf_import_url_2" placeholder="https://maps.google.com/..." />
                                <p class="description"><?php esc_html_e('Optional secondary source used to complement missing fields.', 'ldf-plugin'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(__('Create Professional', 'ldf-plugin')); ?>
            </form>
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
     * Handle Professional import form submission
     */
    public function handle_import_professional_from_urls() {
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('Permission denied', 'ldf-plugin'));
        }

        check_admin_referer('ldf_import_professional_from_urls', 'ldf_import_professional_nonce');

        $urls = array_filter(array(
            $this->sanitize_import_url($_POST['ldf_import_url_1'] ?? ''),
            $this->sanitize_import_url($_POST['ldf_import_url_2'] ?? ''),
        ));

        if (empty($urls)) {
            $this->store_import_result_and_redirect(array(
                'success' => false,
                'message' => __('Please provide at least one valid URL.', 'ldf-plugin'),
                'details' => array(),
            ));
        }

        $result = $this->import_professional_from_urls($urls);
        $this->store_import_result_and_redirect($result);
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
     * Import a professional from one or two source URLs
     */
    private function import_professional_from_urls($urls) {
        $pages = array();
        $details = array();

        foreach ($urls as $url) {
            $page = $this->fetch_scrape_source($url);
            if (!$page['success']) {
                return array(
                    'success' => false,
                    'message' => sprintf(__('Unable to fetch %s', 'ldf-plugin'), $url),
                    'details' => array($page['message']),
                );
            }

            $details[] = sprintf(__('Fetched source: %s', 'ldf-plugin'), $url);
            $pages[] = $page;
        }

        $data = $this->merge_scraped_data($pages);

        if (empty($data['title'])) {
            return array(
                'success' => false,
                'message' => __('Could not determine a business title from the provided URL(s).', 'ldf-plugin'),
                'details' => $details,
            );
        }

        $duplicate_id = $this->find_existing_professional($data);
        if ($duplicate_id) {
            return array(
                'success' => false,
                'message' => __('A Professional with the same website, email, phone, or title already exists.', 'ldf-plugin'),
                'details' => array_merge($details, array(sprintf(__('Existing Professional ID: %d', 'ldf-plugin'), $duplicate_id))),
                'edit_link' => get_edit_post_link($duplicate_id, 'raw'),
            );
        }

        $post_id = wp_insert_post(array(
            'post_type' => 'professional',
            'post_status' => 'draft',
            'post_title' => sanitize_text_field($data['title']),
            'post_content' => wp_kses_post($data['description'] ?? ''),
        ), true);

        if (is_wp_error($post_id)) {
            return array(
                'success' => false,
                'message' => __('Failed to create the Professional post.', 'ldf-plugin'),
                'details' => array($post_id->get_error_message()),
            );
        }

        update_post_meta($post_id, self::META_PIPELINE_STAGE, 'New');

        $saved_fields = $this->save_imported_professional_data($post_id, $data);
        $details = array_merge($details, $saved_fields);

        $enrichment = $this->enrich_professional($post_id);
        if (!empty($enrichment['message'])) {
            $details[] = sprintf(__('Post-creation enrichment: %s', 'ldf-plugin'), $enrichment['message']);
        }

        return array(
            'success' => true,
            'message' => __('Professional imported successfully.', 'ldf-plugin'),
            'details' => $details,
            'edit_link' => get_edit_post_link($post_id, 'raw'),
        );
    }

    private function sanitize_import_url($url) {
        $url = trim((string) wp_unslash($url));
        if (empty($url)) {
            return '';
        }

        $url = esc_url_raw($url);
        if (empty($url) || !wp_http_validate_url($url)) {
            return '';
        }

        return $url;
    }

    private function fetch_scrape_source($url) {
        $response = wp_remote_get($url, array(
            'timeout' => 20,
            'redirection' => 5,
            'user-agent' => 'Mozilla/5.0 (compatible; LDF Professional Importer/1.0; +' . home_url('/') . ')',
        ));

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return array('success' => false, 'message' => sprintf('HTTP %d', $code));
        }

        $html = wp_remote_retrieve_body($response);
        if (empty($html)) {
            return array('success' => false, 'message' => __('The source page was empty.', 'ldf-plugin'));
        }

        return array(
            'success' => true,
            'url' => $url,
            'html' => $html,
            'data' => $this->extract_scraped_data($html, $url),
        );
    }

    private function extract_scraped_data($html, $url) {
        $data = array(
            'source_url' => $url,
            'website' => $url,
        );

        $title = $this->extract_first_match('/<title[^>]*>(.*?)<\/title>/is', $html);
        if ($title) {
            $data['title'] = wp_strip_all_tags(html_entity_decode($title, ENT_QUOTES | ENT_HTML5));
        }

        $description = $this->extract_meta_content($html, array('description', 'og:description', 'twitter:description'));
        if ($description) {
            $data['description'] = $description;
        }

        $og_title = $this->extract_meta_content($html, array('og:title', 'twitter:title'));
        if (!empty($og_title)) {
            $data['title'] = $og_title;
        }

        $emails = array_unique(array_map('sanitize_email', $this->extract_all_matches('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $html)));
        $emails = array_values(array_filter($emails, 'is_email'));
        if (!empty($emails)) {
            $data['email'] = $emails[0];
        }

        $phone = $this->extract_phone_number($html);
        if ($phone) {
            $data['phone'] = $phone;
        }

        $json_ld = $this->extract_json_ld_data($html);
        $data = array_merge($data, array_filter($json_ld, function($value) {
            return $value !== null && $value !== '' && $value !== array();
        }));

        $address = $this->extract_address_text($html);
        if ($address && empty($data['address'])) {
            $data['address'] = $address;
        }

        if (empty($data['city']) && !empty($data['address'])) {
            $data['city'] = $this->extract_city_from_address($data['address']);
        }

        if (empty($data['logo'])) {
            $logo = $this->extract_meta_content($html, array('og:image', 'twitter:image'));
            if ($logo) {
                $data['logo'] = $this->normalize_asset_url($logo, $url);
            }
        }

        $gallery = $this->extract_image_candidates($html, $url);
        if (!empty($gallery)) {
            $data['image_gallery'] = $gallery;
        }

        $place_id = $this->extract_google_place_id($html . ' ' . $url);
        if ($place_id) {
            $data['place_id'] = $place_id;
        }

        $ratings = $this->extract_ratings($html, $json_ld);
        $data = array_merge($data, $ratings);

        $owner = $this->extract_labeled_value($html, array('owner', 'propriétaire', 'proprietaire', 'founder'));
        if ($owner) {
            $data['proprietaire'] = $owner;
        }

        $awards = $this->extract_labeled_value($html, array('awards', 'recognitions', 'awards and recognitions'));
        if ($awards) {
            $data['awards_and_recognitions'] = $awards;
        }

        $certifications = $this->extract_labeled_value($html, array('certifications', 'certification', 'licensed', 'licences'));
        if ($certifications) {
            $data['certifications'] = $certifications;
        }

        $service_areas = $this->extract_labeled_value($html, array('service areas', 'areas served', 'zones desservies'));
        if ($service_areas) {
            $data['service_areas'] = $service_areas;
        }

        $years = $this->extract_years_in_business($html);
        if ($years) {
            $data['years_in_business'] = $years;
        }

        if (!empty($data['title'])) {
            $data['title'] = trim(preg_replace('/\s*[\|\-–—].*$/u', '', $data['title']));
        }

        if (!empty($data['description'])) {
            $data['description'] = trim(wp_trim_words(wp_strip_all_tags($data['description']), 120, '...'));
        }

        return $data;
    }

    private function merge_scraped_data($pages) {
        $merged = array();

        foreach ($pages as $page) {
            foreach ($page['data'] as $key => $value) {
                if ($value === '' || $value === null || $value === array()) {
                    continue;
                }

                if ($key === 'image_gallery') {
                    if (empty($merged[$key])) {
                        $merged[$key] = array();
                    }
                    $merged[$key] = array_values(array_unique(array_merge($merged[$key], (array) $value)));
                    continue;
                }

                if (empty($merged[$key])) {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    private function find_existing_professional($data) {
        $normalized_website = $this->normalize_website_for_matching($data['website'] ?? '');
        $normalized_email = $this->normalize_email_for_matching($data['email'] ?? '');
        $normalized_phone = $this->normalize_phone_for_matching($data['phone'] ?? '');
        $normalized_title = $this->normalize_text_for_matching($data['title'] ?? '');
        $normalized_city = $this->normalize_text_for_matching($data['city'] ?? '');

        $normalized_checks = array(
            self::META_NORMALIZED_WEBSITE => $normalized_website,
            self::META_NORMALIZED_EMAIL => $normalized_email,
            self::META_NORMALIZED_PHONE => $normalized_phone,
        );

        foreach ($normalized_checks as $meta_key => $value) {
            if (empty($value)) {
                continue;
            }

            $query = new WP_Query(array(
                'post_type' => 'professional',
                'post_status' => array('publish', 'draft', 'pending', 'private'),
                'fields' => 'ids',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => $meta_key,
                        'value' => $value,
                    ),
                ),
            ));

            if (!empty($query->posts[0])) {
                return (int) $query->posts[0];
            }
        }

        foreach (array(
            'website' => $data['website'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
        ) as $field => $value) {
            if (empty($value)) {
                continue;
            }

            $query = new WP_Query(array(
                'post_type' => 'professional',
                'post_status' => array('publish', 'draft', 'pending', 'private'),
                'fields' => 'ids',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => $field,
                        'value' => $value,
                    ),
                ),
            ));

            if (!empty($query->posts[0])) {
                return (int) $query->posts[0];
            }
        }

        if (!empty($normalized_title)) {
            $query = new WP_Query(array(
                'post_type' => 'professional',
                'post_status' => array('publish', 'draft', 'pending', 'private'),
                'fields' => 'ids',
                'posts_per_page' => 5,
                'meta_query' => array(
                    array(
                        'key' => self::META_NORMALIZED_TITLE,
                        'value' => $normalized_title,
                    ),
                ),
            ));

            if (!empty($query->posts)) {
                if (count($query->posts) === 1 || empty($normalized_city)) {
                    return (int) $query->posts[0];
                }

                foreach ($query->posts as $post_id) {
                    $existing_city = $this->normalize_text_for_matching((string) get_post_meta($post_id, self::ACF_CITY, true));
                    if (!empty($existing_city) && $existing_city === $normalized_city) {
                        return (int) $post_id;
                    }
                }
            }
        }

        if (!empty($data['title'])) {
            $existing = get_page_by_title($data['title'], OBJECT, 'professional');
            if ($existing) {
                return (int) $existing->ID;
            }
        }

        return 0;
    }

    private function save_imported_professional_data($post_id, $data) {
        $details = array();

        $field_map = array(
            self::ACF_WEBSITE => $data['website'] ?? '',
            self::ACF_EMAIL => $data['email'] ?? '',
            self::ACF_PHONE => $data['phone'] ?? '',
            self::ACF_ADDRESS => $data['address'] ?? '',
            self::ACF_CITY => $data['city'] ?? '',
            self::ACF_PLACE_ID => $data['place_id'] ?? '',
            self::ACF_OWNER_NAME => $data['proprietaire'] ?? '',
            self::ACF_AWARDS => $data['awards_and_recognitions'] ?? '',
            self::ACF_CERTIFICATIONS => $data['certifications'] ?? '',
            self::ACF_SERVICE_AREAS => $data['service_areas'] ?? '',
            self::ACF_YEARS_IN_BUSINESS => $data['years_in_business'] ?? '',
            self::ACF_QUALITY_AVG => $data['average_rating'] ?? '',
            self::ACF_TIMELINESS_AVG => $data['timeliness_avg'] ?? '',
            self::ACF_PROFESSIONALISM_AVG => $data['professionalism_avg'] ?? '',
            self::ACF_VALUE_AVG => $data['value_for_money_avg'] ?? '',
        );

        foreach ($field_map as $field_key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            $this->update_professional_field($field_key, $value, $post_id);
            $details[] = sprintf(__('Saved field: %s', 'ldf-plugin'), $field_key);
        }

        if (!empty($data['logo'])) {
            $logo_id = $this->upload_external_image($data['logo'], $post_id, 'logo');
            if ($logo_id) {
                $this->update_professional_field(self::ACF_LOGO, $logo_id, $post_id);
                if (!has_post_thumbnail($post_id)) {
                    set_post_thumbnail($post_id, $logo_id);
                }
                $details[] = __('Imported logo image', 'ldf-plugin');
            }
        }

        if (!empty($data['image_gallery'])) {
            $gallery_ids = array();
            foreach (array_slice((array) $data['image_gallery'], 0, 5) as $image_url) {
                $image_id = $this->upload_external_image($image_url, $post_id, 'gallery');
                if ($image_id) {
                    $gallery_ids[] = $image_id;
                }
            }

            if (!empty($gallery_ids)) {
                $this->update_professional_field(self::ACF_PHOTOS, $gallery_ids, $post_id);
                if (!has_post_thumbnail($post_id)) {
                    set_post_thumbnail($post_id, $gallery_ids[0]);
                }
                $details[] = sprintf(__('Imported %d gallery image(s)', 'ldf-plugin'), count($gallery_ids));
            }
        }

        $this->store_normalized_dedup_meta($post_id, $data);

        return $details;
    }

    private function store_normalized_dedup_meta($post_id, $data) {
        $normalized_map = array(
            self::META_NORMALIZED_WEBSITE => $this->normalize_website_for_matching($data['website'] ?? ''),
            self::META_NORMALIZED_EMAIL => $this->normalize_email_for_matching($data['email'] ?? ''),
            self::META_NORMALIZED_PHONE => $this->normalize_phone_for_matching($data['phone'] ?? ''),
            self::META_NORMALIZED_TITLE => $this->normalize_text_for_matching($data['title'] ?? get_the_title($post_id)),
        );

        foreach ($normalized_map as $meta_key => $value) {
            if ($value === '') {
                delete_post_meta($post_id, $meta_key);
                continue;
            }

            update_post_meta($post_id, $meta_key, $value);
        }
    }

    private function update_professional_field($field_key, $value, $post_id) {
        if (function_exists('update_field')) {
            update_field($field_key, $value, $post_id);
        }

        update_post_meta($post_id, $field_key, $value);
    }

    private function upload_external_image($image_url, $post_id, $context = 'import') {
        $image_url = esc_url_raw($image_url);
        if (empty($image_url) || !wp_http_validate_url($image_url)) {
            return false;
        }

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $tmp = download_url($image_url, 30);
        if (is_wp_error($tmp)) {
            return false;
        }

        $filename = wp_basename(parse_url($image_url, PHP_URL_PATH));
        if (!$filename) {
            $filename = $context . '-' . md5($image_url) . '.jpg';
        }

        $file_array = array(
            'name' => sanitize_file_name($filename),
            'tmp_name' => $tmp,
        );

        $attachment_id = media_handle_sideload($file_array, $post_id);
        if (is_wp_error($attachment_id)) {
            @unlink($tmp);
            return false;
        }

        return $attachment_id;
    }

    private function extract_meta_content($html, $names) {
        foreach ((array) $names as $name) {
            $pattern = '/<meta[^>]+(?:name|property)=["\']' . preg_quote($name, '/') . '["\'][^>]+content=["\']([^"\']+)["\']/i';
            $match = $this->extract_first_match($pattern, $html);
            if ($match) {
                return trim(html_entity_decode($match, ENT_QUOTES | ENT_HTML5));
            }
        }

        return '';
    }

    private function extract_json_ld_data($html) {
        $matches = array();
        preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches);

        $data = array();
        foreach ($matches[1] as $json) {
            $decoded = json_decode(trim($json), true);
            if (json_last_error() !== JSON_ERROR_NONE || empty($decoded)) {
                continue;
            }

            foreach ($this->flatten_json_ld_nodes($decoded) as $node) {
                if (empty($data['title']) && !empty($node['name'])) {
                    $data['title'] = $node['name'];
                }
                if (empty($data['description']) && !empty($node['description'])) {
                    $data['description'] = wp_strip_all_tags($node['description']);
                }
                if (empty($data['website']) && !empty($node['url'])) {
                    $data['website'] = esc_url_raw($node['url']);
                }
                if (empty($data['email']) && !empty($node['email'])) {
                    $data['email'] = sanitize_email(str_replace('mailto:', '', $node['email']));
                }
                if (empty($data['phone']) && !empty($node['telephone'])) {
                    $data['phone'] = $this->sanitize_phone($node['telephone']);
                }
                if (empty($data['logo']) && !empty($node['logo'])) {
                    $data['logo'] = is_array($node['logo']) ? ($node['logo']['url'] ?? '') : $node['logo'];
                }
                if (empty($data['address']) && !empty($node['address'])) {
                    $data['address'] = $this->stringify_address($node['address']);
                    if (is_array($node['address']) && !empty($node['address']['addressLocality'])) {
                        $data['city'] = $node['address']['addressLocality'];
                    }
                }
                if (empty($data['average_rating']) && !empty($node['aggregateRating']['ratingValue'])) {
                    $rating = (float) $node['aggregateRating']['ratingValue'];
                    $data['average_rating'] = round($rating, 1);
                    $data['timeliness_avg'] = round($rating, 1);
                    $data['professionalism_avg'] = round($rating, 1);
                    $data['value_for_money_avg'] = round($rating, 1);
                }
                if (empty($data['service_areas']) && !empty($node['areaServed'])) {
                    $data['service_areas'] = $this->stringify_list($node['areaServed']);
                }
                if (empty($data['awards_and_recognitions']) && !empty($node['award'])) {
                    $data['awards_and_recognitions'] = $this->stringify_list($node['award']);
                }
            }
        }

        return $data;
    }

    private function flatten_json_ld_nodes($decoded) {
        if (isset($decoded['@graph']) && is_array($decoded['@graph'])) {
            return $decoded['@graph'];
        }

        if (isset($decoded[0]) && is_array($decoded[0])) {
            return $decoded;
        }

        return array($decoded);
    }

    private function stringify_address($address) {
        if (is_string($address)) {
            return trim($address);
        }
        if (!is_array($address)) {
            return '';
        }

        $parts = array_filter(array(
            $address['streetAddress'] ?? '',
            $address['addressLocality'] ?? '',
            $address['addressRegion'] ?? '',
            $address['postalCode'] ?? '',
            $address['addressCountry'] ?? '',
        ));

        return implode(', ', $parts);
    }

    private function stringify_list($value) {
        if (is_string($value)) {
            return trim($value);
        }
        if (!is_array($value)) {
            return '';
        }

        $items = array();
        foreach ($value as $item) {
            if (is_string($item)) {
                $items[] = trim($item);
            } elseif (is_array($item)) {
                $items[] = trim((string) ($item['name'] ?? $item['@id'] ?? ''));
            }
        }

        return implode(', ', array_filter($items));
    }

    private function extract_phone_number($html) {
        $phone = $this->extract_first_match('/(?:tel:|phone[^\d]{0,15})(\+?[0-9\s\-().]{7,})/i', $html);
        return $phone ? $this->sanitize_phone($phone) : '';
    }

    private function sanitize_phone($phone) {
        $phone = preg_replace('/[^0-9+\-().\s]/', '', (string) $phone);
        return trim(preg_replace('/\s+/', ' ', $phone));
    }

    private function normalize_phone_for_matching($phone) {
        $phone = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($phone) === 11 && strpos($phone, '1') === 0) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    private function normalize_email_for_matching($email) {
        return strtolower(trim((string) $email));
    }

    private function normalize_website_for_matching($url) {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        $parts = wp_parse_url($url);
        if (empty($parts['host']) && !empty($parts['path'])) {
            $parts = wp_parse_url('https://' . ltrim($url, '/'));
        }

        if (empty($parts['host'])) {
            return '';
        }

        $host = strtolower($parts['host']);
        $host = preg_replace('/^www\./i', '', $host);
        $path = isset($parts['path']) ? untrailingslashit(strtolower($parts['path'])) : '';

        return $host . $path;
    }

    private function normalize_text_for_matching($text) {
        $text = remove_accents(wp_strip_all_tags((string) $text));
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function extract_address_text($html) {
        $address = $this->extract_first_match('/<address[^>]*>(.*?)<\/address>/is', $html);
        if ($address) {
            return trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($address)));
        }

        $address = $this->extract_labeled_value($html, array('address', 'adresse'));
        return $address ? trim($address) : '';
    }

    private function extract_city_from_address($address) {
        $parts = array_map('trim', explode(',', (string) $address));
        return !empty($parts[1]) ? $parts[1] : '';
    }

    private function extract_image_candidates($html, $base_url) {
        $matches = array();
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches);
        $urls = array();
        foreach ($matches[1] as $src) {
            $normalized = $this->normalize_asset_url($src, $base_url);
            if (!$normalized) {
                continue;
            }

            if (preg_match('/logo|icon|avatar/i', $normalized)) {
                continue;
            }

            $urls[] = $normalized;
        }

        return array_values(array_unique(array_slice($urls, 0, 5)));
    }

    private function normalize_asset_url($asset_url, $base_url) {
        $asset_url = trim((string) $asset_url);
        if (empty($asset_url) || strpos($asset_url, 'data:') === 0) {
            return '';
        }

        if (preg_match('#^https?://#i', $asset_url)) {
            return esc_url_raw($asset_url);
        }

        $base_parts = wp_parse_url($base_url);
        if (empty($base_parts['scheme']) || empty($base_parts['host'])) {
            return '';
        }

        if (strpos($asset_url, '//') === 0) {
            return esc_url_raw($base_parts['scheme'] . ':' . $asset_url);
        }

        $root = $base_parts['scheme'] . '://' . $base_parts['host'];
        if (!empty($base_parts['port'])) {
            $root .= ':' . $base_parts['port'];
        }

        if (strpos($asset_url, '/') === 0) {
            return esc_url_raw($root . $asset_url);
        }

        $path = !empty($base_parts['path']) ? dirname($base_parts['path']) : '';
        if ($path === DIRECTORY_SEPARATOR || $path === '.') {
            $path = '';
        }

        return esc_url_raw($root . trailingslashit($path) . ltrim($asset_url, '/'));
    }

    private function extract_google_place_id($text) {
        $match = $this->extract_first_match('/ChI[A-Za-z0-9_\-]{10,}/', $text);
        return $match ? trim($match) : '';
    }

    private function extract_ratings($html, $json_ld_data) {
        $ratings = array();
        if (!empty($json_ld_data['average_rating'])) {
            $average = (float) $json_ld_data['average_rating'];
            $ratings['average_rating'] = round($average, 1);
            $ratings['timeliness_avg'] = round($average, 1);
            $ratings['professionalism_avg'] = round($average, 1);
            $ratings['value_for_money_avg'] = round($average, 1);
            return $ratings;
        }

        $average = $this->extract_first_match('/(?:rating|rated|average rating)[^0-9]{0,10}([0-9](?:\.[0-9])?)/i', $html);
        if ($average !== '') {
            $average = round((float) $average, 1);
            $ratings['average_rating'] = $average;
            $ratings['timeliness_avg'] = $average;
            $ratings['professionalism_avg'] = $average;
            $ratings['value_for_money_avg'] = $average;
        }

        return $ratings;
    }

    private function extract_labeled_value($html, $labels) {
        $text = wp_strip_all_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
        $text = preg_replace('/\s+/', ' ', $text);

        foreach ((array) $labels as $label) {
            $match = $this->extract_first_match('/' . preg_quote($label, '/') . '\s*[:\-]\s*(.{1,120})/iu', $text);
            if ($match) {
                $value = preg_split('/\s{2,}|(?:\.|\||;)/u', $match);
                return trim((string) ($value[0] ?? $match));
            }
        }

        return '';
    }

    private function extract_years_in_business($html) {
        $text = wp_strip_all_tags($html);
        $match = $this->extract_first_match('/(?:years in business|depuis|since)\D{0,20}(\d{1,2}|19\d{2}|20\d{2})/i', $text);
        if ($match === '') {
            return '';
        }

        $value = (int) $match;
        if ($value > 1900) {
            $current_year = (int) gmdate('Y');
            return max(0, $current_year - $value);
        }

        return $value;
    }

    private function extract_first_match($pattern, $subject) {
        if (preg_match($pattern, $subject, $matches)) {
            return trim(html_entity_decode(wp_strip_all_tags($matches[1] ?? $matches[0]), ENT_QUOTES | ENT_HTML5));
        }

        return '';
    }

    private function extract_all_matches($pattern, $subject) {
        if (preg_match_all($pattern, $subject, $matches)) {
            return $matches[0];
        }

        return array();
    }

    private function get_import_result_transient_key() {
        return self::IMPORT_RESULT_TRANSIENT_PREFIX . get_current_user_id();
    }

    private function store_import_result_and_redirect($result) {
        set_transient($this->get_import_result_transient_key(), $result, MINUTE_IN_SECONDS * 5);
        wp_safe_redirect(admin_url('edit.php?post_type=professional&page=ldf-professional-import'));
        exit;
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
