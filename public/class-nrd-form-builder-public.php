<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/Alucard17th
 * @since      1.0.0
 *
 * @package    Nrd_Form_Builder
 * @subpackage Nrd_Form_Builder/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Nrd_Form_Builder
 * @subpackage Nrd_Form_Builder/public
 * @author     Noureddine Eddallal <eddallal.noureddine@gmail.com>
 */
class Nrd_Form_Builder_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nrd_Form_Builder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nrd_Form_Builder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nrd-form-builder-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nrd_Form_Builder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nrd_Form_Builder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/nrd-form-builder-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '-form-render-public', plugin_dir_url( __FILE__ ) . '../admin/vendor/jquery-formbuilder/form-render.min.js', array( 'jquery' ), '3.19.7', true );
		wp_localize_script( $this->plugin_name, 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	// SHORTCODE TO RENDER FORM
	public function register_shortcodes() {
		add_shortcode( 'nrd_form_bd', array( $this, 'render_form_shortcode' ) );
	}

	public function render_form_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'id' => '' ), $atts );
		$id   = absint( $atts['id'] );
		if ( ! $id ) {
			return '';
		}

		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'nrd-form-bd' ) {
			return '';
		}

		$content       = $post->post_content; // RAW JSON string as saved
		$title         = get_the_title( $id );
		$linkedSheetId = get_post_meta( $id, 'nrd_form_bd_google_sheet_id', true );
		$linkedSheetPg = get_post_meta( $id, 'nrd_form_bd_google_sheet_page', true );
		$nonce         = wp_create_nonce( 'nrd_form_submit_' . $id );

		$container_id = 'nrd-fb-form-' . $id;
		$json_id      = 'nrd-fb-json-' . $id;

		$json_raw = trim( (string) $content );
		if ( $json_raw === '' ) {
			$json_raw = '[]'; }

		ob_start(); ?>
		<div class="nrd-fb-instance"
			data-form-id="<?php echo esc_attr( $id ); ?>"
			data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<form id="<?php echo esc_attr( $container_id ); ?>"
				class="nrd-fb-form"
				enctype="multipart/form-data"
				data-form-id="<?php echo esc_attr( $id ); ?>">

			<!-- keep meta INSIDE the form -->
			<div class="nrd-fb-meta" style="display:none;">
				<input type="hidden" name="form_id" value="<?php echo esc_attr( $id ); ?>">
				<input type="hidden" name="form_title" value="<?php echo esc_attr( $title ); ?>">
				<input type="hidden" name="google_sheet_id" value="<?php echo esc_attr( $linkedSheetId ); ?>">
				<input type="hidden" name="google_sheet_page" value="<?php echo esc_attr( $linkedSheetPg ); ?>">
				<input type="hidden" name="nrd_fb_nonce" value="<?php echo esc_attr( $nonce ); ?>">
			</div>

			<!-- render target (DO NOT render into the <form> itself) -->
			<div class="nrd-fb-render-target"></div>

			<!-- if your builder doesn’t add a submit button, add one -->
			<!-- <button type="submit">Submit</button> -->
			</form>

			<script type="application/json" id="<?php echo esc_attr( $json_id ); ?>">
			<?php echo $json_raw; // raw JSON, not wp_json_encode() ?>
			</script>
		</div>
		<?php
		return ob_get_clean();
	}

	public function post_nrd_wp_fb() {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		// ---- 0) Basic input & CSRF ------------------------------------------------
		$formId = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$nonce  = isset( $_POST['nrd_fb_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nrd_fb_nonce'] ) ) : '';

		if ( ! $formId || ! wp_verify_nonce( $nonce, 'nrd_form_submit_' . $formId ) ) {
			wp_send_json_error(
				array(
					'status' => 'error',
					'errors' => array( 'Invalid or missing nonce.' ),
				),
				400
			);
		}

		// Rate-limit by IP+form (e.g., 1 submit / 10s)
		$ip     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '0.0.0.0';
		$rl_key = 'nrd_fb_rl_' . md5( $ip . '|' . $formId );
		if ( get_transient( $rl_key ) ) {
			wp_send_json_error(
				array(
					'status' => 'error',
					'errors' => array( 'Please wait a moment before submitting again.' ),
				),
				429
			);
		}
		set_transient( $rl_key, 1, 10 ); // 10 seconds

		$uploaded_files  = array();
		$successMessages = array();
		$errorMessages   = array();

		// ---- 1) File uploads (strict allow-list) ----------------------------------
		$allowed_mimes = array(
			'jpg|jpeg' => 'image/jpeg',
			'png'      => 'image/png',
			'pdf'      => 'application/pdf',
		);
		$max_bytes     = 5 * 1024 * 1024; // 5MB per file

		if ( ! empty( $_FILES ) ) {
			foreach ( $_FILES as $file_key => $file ) {
				try {
					if ( isset( $file['error'] ) && $file['error'] === UPLOAD_ERR_OK && isset( $file['size'] ) && $file['size'] > 0 ) {
						if ( $file['size'] > $max_bytes ) {
							$errorMessages[] = 'File too large (' . esc_html( $file_key ) . ')';
							continue;
						}

						// Validate filename & type
						$ext    = pathinfo( $file['name'], PATHINFO_EXTENSION );
						$ext    = strtolower( $ext );
						$ok_ext = array( 'jpg', 'jpeg', 'png', 'pdf' );
						if ( ! in_array( $ext, $ok_ext, true ) ) {
							$errorMessages[] = 'Invalid file type (' . esc_html( $file_key ) . ')';
							continue;
						}

						$upload_overrides = array(
							'test_form'                => false,
							'mimes'                    => $allowed_mimes,
							'unique_filename_callback' => function ( $dir, $name, $ext ) {
								$base = sanitize_file_name( wp_basename( $name, $ext ) );
								return $base . '-' . time() . $ext;
							},
						);

						$movefile = wp_handle_upload( $file, $upload_overrides );

						if ( $movefile && ! isset( $movefile['error'] ) ) {
							// Insert into Media Library
							$attachment = array(
								'guid'           => $movefile['url'],
								'post_mime_type' => $movefile['type'],
								'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $movefile['file'] ) ),
								'post_content'   => '',
								'post_status'    => 'inherit',
							);

							$attach_id = wp_insert_attachment( $attachment, $movefile['file'] );
							if ( is_wp_error( $attach_id ) ) {
								$errorMessages[] = 'Failed to register file in Media Library.';
								continue;
							}

							require_once ABSPATH . 'wp-admin/includes/image.php';
							$attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
							wp_update_attachment_metadata( $attach_id, $attach_data );

							$uploaded_files[ $file_key ] = wp_get_attachment_url( $attach_id );
						} else {
							$msg             = isset( $movefile['error'] ) ? $movefile['error'] : 'Unknown upload error';
							$errorMessages[] = 'Error uploading file (' . esc_html( $file_key ) . '): ' . esc_html( $msg );
						}
					} elseif ( isset( $file['error'] ) && $file['error'] !== UPLOAD_ERR_NO_FILE ) {
						$errorMessages[] = 'File upload error (' . esc_html( $file_key ) . '): code ' . intval( $file['error'] );
					}
				} catch ( \Throwable $t ) {
					$errorMessages[] = 'Unexpected file upload error (' . esc_html( $file_key ) . '): ' . esc_html( $t->getMessage() );
				}
			}
		}

		// ---- 2) Build sanitized form map ------------------------------------------
		$whitelist_keys = array(
			'google_sheet_id',
			'google_sheet_page',
			'form_title',
			'form_id',
			'nrd_fb_nonce',
			// + dynamic field names from formBuilder — we’ll accept anything not starting with '_'
		);

		$dataArray = array();
		foreach ( $_POST as $k => $v ) {
			if ( $k === 'action' ) {
				continue;
			}
			if ( strpos( $k, '_' ) === 0 ) {
				continue; // ignore WP internal/system keys
			}
			// keep non-whitelisted dynamic fields too, they’re user fields
			$dataArray[ $k ] = is_array( $v ) ? array_map( 'sanitize_text_field', wp_unslash( $v ) ) : sanitize_text_field( wp_unslash( $v ) );
		}

		// Extract and remove internal fields
		$googleSheetId   = isset( $dataArray['google_sheet_id'] ) ? sanitize_text_field( $dataArray['google_sheet_id'] ) : '';
		$googleSheetPage = isset( $dataArray['google_sheet_page'] ) ? sanitize_text_field( $dataArray['google_sheet_page'] ) : '';
		$formTitle       = isset( $dataArray['form_title'] ) ? sanitize_text_field( $dataArray['form_title'] ) : 'Form Submission';

		unset( $dataArray['google_sheet_id'], $dataArray['google_sheet_page'], $dataArray['form_title'], $dataArray['form_id'], $dataArray['nrd_fb_nonce'] );

		// Merge files
		$form_data = array_merge( $dataArray, $uploaded_files );

		// ---- 3) Email notification -------------------------------------------------
		try {
			$sent = $this->sendNotificationEmail( $form_data, $formTitle, $formId );
			if ( $sent ) {
				$successMessages[] = 'Form data received and email sent successfully';
			} else {
				$errorMessages[] = 'Failed to send email';
			}
		} catch ( \Throwable $t ) {
			$errorMessages[] = 'Email error: ' . esc_html( $t->getMessage() );
		}

		$errorMessages[] = $googleSheetId ? 'Google Sheets ID: ' . esc_html( $googleSheetId ) : '';

		// ---- 4) Google Sheets (optional) ------------------------------------------
		if ( ! empty( $googleSheetId ) ) {
			try {
				$result = $this->addLeadToGoogleSheets( $form_data, $googleSheetId, $googleSheetPage );
				if ( $result === true ) {
					$successMessages[] = 'Lead added to Google Sheets successfully';
				} else {
					$errorMessages[] = 'Failed to add lead to Google Sheets: ' . esc_html( $result );
				}
			} catch ( \Throwable $t ) {
				$errorMessages[] = 'Google Sheets error: ' . esc_html( $t->getMessage() );
			}
		}

		// ---- 5) Save submission CPT -----------------------------------------------
		$submissionResult = $this->createSubmissionPost( $formId, $form_data, $formTitle );
		if ( $submissionResult['ok'] ) {
			$successMessages[] = 'Submission saved (ID: ' . intval( $submissionResult['id'] ) . ')';
		} else {
			$errorMessages[] = 'Failed to save submission: ' . esc_html( $submissionResult['error'] );
		}

		// ---- 6) Respond ------------------------------------------------------------
		$payload = array(
			'status'        => empty( $errorMessages ) ? 'success' : 'error',
			'messages'      => $successMessages,
			'errors'        => $errorMessages,
			'submission_id' => $submissionResult['ok'] ? intval( $submissionResult['id'] ) : null,
		);

		if ( empty( $errorMessages ) ) {
			wp_send_json_success( $payload );
		} else {
			wp_send_json_error( $payload );
		}
	}

		/**
	 * Create a submission CPT and save payload.
	 *
	 * @param int    $formIdFromRequest  Parent form ID.
	 * @param array  $form_data          Flat field map (includes file URLs).
	 * @param string $formTitle          Used for submission post_title.
	 *
	 * @return array { ok: bool, id?: int, error?: string }
	 */
	private function createSubmissionPost( $formIdFromRequest, array $form_data, $formTitle ) {
		try {
			$title           = (string) ( $formTitle ?: 'Form Submission' );
			$submission_post = array(
				'post_type'   => 'nrd-form-bd-submit',
				'post_status' => 'publish',
				'post_title'  => $title . ' – ' . current_time( 'mysql' ),
				'post_parent' => $formIdFromRequest ?: 0,
			);

			$submission_id = wp_insert_post( $submission_post, true );
			if ( is_wp_error( $submission_id ) ) {
				return array(
					'ok'    => false,
					'error' => $submission_id->get_error_message(),
				);
			}

			// Full JSON payload
			$encoded = wp_json_encode( $form_data );
			if ( $encoded === false || $encoded === null ) {
				// Still create the post, but warn
				update_post_meta( $submission_id, '_nrd_fb_json_error', 'Failed to encode submission payload as JSON' );
			} else {
				add_post_meta( $submission_id, '_nrd_fb_submission_json', $encoded );
			}

			// Per-field meta (strings only; arrays/objects to JSON)
			foreach ( $form_data as $key => $value ) {
				$meta_key = sanitize_key( '_fld_' . $key );
				$val      = is_scalar( $value ) ? (string) $value : wp_json_encode( $value );
				// sanitize text, but allow URLs/emails to pass as text
				add_post_meta( $submission_id, $meta_key, wp_kses_post( $val ) );
			}

			// Common quick-look fields
			if ( ! empty( $form_data['email'] ) ) {
				update_post_meta( $submission_id, '_nrd_fb_email', sanitize_email( $form_data['email'] ) );
			}
			if ( ! empty( $form_data['name'] ) ) {
				update_post_meta( $submission_id, '_nrd_fb_name', sanitize_text_field( $form_data['name'] ) );
			}

			return array(
				'ok' => true,
				'id' => (int) $submission_id,
			);
		} catch ( \Throwable $t ) {
			return array(
				'ok'    => false,
				'error' => $t->getMessage(),
			);
		}
	}

	// function addLeadToGoogleSheets($leadData, $googleSheetId, $googleSheetPage) {
	//  require_once plugin_dir_path(__DIR__) . 'vendor/autoload.php';

	//  try {
	//      // Configure the Google Client
	//      $client = new \Google_Client();
	//      $client->setApplicationName('Google Sheets with Primo');
	//      $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
	//      $client->setAccessType('offline');
	//      $client->setAuthConfig(__DIR__ . '/credentials.json');

	//      $service = new Google_Service_Sheets($client);
	//      $spreadsheetId = $googleSheetId;

	//      $range = $googleSheetPage; // Sheet name
	//      // Prepare headers and values
	//      $headers = array_keys($leadData);
	//      $values = array_values($leadData);

	//      // Get the existing headers from the sheet
	//      $response = $service->spreadsheets_values->get($spreadsheetId, $range);
	//      $existingHeaders = $response->getValues() ? $response->getValues()[0] : [];

	//      // If headers are not present, add them
	//      if (empty($existingHeaders)) {
	//          $headerBody = new Google_Service_Sheets_ValueRange([
	//              'values' => [$headers]
	//          ]);
	//          $service->spreadsheets_values->append(
	//              $spreadsheetId,
	//              $range,
	//              $headerBody,
	//              ['valueInputOption' => 'RAW']
	//          );
	//      }

	//      // Append values
	//      $valueBody = new Google_Service_Sheets_ValueRange([
	//          'values' => [$values]
	//      ]);
	//      $params = [
	//          'valueInputOption' => 'RAW'
	//      ];

	//      $result = $service->spreadsheets_values->append(
	//          $spreadsheetId,
	//          $range,
	//          $valueBody,
	//          $params
	//      );

	//      return true; // Success
	//  } catch (Exception $e) {
	//      // error_log('Error adding lead to Google Sheets: ' . $e->getMessage());
	//      return $e->getMessage();
	//  }
	// }

	function addLeadToGoogleSheets( $leadData, $googleSheetId, $googleSheetPage ) {
		if ( ! $googleSheetId ) {
			$googleSheetId = get_option( Nrd_FB_Sheets_Service::OPT_SHEET_ID, '' );
		}
		if ( ! $googleSheetPage ) {
			$googleSheetPage = get_option( Nrd_FB_Sheets_Service::OPT_SHEET_TAB, 'Leads' );
		}
		if ( ! $googleSheetId ) {
			return 'No Spreadsheet ID configured.';
		}

		$service = new Nrd_FB_Sheets_Service();
		return $service->append_row( $leadData, $googleSheetId, $googleSheetPage, true ); // true|error string
	}


	function sendNotificationEmail( $form_data, $formTitle, $formId = 0 ) {
		// Per-form override (optional)
		$to = '';
		if ( $formId ) {
			$meta_to = get_post_meta( $formId, 'nrd_form_bd_notify_email', true );
			if ( $meta_to && is_email( $meta_to ) ) {
				$to = $meta_to;
			}
		}
		if ( ! $to ) {
			$adminEmail = get_option( 'admin_email' );
			$to         = is_email( $adminEmail ) ? $adminEmail : '';
		}
		if ( ! $to ) {
			return false; // no valid recipient
		}

		// HTML table body
		$email_body  = '<h1>New Form Submission for: ' . esc_html( $formTitle ) . '</h1>';
		$email_body .= "<table border='1' cellpadding='5' cellspacing='0'>";
		foreach ( $form_data as $key => $value ) {
			$val         = is_scalar( $value ) ? (string) $value : wp_json_encode( $value );
			$email_body .= '<tr><th style="text-align:left;">' . esc_html( $key ) . '</th><td>' . esc_html( $val ) . '</td></tr>';
		}
		$email_body .= '</table>';

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$subject = 'New Form Submission';
		return wp_mail( $to, $subject, $email_body, $headers );
	}
}
