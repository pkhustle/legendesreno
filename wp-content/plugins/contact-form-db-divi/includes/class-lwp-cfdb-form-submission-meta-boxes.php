<?php

/**
 * Class to manage meta boxes on the lwp_form_submission post type
 */
class Lwp_Cfdb_Form_Submission_Meta_Boxes {

	//===========================================================================================

	/**
	 * Registers the metaboxes for form submission CPT based on the version being used (free/premium).
	 * @global bool $is_free_version Whether the current version of the plugin is free or premium.
	 */
	public function __construct() {

		global $is_free_version;

		//
		if ( lwp_cfdd_fs()->is__premium_only() ) {
			add_action( 'add_meta_boxes', array( $this, 'add_form_submission_meta_boxes__premium_only' ) );
		}

		//
		if ( $is_free_version ) {
			add_action( 'add_meta_boxes', array( $this, 'add_form_submission_meta_boxes__free' ) );
		}

	}

	//===========================================================================================

	/**
	 * Registers the meta boxes for the premium version.
	 */
	public function add_form_submission_meta_boxes__premium_only() {

		//Form Submission Details Meta Box
		add_meta_box(
			'form-submission-details',
			'Form Submission Details',
			array( $this, 'render_form_submission_meta_box__premium_only' ),
			'lwp_form_submission',
			'normal',
			'high'
		);

		//Additional Details Meta Box
		add_meta_box(
			'form-submission-additional-details',
			'Additional Details',
			array( $this, 'render_form_submission_additional_details__premium_only' ),
			'lwp_form_submission',
			'normal',
			'high'
		);

	}

	//===========================================================================================

	/**
	 * Registers the meta boxes for free version.
	 */
	public function add_form_submission_meta_boxes__free() {

		//Form Submission Details Meta Box
		add_meta_box(
			'form-submission-details',
			'Form Submission Details',
			array( $this, 'render_form_submission_meta_box__free' ),
			'lwp_form_submission',
			'normal',
			'high'
		);

	}

	//===========================================================================================

	/**
	 * Callback function to render the Submission Details Meta Box.
	 *
	 * @param WP_Post $post The current post being edited.
	 */
	function render_form_submission_meta_box__premium_only($post) {

		//
		$post_meta          = get_post_meta( $post->ID );

		$additional_details = get_post_meta( $post->ID, 'additional_details',      true );
		$submission_details = get_post_meta( $post->ID, 'processed_fields_values', true );
		$read_status        = get_post_meta( $post->ID, 'lwp_cfdb_read_status',    true );
		$read_date          = get_post_meta( $post->ID, 'lwp_cfdb_read_date',      true );

		//
		if ($read_status  == false) {

			//
			update_post_meta( $post->ID, 'lwp_cfdb_read_status', true );
			update_post_meta( $post->ID, 'lwp_cfdb_read_date', current_time( 'mysql' ) );

		}

	?>

		<table class="wp-list-table widefat fixed striped" style="margin-bottom:10px;">
			<thead>
				<tr>
					<th scope="col"><?php esc_attr_e( 'Field Name' , 'contact-form-db-divi' ); ?></th>
					<th scope="col"><?php esc_attr_e( 'Value'      , 'contact-form-db-divi' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $submission_details as $value ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $value['label'] ); ?>:</strong></td>
					<td><?php echo esc_html( $value['value'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<a class="button button-primary" href="mailto:<?php echo esc_attr($submission_details['email']['value']); ?>" type="button">Reply via Email</a>

		<?php
	}

	//===========================================================================================

	/**
	 * Callback function to render the Additional Details Meta Box.
	 *
	 * @param WP_Post $post The current post being edited.
	 */
	function render_form_submission_additional_details__premium_only($post) {

		//
		$post_meta          = get_post_meta( $post->ID );

		$additional_details = get_post_meta( $post->ID, 'additional_details', true );
		$read_date          = get_post_meta( $post->ID, 'lwp_cfdb_read_date', true );

		?>

		<table class="wp-list-table widefat fixed striped">
			<tr>
				<td><strong>Page Submitted:</strong></td>
				<td><?php echo esc_html($additional_details['page_name']); ?></td>
			</tr>
			<tr>
				<td><strong>Date Submitted:</strong></td>
				<td><?php echo esc_html($additional_details['date_submitted']); ?></td>
			</tr>
			<tr>
				<td><strong>Read Date:</strong></td>
				<td><?php echo esc_html($read_date); ?></td>
			</tr>
		</table>

		<?php

	}

	//===========================================================================================

	/**
	 * Callback function to render the Submission Details Meta Box for free version.
	 *
	 * @param WP_Post $post The current post being edited.
	 */
	function render_form_submission_meta_box__free($post) {

		$submission_details = get_post_meta( $post->ID, 'processed_fields_values', true );

	?>

		<table class="wp-list-table widefat fixed striped" style="margin-bottom:10px;">
			<thead>
				<tr>
					<th scope="col"><?php esc_attr_e( 'Field Name' , 'contact-form-db-divi' ); ?></th>
					<th scope="col"><?php esc_attr_e( 'Value'      , 'contact-form-db-divi' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong><?php echo esc_html( $submission_details['name']['label'] ); ?>:</strong></td>
					<td><?php echo esc_html( $submission_details['name']['value'] ); ?></td>
				</tr>
				<tr>
					<td><strong><?php echo esc_html( $submission_details['email']['label'] ); ?>:</strong></td>
					<td><?php echo esc_html( $submission_details['email']['value'] ); ?></td>
				</tr>
				<tr>
					<td><strong><?php echo esc_html( $submission_details['message']['label'] ); ?>:</strong></td>
					<td><?php echo esc_html( $submission_details['message']['value'] ); ?></td>
				</tr>
			</tbody>
		</table>

		<a class="button button-primary" href="mailto:<?php echo esc_attr($submission_details['email']['value']); ?>" type="button">Reply via Email</a>

		<?php
	}

	//===========================================================================================

}
