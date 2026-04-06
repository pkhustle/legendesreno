<?php
if (!class_exists('PAC_DTH_Term_Image')) {
    class PAC_DTH_Term_Image
    {

        private static $_instance;

        private $taxonomies = [];

        private $labels;

        private $term_meta_key = 'thumbnail_id';

        /**
         * Get Class Instance
         * @return PAC_DTH_Term_Image
         */
        static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Initializer Of The Class
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            if (is_admin()) {
                $prefix = 'pac_dth_';
                $options = get_option(pac_dth_get_theme_option_name());
                if (!empty($options)) {
                    $taxonomie = array_filter($options, function ($key) use ($prefix) {
                        return strpos($key, $prefix) === 0;
                    }, ARRAY_FILTER_USE_KEY);
                    if ($taxonomie) {
                        foreach ($taxonomie as $taxonomy_key => $taxonomy_value) {
                            if ('on' === $taxonomy_value) {
                                $this->taxonomies[] = str_replace($prefix, '', $taxonomy_key);
                            }
                        }
                    }
                }
                $this->labels = [
                    'field_label' => __('Thumbnail', 'divi-taxonomy-helper'),
                    'field_description' => __('Select which image should represent this term.', 'divi-taxonomy-helper'),
                    'upload_btn_label' => __('Upload/Add image', 'divi-taxonomy-helper'),
                    'remove_btn_label' => __('Remove', 'divi-taxonomy-helper'),
                    'admin_column_title' => __('Featured Image', 'divi-taxonomy-helper'),
                ];
                $this->taxonomies = apply_filters($prefix.'filter_taxonomies', $this->taxonomies);
                $this->labels = apply_filters($prefix.'filter_taxonomy_labels', $this->labels);
                foreach ($this->taxonomies as $taxonomy) {
                    add_action($taxonomy."_add_form_fields", [$this, 'maybe_add_form']);
                    add_action($taxonomy.'_edit_form_fields', [$this, 'maybe_edit_form']);
                    add_action('create_'.$taxonomy, [$this, 'maybe_save_form']);
                    add_action('edit_'.$taxonomy, [$this, 'maybe_save_form']);
                    add_filter('manage_edit-'.$taxonomy.'_columns', [$this, 'maybe_term_column_image']);
                    add_filter('manage_'.$taxonomy.'_custom_column', [$this, 'maybe_term_column_image_content'], 10, 3);
                }
            }
        }

        /**
         * Add Image Field
         * @return void
         */
        public function maybe_add_form()
        { ?>
            <div class="form-field term-image-wrap">
                <label><?php echo esc_attr($this->labels['field_label']); ?></label>
                <?php $this->taxonomy_term_image_field(); ?>
            </div>
            <?php
        }

        /**
         * Edit Term
         *
         * @param $term
         *
         * @return void
         */
        public function maybe_edit_form($term)
        {
            $thumbnail_id = $this->get_thumbnail_id($term); ?>
            <tr class="form-field">
                <th scope="row"><label><?php echo esc_attr($this->labels['field_label']); ?></label></th>
                <td class="taxonomy-term-image-row">
                    <?php $this->taxonomy_term_image_field($thumbnail_id); ?>
                </td>
            </tr>
            <?php
        }

        /**
         * Save Term
         *
         * @param $term_id
         *
         * @return void
         */
        public function maybe_save_form($term_id)
        {
            if (isset($_POST['taxonomy-term-image-save-form-nonce']) && wp_verify_nonce(sanitize_text_field($_POST['taxonomy-term-image-save-form-nonce']),
                    'taxonomy-term-image-form-save') && isset($_POST['taxonomy']) && isset($_POST['taxonomy_term_image']) && in_array($_POST['taxonomy'], $this->taxonomies)) {
                $old_image = get_term_meta($term_id, $this->term_meta_key, true);
                $new_image = absint($_POST['taxonomy_term_image']);
                if ($old_image && '' == $new_image) {
                    delete_term_meta($term_id, $this->term_meta_key);
                } elseif ($old_image !== $new_image) {
                    update_term_meta($term_id, $this->term_meta_key, $new_image);
                }
            }
        }

        /**
         * Add Term Column
         *
         * @param $columns
         *
         * @return mixed
         */
        public function maybe_term_column_image($columns)
        {
            $columns['thumbnail_id'] = $this->labels['admin_column_title'];

            return $columns;
        }

        /**
         * Term Header Content
         *
         * @param $content
         * @param $column_name
         * @param $term_id
         *
         * @return mixed|string
         */
        public function maybe_term_column_image_content($content, $column_name, $term_id)
        {
            if ('thumbnail_id' == $column_name) {
                $term = get_term($term_id);
                $thumbnail_id = $this->get_thumbnail_id($term);
                if ($thumbnail_id) {
                    $content = wp_get_attachment_image($thumbnail_id, 'thumbnail', false, ['style' => 'width:20%; height:auto;']);
                }
            }

            return $content;
        }

        /**
         * Render Image Field
         *
         * @param $image_id
         *
         * @return void
         */
        private function taxonomy_term_image_field($image_id = null)
        {
            $image_src = $image_id ? wp_get_attachment_url($image_id) : [];
            $display = !empty($image_src) ? '' : 'none';
            ?>
            <button type="button" class="taxonomy-term-image-attach button"><?php echo esc_attr($this->labels['upload_btn_label']); ?></button>
            <button type="button" class="taxonomy-term-image-remove button" style="display: <?php echo esc_attr($display); ?>"><?php echo esc_attr($this->labels['remove_btn_label']); ?></button>
            <input type="hidden" id="taxonomy-term-image-id" name="taxonomy_term_image" value="<?php echo esc_attr($image_id); ?>"/>
            <p class="description"><?php echo esc_html($this->labels['field_description']); ?></p>
            <p id="taxonomy-term-image-container">
                <?php if (!empty($image_src)) { ?>
                    <img class="taxonomy-term-image-attach" src="<?php echo esc_attr($image_src); ?>" width="80px" height="80px" alt=""/>
                <?php } ?>
            </p>
            <?php
            wp_nonce_field('taxonomy-term-image-form-save', 'taxonomy-term-image-save-form-nonce');
        }

        /**
         * Get Thumbnail ID
         *
         * @param $term
         *
         * @return mixed
         */
        private function get_thumbnail_id($term)
        {
            return get_term_meta($term->term_id, $this->term_meta_key, true);
        }

    }

    (new PAC_DTH_Term_Image())->instance()->init();
}