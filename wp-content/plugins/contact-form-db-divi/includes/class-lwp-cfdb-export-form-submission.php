<?php

/**
 * A class to handle the export of form submissions
 */
class Lwp_Cfdb_Export_Form_Submission {

	//===========================================================================================

	/**
	 * Constructor function that registers hooks related to form export.
	 */
	public function __construct() {
		//register the submenu item
		add_action( 'admin_menu', array( $this, 'add_csv_export_submenu' ) );
		//register the form settings
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		//register the function for handling of form submission
		add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
	}

	//===========================================================================================

	/**
	 * Registers the Export CSV submenu item
	 */
	public function add_csv_export_submenu() {
		add_submenu_page(
			'edit.php?post_type=lwp_form_submission',
			'Export CSV',
			'Export CSV',
			'manage_options',
			'export-csv',
			array( $this, 'export_csv_callback' )
		);
	}

	//===========================================================================================

	/**
	 * Handles the form submission on admin_init to avoid headers being sent
	 */
	public function handle_form_submission() {
		//Check if the export form has been submitted
		if ( isset( $_POST['lwp_cfdb_export_submit'] ) && check_admin_referer( 'lwp_cfdb_export_submit' , 'lwp_cfdb_export_nonce' ) ) {

			//Retrieve the form data
			$file_name   = isset( $_POST['lwp_cfdb_export_options']['file_name'] ) ? sanitize_text_field( $_POST['lwp_cfdb_export_options']['file_name'] ) : '';
			$date_from   = isset( $_POST['lwp_cfdb_export_options']['date_from'] ) ? sanitize_text_field( $_POST['lwp_cfdb_export_options']['date_from'] ) : '';
			$date_to     = isset( $_POST['lwp_cfdb_export_options']['date_to'] )   ? sanitize_text_field( $_POST['lwp_cfdb_export_options']['date_to'] )   : '';
			$unique_id   = isset( $_POST['lwp_cfdb_export_options']['unique_id'] ) ? sanitize_text_field( $_POST['lwp_cfdb_export_options']['unique_id'] ) : '';

			//WP QUERY to fetch all relevant form submissions
			$date_from = isset( $date_from ) ? sanitize_text_field( $date_from ) : '';
			$date_to   = isset( $date_to ) ? sanitize_text_field( $date_to ) : '';

			//default arguments for the WP QUERY
			$args = array(
				'post_type'      => 'lwp_form_submission',
				'posts_per_page' => -1,
				'fields'         => 'ids', //Retrieve only post IDs for better performance
				'meta_key'       => 'lwp_cfdb_contact_form_unique_id',
				'meta_value'     => $unique_id,
			);

			//If either date_from or date_to is set
			if ( ! empty( $date_from ) || ! empty( $date_to ) ) {
				$args['date_query'] = array();

				if ( ! empty( $date_from ) ) {
					$args['date_query'][] = array(
						'after'     => gmdate( 'Y-m-d', strtotime( $date_from ) ),
						'inclusive' => true,
					);
				}

				if ( ! empty( $date_to ) ) {
					$args['date_query'][] = array(
						'before'    => gmdate( 'Y-m-d', strtotime( $date_to . ' +1 day' ) ),
						'inclusive' => true,
					);
				}
			}

			//
			$posts = new WP_Query( $args );

			//array to store all keys later used to build header for CSV
			$all_keys = array();

			//an array to store all submissions
			$submissions = array();

			//
			if ($posts->have_posts()) {

				//get all post ids
				$post_ids = $posts->posts;
				//the meta key that contains form submission information
				$meta_key = 'processed_fields_values';

				//
				foreach ( $post_ids as $post_id ) {
					$submission = get_post_meta( $post_id, $meta_key, true );

					//
					$submission_keys = array_keys( $submission );
					$extra_keys      = array_diff( $submission_keys, $all_keys );
					$all_keys        = array_merge( $all_keys, $extra_keys );

					//
					$submission = array_combine( array_keys( $submission ), array_column( $submission, 'value' ) );
					array_push( $submissions, $submission );
				}

			}

			//an associative array of empty key => values where keys are CSV headers.
			$empty_headers = array_fill_keys( $all_keys, '' );

			//file name
			$filename = !empty($file_name) ? $file_name . '.csv' : gmdate('Ymd_His') . '.csv';

			//Open file handle to output stream
			$fh = fopen( $filename, 'w' );

			//Write header row for the CSV
			fputcsv( $fh, $all_keys );

			//Write data rows on the CSV
			foreach ( $submissions as $submission ) {
				$combinedArray = wp_parse_args( $submission, $empty_headers );
				fputcsv( $fh, array_values( $combinedArray ) );
			}

			//Close file handle
			fclose( $fh );

			//Set appropriate headers to trigger the file download
			header( 'Content-Type: application/csv' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			header( 'Content-Length: ' . filesize($filename) );
			readfile( $filename );

			//Remove the temporary file
			unlink( $filename );

			//Terminate script execution
			exit;
		}
	}

	//===========================================================================================

	/**
 	 * Renders the export CSV settings page.
	 */
	public function export_csv_callback() {
		//Render the export CSV settings page
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="">
				<?php
				//Output the form fields
				settings_fields( 'lwp_cfdb_export_options_group' );
				do_settings_sections( 'lwp_cfdb_export_settings_page' );
				// Add the nonce field
				wp_nonce_field('lwp_cfdb_export_submit', 'lwp_cfdb_export_nonce');
				?>

				<p class="submit">
					<input type="submit" name="lwp_cfdb_export_submit" class="button-primary" value="<?php esc_attr_e( 'Export CSV', 'textdomain' ); ?>" />
				</p>
			</form>
		</div>
		<?php
	}

	//===========================================================================================

	/**
 	 * Initializes the Export CSV form settings.
	 */
	public function init_settings() {
		//Register settings
		register_setting( 'lwp_cfdb_export_options_group', 'lwp_cfdb_export_options' );

		//Add sections and fields to settings page
		add_settings_section(
			 'lwp_cfdb_export_settings_section',
			 'Export CSV Settings',
			 array( $this, 'lwp_cfdb_export_settings_section_callback' ),
			 'lwp_cfdb_export_settings_page'
		);

		add_settings_field(
			'lwp_cfdb_export_file_name',
			'File Name',
			array( $this, 'lwp_cfdb_export_file_name_callback' ),
			'lwp_cfdb_export_settings_page',
			'lwp_cfdb_export_settings_section'
		);

		add_settings_field(
			'lwp_cfdb_export_date_from',
			'Date From',
			array( $this, 'lwp_cfdb_export_date_from_callback' ),
			'lwp_cfdb_export_settings_page',
			'lwp_cfdb_export_settings_section'
		);

		add_settings_field(
			'lwp_cfdb_export_date_to',
			'Date To',
			array( $this, 'lwp_cfdb_export_date_to_callback' ),
			'lwp_cfdb_export_settings_page',
			'lwp_cfdb_export_settings_section'
		);

		add_settings_field(
			'lwp_cfdb_export_unique_id',
			'Form Unique ID',
			array( $this, 'lwp_cfdb_export_unique_id_callback' ),
			'lwp_cfdb_export_settings_page',
			'lwp_cfdb_export_settings_section'
		);
	}

	//===========================================================================================

	/**
 	 * Callback function for the form section
	 */
	public function lwp_cfdb_export_settings_section_callback() {
		//Callback function for settings section
		echo '<p>Filter the form submissions you want to export. Not choosing any options will export all form submissions</p>';
	}

	/**
 	 * Callback function for the export file name field
	 */
	public function lwp_cfdb_export_file_name_callback() {
		echo '<input type="text" name="lwp_cfdb_export_options[file_name]" value="" />';
		echo '<p class="description">Name for the exported csvs file.</p>';
	}

	/**
 	 * Callback function for the date from field
	 */
	public function lwp_cfdb_export_date_from_callback() {
		$date_from_default   = gmdate( 'Y-m-d', strtotime( '-30 days' ) ); // 30 days ago
		echo '<input type="date" name="lwp_cfdb_export_options[date_from]" value="' . esc_attr( $date_from_default ) . '" />';
		echo '<p class="description">The date from which all form submissions should be exported.</p>';
	}

	/**
 	 * Callback function for the date to field
	 */
	public function lwp_cfdb_export_date_to_callback() {
		$date_to_default   = gmdate( 'Y-m-d' ); // Today's date
		echo '<input type="date" name="lwp_cfdb_export_options[date_to]" value="' . esc_attr( $date_to_default ) . '" />';
		echo '<p class="description">The date to which all form submissions should be exported.</p>';
	}

	/**
 	 * Callback function for the unique id field
	 */
	public function lwp_cfdb_export_unique_id_callback() {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'lwp_cfdb_contact_form_unique_id'", OBJECT );
		echo '<select name="lwp_cfdb_export_options[unique_id]">';
		foreach ($results as $result) {
			echo '<option value="' . esc_attr( $result->meta_value ) . '">' . esc_html( $result->meta_value ) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">The unique ID of the form. Visible in the Contact Form Module Settings. For more details, please consult the <a href="https://www.learnhowwp.com/documentation/contact-form-db-divi/#export">documentation</a>.</p>';
	}

	//===========================================================================================
}
