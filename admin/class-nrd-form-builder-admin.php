<?php

// Include the PUC library
require_once plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/Alucard17th
 * @since      1.0.0
 *
 * @package    Nrd_Form_Builder
 * @subpackage Nrd_Form_Builder/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Nrd_Form_Builder
 * @subpackage Nrd_Form_Builder/admin
 * @author     Noureddine Eddallal <eddallal.noureddine@gmail.com>
 */
class Nrd_Form_Builder_Admin {

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

	// Google Sheets Options
	const OPT_JSON_ENC  = 'nrd_fb_sa_json_enc';
	const OPT_SHEET_ID  = 'nrd_fb_default_sheet_id';
	const OPT_SHEET_TAB = 'nrd_fb_default_sheet_tab';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	private function nrd_form_bd_get_api_headers() {
		$api_token = get_option( 'nrd_form_bd_api_token', '' );
		$api_token = is_string( $api_token ) ? trim( $api_token ) : '';
		$headers   = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
		);
		if ( $api_token !== '' ) {
			$headers['Authorization'] = 'Bearer ' . $api_token;
		}
		return $headers;
	}

	private function nrd_form_bd_api_base_url() {
		$base = defined( 'NRD_API_BASE_URL' ) ? (string) NRD_API_BASE_URL : '';
		$base = apply_filters( 'nrd_form_bd_api_base_url', $base );
		return is_string( $base ) ? $base : '';
	}

	private function nrd_form_bd_remote_post_json( $endpoint, $payload ) {
		return wp_remote_post(
			$this->nrd_form_bd_api_base_url() . ltrim( (string) $endpoint, '/' ),
			array(
				'timeout' => 20,
				'headers' => $this->nrd_form_bd_get_api_headers(),
				'body'    => wp_json_encode( $payload ),
			)
		);
	}

	private function nrd_form_bd_parse_status_message( $decoded ) {
		$status  = 'error';
		$message = '';
		if ( is_array( $decoded ) && isset( $decoded['status'] ) ) {
			$status  = sanitize_text_field( (string) $decoded['status'] );
			$message = isset( $decoded['message'] ) ? sanitize_text_field( (string) $decoded['message'] ) : '';
		} elseif ( is_array( $decoded ) && isset( $decoded[0] ) && is_string( $decoded[0] ) ) {
			$status = sanitize_text_field( $decoded[0] );
		}
		return array( $status, $message );
	}

	private function nrd_form_bd_menu_slug() {
		return 'nrd-form-bd-menu-slug';
	}

	private function nrd_form_bd_admin_cap() {
		return 'manage_options';
	}

	private function nrd_form_bd_license_cache_key() {
		return 'nrd_form_bd_license_status_cache';
	}

	private function nrd_form_bd_license_cache_ttl() {
		return 15 * MINUTE_IN_SECONDS;
	}

	private function nrd_form_bd_clear_license_cache() {
		delete_transient( $this->nrd_form_bd_license_cache_key() );
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nrd-form-builder-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/nrd-form-builder-admin.js', array( 'jquery' ), $this->version, false );

		// FORM BUILDER
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( $this->plugin_name . '-form-builder', plugin_dir_url( __FILE__ ) . 'vendor/jquery-formbuilder/form-builder.min.js', array( 'jquery', 'jquery-ui-sortable' ), '3.19.7', true );
		wp_enqueue_script( $this->plugin_name . '-form-render', plugin_dir_url( __FILE__ ) . 'vendor/jquery-formbuilder/form-render.min.js', array( 'jquery' ), '3.19.7', true );

		wp_localize_script(
			$this->plugin_name,
			'nrdFormBD',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'nrd_form_bd_admin' ),
				'smtp_nonce' => wp_create_nonce( 'nrd_smtp_test_action' ),

			)
		);
	}

	// Plugin Updates
	public function nrd_plugin_setup_updater() {
		// Only check updates if license is active
		if ( get_option( 'nrd_form_bd_license_active' ) !== 'active' ) {
			return;
		}

		delete_site_transient( 'update_plugins' );
		delete_transient( 'nrd_form_builder_puc_update' );

		$plugin_file = WP_PLUGIN_DIR . '/nrd-form-builder-premium/nrd-form-builder.php';
		$plugin_slug = 'nrd-form-builder-premium';
		$repo_url    = 'https://github.com/Alucard17th/nrd-form-builder-premium';

		try {
			$myUpdateChecker = PucFactory::buildUpdateChecker(
				$repo_url,
				$plugin_file,
				$plugin_slug
			);

			$myUpdateChecker->setBranch( 'main' );
			$myUpdateChecker->getVcsApi()->enableReleaseAssets();

			// Send license key optionally
			$myUpdateChecker->addQueryArgFilter(
				function ( $queryArgs ) {
					$queryArgs['license'] = get_option( 'nrd_form_bd_license_key' );
					return $queryArgs;
				}
			);

			// Debug transient responses
			// add_filter('pre_set_site_transient_update_plugins', function($transient) {
			//  $checked = property_exists($transient, 'checked') ? $transient->checked : [];
			//  $response = property_exists($transient, 'response') ? $transient->response : [];

			//  error_log('NRD Updater: Checked plugins = ' . json_encode($checked));
			//  error_log('NRD Updater: Transient response = ' . json_encode($response));

			//  $plugin_file = 'nrd-form-builder-premium/nrd-form-builder.php'; // adjust as needed
			//  if (!isset($response[$plugin_file]) && isset($checked[$plugin_file])) {
			//      $msg = 'NRD Updater: Failed to retrieve update information from GitHub. Check your token and repo URL.';
			//      error_log($msg);

			//      add_action('admin_notices', function() use ($msg) {
			//          echo '<div class="notice notice-error"><p>' . esc_html($msg) . '</p></div>';
			//      });
			//  }

			//  return $transient;
			// });

		} catch ( Exception $e ) {
		}
	}

	public function check_local_license_status() {
		$isActive = get_option( 'nrd_form_bd_license_active' );
		return $isActive == 'active' ? true : false;
	}

	public function nrd_form_bd_please_activate_plugin() {
		if ( ! $this->check_local_license_status() ) {
			$page_slug = $this->nrd_form_bd_menu_slug();
			$page_url  = menu_page_url( $page_slug, false );

			echo // Customize the message below as needed
			'<div class="notice notice-error is-dismissible">
				<p>Please activate NRD Form Builder to use it.</p>
				<p><a href="' . esc_url( $page_url ) . '" class="">Activate NRD Form Builder</a></p>
			</div>';
		}
	}

	// Function to add the menu and the page
	function custom_dashboard_menu() {
		add_menu_page(
			'NRD Form BD',          // Page title
			'NRD Form BD',                // Menu title
			$this->nrd_form_bd_admin_cap(),             // Capability
			$this->nrd_form_bd_menu_slug(),           // Menu slug
			array( $this, 'custom_dashboard_page_html' ), // Function to display the page content
			'dashicons-forms',    // Icon URL or dashicons class
			20                            // Position in the menu
		);

		add_submenu_page(
			$this->nrd_form_bd_menu_slug(),
			'NRD Form BD',
			'Dashboard',
			$this->nrd_form_bd_admin_cap(),
			$this->nrd_form_bd_menu_slug(),
			array( $this, 'custom_dashboard_page_html' )
		);

		if ( $this->check_local_license_status() ) {
			add_submenu_page(
				$this->nrd_form_bd_menu_slug(),
				'Forms',
				'Forms',
				$this->nrd_form_bd_admin_cap(),
				'edit.php?post_type=nrd-form-bd'
			);
		}
	}

	public function custom_dashboard_page_html() {
		if ( ! current_user_can( $this->nrd_form_bd_admin_cap() ) ) {
			return;
		}

		// Determine which tab to show
		$tab_to_show = isset( $_GET['tab'] ) && $_GET['tab'] === 'email-config' ? 'email-config' : 'getting-started';

		if ( ! empty( $_GET['notice'] ) && ! empty( $_GET['notice_class'] ) ) {
			$notice_class = sanitize_text_field( wp_unslash( $_GET['notice_class'] ) );
			$notice       = sanitize_text_field( wp_unslash( $_GET['notice'] ) );
			echo '<div class="' . esc_attr( $notice_class ) . '"><p>' . esc_html( $notice ) . '</p></div>';
		}
		?>
		<div class="nrd-dashboard-container">
			<!-- Header Section -->
			<header class="nrd-dashboard-header">
				<div>
					<img src="<?php echo esc_url( plugins_url( 'images/nrd-logo.png', __FILE__ ) ); ?>" alt="<?php echo esc_attr( 'wp-nrd-form-builder' ); ?>">
				</div>
				<p class="nrd-dashboard-subtitle">Activate your license key to get started and explore powerful features.</p>
			</header>

			<!-- Main Content -->
			<div class="nrd-dashboard-content">
				<h2 class="nav-tab-wrapper">
					<a href="#tab-getting-started"
						class="nav-tab <?php echo $tab_to_show === 'getting-started' ? 'nav-tab-active' : ''; ?>">Getting
						Started</a>
					<a href="#tab-email-config"
						class="nav-tab <?php echo $tab_to_show === 'email-config' ? 'nav-tab-active' : ''; ?>">Email
						Configuration</a>
				</h2>

				<!-- License Activation Tab -->
				<div id="tab-getting-started" class="nrd-tab-content"
				style="display:<?php echo $tab_to_show === 'getting-started' ? 'block' : 'none'; ?>;">
					<div class="nrd-license-section">
						<div class="nrd-license-header">
							<h2>Enter Your License Key</h2>
							<p>Get access to all premium features by activating your license key.</p>
							<div class="nrd-license-status">
								Status: 
								<span class="nrd-status-badge <?php echo ( get_option( 'nrd_form_bd_license_active' ) == 'active' ? 'nrd-status-active' : 'nrd-status-inactive' ); ?>">
									<?php echo ( get_option( 'nrd_form_bd_license_active' ) == 'active' ? 'Active' : 'Inactive' ); ?>
								</span>
							</div>
						</div>

						<div class="nrd-license-form">
							<input type="text" id="nrd-form-bd-license-key" placeholder="Enter License Key" value="<?php echo esc_attr( get_option( 'nrd_form_bd_license_key' ) ); ?>">
							<div class="nrd-license-actions">
								<?php if ( get_option( 'nrd_form_bd_license_active' ) != 'active' ) : ?>
									<button type="button" class="nrd-button nrd-button-primary" id="nrd-form-bd-activate-button">Activate</button>
								<?php else : ?>
									<button type="button" class="nrd-button nrd-button-primary" id="nrd-form-bd-activated-button" disabled>Activated</button>
									<button type="button" class="nrd-button nrd-button-secondary" id="nrd-form-bd-deactivate-button">Deactivate</button>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<!-- Second Row: Image and Description (Text on Left, Image on Right) -->
					<div class="nrd-second-row">
						<div class="nrd-text-description">
							<h3>Getting Started</h3>
							<p>Watch this tutorial to understand how to activate and use NRD Form Builder. Learn how to build, customize, and manage your forms with ease. Whether you're a beginner or an advanced user, the NRD Form Builder provides powerful tools for creating forms in minutes.</p>
						</div>
						<div class="nrd-image-section">
							<img src="<?php echo esc_url( plugins_url( 'images/nrd-form-builder.png', __FILE__ ) ); ?>" alt="<?php echo esc_attr( 'Form Builder Image' ); ?>">
						</div>
					</div>

					<div class="nrd-third-row">
						<p>Connect with us:</p>
						<div class="nrd-social-icons">
							<a href="https://www.facebook.com/nrdformbuilder/" target="_blank"><img src="<?php echo esc_url( plugins_url( 'images/socials/facebook.svg', __FILE__ ) ); ?>" alt="<?php echo esc_attr( 'Facebook' ); ?>"></a>
							<a href="https://www.instagram.com/nrdformbuilder/" target="_blank"><img src="<?php echo esc_url( plugins_url( 'images/socials/instagram.svg', __FILE__ ) ); ?>" alt="<?php echo esc_attr( 'Instagram' ); ?>"></a>
							<a href="https://www.linkedin.com/company/nrdformbuilder/" target="_blank"><img src="<?php echo esc_url( plugins_url( 'images/socials/linkedin.svg', __FILE__ ) ); ?>" alt="<?php echo esc_attr( 'LinkedIn' ); ?>"></a>
							<a href="https://twitter.com/nrdformbuilder" target="_blank"><img src="<?php echo esc_url( plugins_url( 'images/socials/twitter.svg', __FILE__ ) ); ?>" alt="<?php echo esc_attr( 'Twitter' ); ?>"></a>
							<a href="https://github.com/nrdformbuilder" target="_blank"><img src="<?php echo esc_url( plugins_url( 'images/socials/github.svg', __FILE__ ) ); ?>" alt="<?php echo esc_attr( 'GitHub' ); ?>"></a>
						</div>
					</div>
				</div>

				<!-- Email Configuration Tab -->
				<div id="tab-email-config" class="nrd-tab-content"
					style="display:<?php echo $tab_to_show === 'email-config' ? 'block' : 'none'; ?>;">
					<h3>Email Configuration</h3>
					<p>Test your SMTP settings to make sure new form submissions can be sent via email.</p>

					<div id="nrd-smtp-notice"></div>

					<form id="nrd-smtp-test-form">
						<?php wp_nonce_field( 'nrd_smtp_test_action', 'nrd_smtp_test_nonce' ); ?>
						<table class="form-table">
							<tr>
								<th><label for="nrd_smtp_to">Test Email To</label></th>
								<td><input type="email" name="nrd_smtp_to" id="nrd_smtp_to" class="regular-text"
										placeholder="you@example.com" required></td>
							</tr>
						</table>
						<p class="submit">
							<input type="submit" class="nrd-button nrd-button-primary" value="Send Test Email">
						</p>
					</form>
				</div>

			</div>

			<!-- Footer Section -->
			<footer class="nrd-dashboard-footer">
				<p>&copy; <?php echo date( 'Y' ); ?> NRD Form Builder. All Rights Reserved.</p>
			</footer>
		</div>
		<script>
		(function($) {
			$('.nav-tab').click(function(e) {
				e.preventDefault();
				$('.nav-tab').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
				$('.nrd-tab-content').hide();
				$($(this).attr('href')).show();
			});
		})(jQuery);
		</script>
		<?php
	}

	public function register_cpt_nrd_form_bd() {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		$labels = array(
			'name'               => 'NRD BD Forms',
			'singular_name'      => 'NRD BD Form',
			'menu_name'          => 'NRD BD Forms',
			'name_admin_bar'     => 'NRD BD Form',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New NRD BD Form',
			'new_item'           => 'New NRD BD Form',
			'edit_item'          => 'Edit NRD BD Form',
			'view_item'          => 'View NRD BD Form',
			'all_items'          => 'All NRD BD Form',
			'search_items'       => 'Search NRD BD Form',
			'parent_item_colon'  => 'Parent NRD BD Form:',
			'not_found'          => 'No NRD BD Form found.',
			'not_found_in_trash' => 'No NRD BD Form found in Trash.',
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'nrd-form-bd' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' ),
		);

		register_post_type( 'nrd-form-bd', $args );
	}
	public function add_custom_meta_box() {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		add_meta_box(
			'nrd_form_bd_meta_box',          // Unique ID
			'From Builder',         // Box title
			array( $this, 'custom_meta_box_html' ),  // Content callback, must be of type callable
			'nrd-form-bd',                    // Post type
			'normal',                         // Context
		);

		add_meta_box(
			'nrd_form_bd_meta_box_drive_sheet',          // Unique ID
			'Google Sheet ID',         // Box title
			array( $this, 'custom_meta_box_html_drive_sheet' ),  // Content callback, must be of type callable
			'nrd-form-bd',                    // Post type
			'side',                         // Context
		);
	}
	public function custom_meta_box_html( $post ) {
		// wp_nonce_field('nrd_form_bd_meta_box', 'nrd_form_bd_meta_box_nonce');
		$screen  = get_current_screen();
		$content = $post->post_content;
		if ( $content != '' ) {
			$json_content = json_encode( $content );
			echo '<script>
				let formData = JSON.parse(' . $json_content . ');
			</script>';
		}

		if ( $screen->base == 'post' && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
			$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;

			echo '<input type="hidden" name="post_id" id="post_id" value="' . $post_id . '">';
			echo '<div id="short-code-preview">Short Code: 
			<code class="nrd-short-code">[nrd_form_bd id="' . $post_id . '"]</code>
			<span class="nrd-short-code-copy" style="display: none; color: #7F8184;">Link copied to clipboard.</span>
			</div>';
		}
		echo '<div class="wrap">';
		echo '<div id="fb-editor"></div>';
		echo '</div>';
	}
	public function custom_meta_box_html_drive_sheet( $post ) {
		$screen = get_current_screen();
		if ( $screen->base == 'post' && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
			$post_id         = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;
			$linkedSheetId   = get_post_meta( $post_id, 'nrd_form_bd_google_sheet_id', true );
			$linkedSheetPage = get_post_meta( $post_id, 'nrd_form_bd_google_sheet_page', true );

			echo '<h3>Google Sheet ID</h3>';
			echo '<input type="text" name="google_sheet_id" id="google_sheet_id" style="width:100%;" value="' . esc_attr( $linkedSheetId ) . '">';
			echo '<h3>Google Sheet Page</h3>';
			echo '<input type="text" name="google_sheet_page" id="google_sheet_page" style="width:100%;" value="' . esc_attr( $linkedSheetPage ) . '">';
			echo '<br><br>';

			// Read SA email from saved JSON
			$sa_email = '';
			$enc      = get_option( self::OPT_JSON_ENC, '' );
			if ( $enc ) {
				$plain = Nrd_FB_Secrets::decrypt( $enc );
				$j     = json_decode( $plain, true );
				if ( is_array( $j ) && ! empty( $j['client_email'] ) ) {
					$sa_email = $j['client_email'];
				}
			}

			echo '<div class="info-tab"><h4>Important</h4>';
			if ( $sa_email ) {
				echo 'Share your Google Sheet with this service account as <b>Editor</b>: ';
				echo '<b class="copiable-item">' . esc_html( $sa_email ) . '</b>';
			} else {
				echo 'Service Account JSON not configured yet. Go to <b>NRD Form BD → Integrations</b> and paste it.';
			}
			echo '</div>';
		}
	}
	public function hide_publish_box() {
		if ( ! $this->check_local_license_status() ) {
			return;
		}
		remove_meta_box( 'submitdiv', 'nrd-form-bd', 'side' );
	}
	public function disable_autosave_for_nrd_form_bd( $pagehook ) {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		global $post_type, $current_screen;
		if ( $post_type == 'nrd-form-bd' ) {
			wp_deregister_script( 'autosave' );
		}
	}
	public function save_nrd_wp_fb() {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		// Security
		check_ajax_referer( 'nrd_form_bd_admin', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$title    = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$content  = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : ''; // JSON string from formBuilder
		$sheet_id = isset( $_POST['google_sheet_id'] ) ? sanitize_text_field( wp_unslash( $_POST['google_sheet_id'] ) ) : '';
		$sheet_pg = isset( $_POST['google_sheet_page'] ) ? sanitize_text_field( wp_unslash( $_POST['google_sheet_page'] ) ) : '';

		// Basic validation
		if ( $title === '' ) {
			wp_send_json_error( 'Title is required', 400 );
		}

		if ( ! $post_id ) {
			$new_post = array(
				'post_title'   => $title,
				'post_content' => wp_slash( $content ), // store raw JSON safely
				'post_type'    => 'nrd-form-bd',
				'post_status'  => 'publish',
			);

			$post_id = wp_insert_post( $new_post, true );
			if ( is_wp_error( $post_id ) ) {
				wp_send_json_error( 'Error: ' . $post_id->get_error_message(), 500 );
			}
		} else {
			$update_post = array(
				'ID'           => $post_id,
				'post_title'   => $title,
				'post_content' => wp_slash( $content ),
			);

			$updated = wp_update_post( $update_post, true );
			if ( is_wp_error( $updated ) ) {
				wp_send_json_error( 'Error: ' . $updated->get_error_message(), 500 );
			}
		}

		// Optional per-form Google Sheet config
		if ( $sheet_id !== '' ) {
			update_post_meta( $post_id, 'nrd_form_bd_google_sheet_id', $sheet_id );
		}
		if ( $sheet_pg !== '' ) {
			update_post_meta( $post_id, 'nrd_form_bd_google_sheet_page', $sheet_pg );
		}

		wp_send_json_success( html_entity_decode( get_edit_post_link( $post_id ) ) );
	}

	// License Management START
	public function ajax_activate_license() {
		check_ajax_referer( 'nrd_form_bd_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
		if ( $license_key === '' ) {
			wp_send_json_error(
				array(
					'status'  => 'invalid',
					'message' => 'License key is required',
				),
				400
			);
		}

		$admin_email = get_bloginfo( 'admin_email' );
		$admin_email = is_string( $admin_email ) ? sanitize_email( $admin_email ) : '';
		$domain      = wp_parse_url( home_url(), PHP_URL_HOST );
		$domain      = is_string( $domain ) ? sanitize_text_field( $domain ) : '';
		$response    = $this->nrd_form_bd_remote_post_json(
			'activate-license',
			array(
				'key'    => $license_key,
				'email'  => $admin_email,
				'domain' => $domain,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'status'  => 'error',
					'message' => $response->get_error_message(),
				),
				500
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		if ( ! is_string( $body ) ) {
			$body = '';
		}

		if ( $code >= 400 ) {
			wp_send_json_error(
				array(
					'status'  => 'error',
					'message' => 'License server error',
					'raw'     => $body,
				),
				$code
			);
		}
		$data = json_decode( $body, true );
		if ( ! is_array( $data ) ) {
			wp_send_json_error(
				array(
					'status'  => 'error',
					'message' => 'Invalid response from license server',
					'raw'     => $body,
				),
				500
			);
		}

		list( $status, $message ) = $this->nrd_form_bd_parse_status_message( $data );

		if ( $status === 'active' ) {
			update_option( 'nrd_form_bd_license_active', 'active' );
			update_option( 'nrd_form_bd_license_key', $license_key );
			$this->nrd_form_bd_clear_license_cache();
			wp_send_json_success(
				array(
					'status'  => 'active',
					'message' => $message,
				)
			);
		}
		if ( $status === 'already_active' ) {
			update_option( 'nrd_form_bd_license_active', 'inactive' );
			if ( $message === '' ) {
				$message = 'License key is already active on another site';
			}
			$this->nrd_form_bd_clear_license_cache();
			wp_send_json_error(
				array(
					'status'  => 'already_active',
					'message' => $message,
				),
				200
			);
		}

		update_option( 'nrd_form_bd_license_active', 'inactive' );
		$this->nrd_form_bd_clear_license_cache();
		if ( $message === '' ) {
			$message = ( $status === 'invalid' ) ? 'Invalid license key' : 'License is not active';
		}
		wp_send_json_error(
			array(
				'status'  => $status,
				'message' => $message,
			),
			200
		);
	}

	public function ajax_deactivate_license() {
		check_ajax_referer( 'nrd_form_bd_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$license_key = get_option( 'nrd_form_bd_license_key', '' );
		$license_key = is_string( $license_key ) ? $license_key : '';
		if ( $license_key === '' ) {
			update_option( 'nrd_form_bd_license_active', 'inactive' );
			delete_option( 'nrd_form_bd_license_key' );
			$this->nrd_form_bd_clear_license_cache();
			wp_send_json_success( array( 'status' => 'inactive' ) );
		}

		$response = $this->nrd_form_bd_remote_post_json(
			'deactivate-license',
			array(
				'key' => $license_key,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'status'  => 'error',
					'message' => $response->get_error_message(),
				),
				500
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		if ( ! is_string( $body ) ) {
			$body = '';
		}
		$data = json_decode( $body, true );
		if ( ! is_array( $data ) ) {
			wp_send_json_error(
				array(
					'status'  => 'error',
					'message' => 'Invalid response from license server',
					'raw'     => $body,
				),
				500
			);
		}

		list( $status, $message ) = $this->nrd_form_bd_parse_status_message( $data );

		if ( $code >= 400 ) {
			wp_send_json_error(
				array(
					'status'  => $status,
					'message' => $message !== '' ? $message : 'License server error',
				),
				$code
			);
		}

		if ( $status === 'inactive' ) {
			update_option( 'nrd_form_bd_license_active', 'inactive' );
			delete_option( 'nrd_form_bd_license_key' );
			$this->nrd_form_bd_clear_license_cache();
			wp_send_json_success(
				array(
					'status'  => 'inactive',
					'message' => $message,
				)
			);
		}

		wp_send_json_error(
			array(
				'status'  => $status,
				'message' => $message !== '' ? $message : 'License could not be deactivated',
			),
			200
		);
	}

	// Function to check the license status by calling your server's endpoint
	public function check_license_status() {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		$cached = get_transient( $this->nrd_form_bd_license_cache_key() );
		if ( is_array( $cached ) && isset( $cached['status'] ) ) {
			return;
		}
		// Get the stored license key
		$license_key = get_option( 'nrd_form_bd_license_key' );

		if ( ! $license_key ) {
			// If no license key exists, mark the plugin as inactive
			update_option( 'nrd_form_bd_license_active', 'inactive' );
			set_transient(
				$this->nrd_form_bd_license_cache_key(),
				array( 'status' => 'inactive' ),
				$this->nrd_form_bd_license_cache_ttl()
			);
			return;
		}

		$response = $this->nrd_form_bd_remote_post_json(
			'activate-license',
			array(
				'key' => $license_key,
			)
		);

		if ( is_wp_error( $response ) ) {
			set_transient(
				$this->nrd_form_bd_license_cache_key(),
				array(
					'status'  => 'error',
					'message' => $response->get_error_message(),
				),
				5 * MINUTE_IN_SECONDS
			);
			return;
		}

		$code           = wp_remote_retrieve_response_code( $response );
		$body           = wp_remote_retrieve_body( $response );
		$body           = is_string( $body ) ? $body : '';
		$data           = json_decode( $body, true );
		list( $status ) = $this->nrd_form_bd_parse_status_message( $data );

		if ( $code >= 400 ) {
			set_transient(
				$this->nrd_form_bd_license_cache_key(),
				array( 'status' => 'error' ),
				5 * MINUTE_IN_SECONDS
			);
			return;
		}

		// Handle the status and activate/deactivate the plugin accordingly
		if ( $status === 'active' ) {
			update_option( 'nrd_form_bd_license_active', 'active' );
			set_transient(
				$this->nrd_form_bd_license_cache_key(),
				array( 'status' => 'active' ),
				$this->nrd_form_bd_license_cache_ttl()
			);
			// Optionally: Deactivate the plugin or disable features
			// deactivate_plugins(plugin_basename(__FILE__));
		} elseif ( $status === 'invalid' || $status === 'inactive' ) {
			update_option( 'nrd_form_bd_license_active', 'inactive' );
			set_transient(
				$this->nrd_form_bd_license_cache_key(),
				array( 'status' => 'inactive' ),
				$this->nrd_form_bd_license_cache_ttl()
			);
		} else {
			set_transient(
				$this->nrd_form_bd_license_cache_key(),
				array( 'status' => 'unknown' ),
				5 * MINUTE_IN_SECONDS
			);
		}
	}

	public function show_license_warning() {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		if ( get_option( 'nrd_form_bd_license_active' ) !== 'active' ) {
			echo '<div class="notice notice-error"><p>' . esc_html( 'Your license is inactive. Please activate your license to continue using the premium features.' ) . '</p></div>';
		}
	}
	// License Management END

	// Custom Post Type Submission
	public function register_cpt_nrd_form_bd_submission() {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		$labels = array(
			'name'               => 'NRD BD Submissions',
			'singular_name'      => 'NRD BD Submission',
			'menu_name'          => 'Submissions',
			'name_admin_bar'     => 'Submission',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Submission',
			'new_item'           => 'New Submission',
			'edit_item'          => 'View Submission',
			'view_item'          => 'View Submission',
			'all_items'          => 'All Submissions',
			'search_items'       => 'Search Submissions',
			'not_found'          => 'No submissions found.',
			'not_found_in_trash' => 'No submissions found in Trash.',
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false, // we’ll mount under your main menu
			'query_var'          => true,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'custom-fields' ),
		);

		register_post_type( 'nrd-form-bd-submit', $args );
	}

	public function register_submissions_submenu() {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		add_submenu_page(
			$this->nrd_form_bd_menu_slug(),       // parent slug (your main menu)
			'Submissions',                 // page title
			'Submissions',                 // menu title
			$this->nrd_form_bd_admin_cap(),              // capability
			'edit.php?post_type=nrd-form-bd-submit' // target
		);
	}

	public function submissions_columns( $columns ) {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		$new                 = array();
		$new['cb']           = $columns['cb'];
		$new['title']        = 'Submission';
		$new['parent_form']  = 'Form';
		$new['fields_count'] = 'Fields';
		$new['date']         = 'Date';
		$new['preview']      = 'Preview';
		return $new;
	}

	public function submissions_column_content( $column, $post_id ) {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		switch ( $column ) {
			case 'parent_form':
				$parent_id = wp_get_post_parent_id( $post_id );
				if ( $parent_id ) {
					$link = get_edit_post_link( $parent_id );
					echo '<a href="' . esc_url( $link ) . '">' . esc_html( get_the_title( $parent_id ) ) . '</a>';
				} else {
					echo '—';
				}
				break;

			case 'fields_count':
				$data = get_post_meta( $post_id, '_nrd_fb_submission_json', true );
				if ( $data ) {
					$arr = json_decode( $data, true );
					echo is_array( $arr ) ? count( $arr ) : '—';
				} else {
					echo '—';
				}
				break;

			case 'preview':
				$data = get_post_meta( $post_id, '_nrd_fb_submission_json', true );
				if ( $data ) {
					$arr = json_decode( $data, true );
					if ( is_array( $arr ) ) {
						// Show first 2 fields as a quick glance
						$pairs = array_slice( $arr, 0, 2 );
						foreach ( $pairs as $k => $v ) {
							echo '<div><strong>' . esc_html( $k ) . ':</strong> ' . esc_html( is_scalar( $v ) ? $v : json_encode( $v ) ) . '</div>';
						}
					} else {
						echo '—';
					}
				} else {
					echo '—';
				}
				break;
		}
	}

	public function submissions_sortable_columns( $columns ) {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		$columns['parent_form'] = 'parent_form';
		return $columns;
	}

	public function add_submission_details_metabox() {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		add_meta_box(
			'nrd_fb_submission_details',
			'Submission Details',
			array( $this, 'render_submission_details_metabox' ),
			'nrd-form-bd-submit',
			'normal',
			'high'
		);
	}

	public function render_submission_details_metabox( $post ) {
		// Parent form link
		$parent_id = wp_get_post_parent_id( $post->ID );
		echo '<p><strong>Form:</strong> ';
		if ( $parent_id ) {
			echo '<a href="' . esc_url( get_edit_post_link( $parent_id ) ) . '">' . esc_html( get_the_title( $parent_id ) ) . '</a>';
		} else {
			echo '—';
		}
		echo '</p>';

		// Quick look fields
		$name  = get_post_meta( $post->ID, '_nrd_fb_name', true );
		$email = get_post_meta( $post->ID, '_nrd_fb_email', true );
		if ( $name || $email ) {
			echo '<p>';
			if ( $name ) {
				echo '<strong>Name:</strong> ' . esc_html( $name ) . '&nbsp;&nbsp;';
			}
			if ( $email ) {
				echo '<strong>Email:</strong> ' . esc_html( $email );
			}
			echo '</p>';
		}

		// Load JSON payload
		$json = get_post_meta( $post->ID, '_nrd_fb_submission_json', true );
		if ( ! $json ) {
			echo '<p><em>No submission payload found.</em></p>';
			return;
		}

		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			echo '<p><em>Submission payload could not be decoded.</em></p>';
			return;
		}

		// Table of fields
		echo '<table class="widefat striped" style="max-width:900px">';
		echo '<thead><tr><th style="width:240px">Field</th><th>Value</th></tr></thead><tbody>';

		foreach ( $data as $key => $value ) {
			echo '<tr>';
			echo '<td><code>' . esc_html( $key ) . '</code></td>';
			echo '<td>' . $this->nrd_fb_pretty_value( $value ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';

		// Raw JSON (collapsible)
		echo '<details style="margin-top:12px"><summary>Raw JSON</summary>';
		echo '<pre style="white-space:pre-wrap;background:#f6f7f7;padding:12px;border:1px solid #e2e4e7;border-radius:4px;">' .
			esc_html( json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ) .
			'</pre></details>';
	}

	public function add_sheets_submenu() {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		add_submenu_page(
			$this->nrd_form_bd_menu_slug(),
			'Integrations – Google Sheets',
			'Integrations',
			$this->nrd_form_bd_admin_cap(),
			'nrd-fb-sheets',
			array( $this, 'render_sheets_page' )
		);
	}

	public function render_sheets_page() {
		if ( ! current_user_can( $this->nrd_form_bd_admin_cap() ) ) {
			return;
		}

		// Handle save
		if ( ! empty( $_POST ) && check_admin_referer( 'nrd_fb_sheets_save' ) ) {
			// Save defaults
			update_option( self::OPT_SHEET_ID, sanitize_text_field( wp_unslash( $_POST['nrd_fb_default_sheet_id'] ?? '' ) ) );
			update_option( self::OPT_SHEET_TAB, sanitize_text_field( wp_unslash( $_POST['nrd_fb_default_sheet_tab'] ?? 'Leads' ) ) );

			// Save SA JSON if provided
			$plain = isset( $_POST['nrd_fb_sa_json_plain'] ) ? trim( (string) wp_unslash( $_POST['nrd_fb_sa_json_plain'] ) ) : '';
			if ( $plain !== '' ) {
				$test = json_decode( $plain, true );
				if ( is_array( $test ) && ! empty( $test['client_email'] ) && ! empty( $test['private_key'] ) ) {
					update_option( self::OPT_JSON_ENC, Nrd_FB_Secrets::encrypt( $plain ) );
					add_settings_error( 'nrd_fb_sheets', 'saved_json', 'Service Account JSON stored (encrypted).', 'updated' );
				} else {
					add_settings_error( 'nrd_fb_sheets', 'bad_json', 'Invalid Service Account JSON.', 'error' );
				}
			} else {
				add_settings_error( 'nrd_fb_sheets', 'saved', 'Settings saved.', 'updated' );
			}
		}

		settings_errors( 'nrd_fb_sheets' );

		$has_json = get_option( self::OPT_JSON_ENC, '' ) ? '✅ Stored (encrypted)' : '❌ Not set';
		$sheetId  = get_option( self::OPT_SHEET_ID, '' );
		$sheetTab = get_option( self::OPT_SHEET_TAB, 'Leads' );

		// Try to read SA email to show in UI
		$sa_email = '';
		$enc      = get_option( self::OPT_JSON_ENC, '' );
		if ( $enc ) {
			$plain = Nrd_FB_Secrets::decrypt( $enc );
			$j     = json_decode( $plain, true );
			if ( is_array( $j ) && ! empty( $j['client_email'] ) ) {
				$sa_email = $j['client_email'];
			}
		}
		?>
		<div class="wrap">
			<h1>Google Sheets Integration</h1>
			<form method="post" action="">
			<?php wp_nonce_field( 'nrd_fb_sheets_save' ); ?>

			<h2 class="title">Service Account</h2>
			<p><em>Status: <?php echo esc_html( $has_json ); ?></em></p>
			<textarea name="nrd_fb_sa_json_plain" rows="10" cols="80" placeholder='Paste Service Account JSON here'></textarea>
			<p class="description">Share your Sheet with: 
				<?php if ( $sa_email ) : ?>
				<code class="copiable-item"><?php echo esc_html( $sa_email ); ?></code>
				<?php else : ?>
				<em>(save JSON to reveal service account email)</em>
				<?php endif; ?>
			</p>

			<h2 class="title" style="margin-top:24px;">Defaults</h2>
			<table class="form-table">
				<tr>
				<th><label for="nrd_fb_default_sheet_id">Spreadsheet ID</label></th>
				<td><input type="text" name="nrd_fb_default_sheet_id" id="nrd_fb_default_sheet_id" class="regular-text" value="<?php echo esc_attr( $sheetId ); ?>"></td>
				</tr>
				<tr>
				<th><label for="nrd_fb_default_sheet_tab">Sheet (tab) name</label></th>
				<td><input type="text" name="nrd_fb_default_sheet_tab" id="nrd_fb_default_sheet_tab" class="regular-text" value="<?php echo esc_attr( $sheetTab ); ?>"></td>
				</tr>
			</table>

			<?php submit_button( 'Save Settings' ); ?>
			</form>

			<hr/>
			<h2>Test Connection</h2>
			<p>After saving, click to append a test row to the default Sheet/Tab.</p>
			<button class="button button-primary" id="nrd-fb-test-sheets" data-nonce="<?php echo esc_attr( wp_create_nonce( 'nrd_fb_test_sheets' ) ); ?>">
			Run Test
			</button>
			<pre id="nrd-fb-test-out" style="margin-top:10px;background:#fff;border:1px solid #ddd;padding:10px;"></pre>
		</div>
		<script>
		(function(){
			const btn = document.getElementById('nrd-fb-test-sheets');
			const out = document.getElementById('nrd-fb-test-out');
			if (!btn) return;
			btn.addEventListener('click', function(){
			out.textContent = 'Testing...';
			fetch(ajaxurl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				body: new URLSearchParams({ action: 'nrd_fb_test_sheets', _wpnonce: btn.getAttribute('data-nonce') })
			}).then(r => r.json()).then(j => {
				out.textContent = JSON.stringify(j, null, 2);
			}).catch(e => { out.textContent = 'Error: ' + e; });
			});
		})();
		</script>
		<?php
	}

	public function ajax_test_sheets() {
		if ( ! $this->check_local_license_status() ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'error' => 'forbidden' ), 403 );
		}
		check_ajax_referer( 'nrd_fb_test_sheets' );

		$sheetId  = get_option( self::OPT_SHEET_ID, '' );
		$sheetTab = get_option( self::OPT_SHEET_TAB, 'Leads' );
		if ( ! $sheetId ) {
			wp_send_json_error( array( 'error' => 'Default Spreadsheet ID is empty. Save settings first.' ), 400 );
		}

		$service = new Nrd_FB_Sheets_Service();
		$lead    = array(
			'test'      => 'ok',
			'timestamp' => current_time( 'mysql' ),
			'site_url'  => home_url(),
		);
		$res     = $service->append_row( $lead, $sheetId, $sheetTab, true );
		if ( $res === true ) {
			wp_send_json_success(
				array(
					'ok'      => true,
					'message' => 'Row appended successfully.',
				)
			);
		}
		wp_send_json_error( array( 'error' => $res ), 500 );
	}


	/**
	 * Nicely render values (arrays/objects/files/links).
	 */
	private function nrd_fb_pretty_value( $value ) {
		// File URLs → clickable links
		$maybeUrl = is_string( $value ) ? trim( $value ) : '';
		if ( $maybeUrl && filter_var( $maybeUrl, FILTER_VALIDATE_URL ) ) {
			// If it’s a media URL show as link
			return '<a href="' . esc_url( $maybeUrl ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $maybeUrl ) . '</a>';
		}

		// Arrays/objects → pretty JSON
		if ( is_array( $value ) || is_object( $value ) ) {
			return '<pre style="white-space:pre-wrap;margin:0;">' .
				esc_html( json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ) .
				'</pre>';
		}

		// Scalars → plain text
		return nl2br( esc_html( (string) $value ) );
	}

	public function nrd_smtp_test_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		check_ajax_referer( 'nrd_smtp_test_action', '_ajax_nonce' );

		$to      = sanitize_email( $_POST['smtp_to'] );
		$subject = 'NRD Form Builder Test Email';
		$message = 'This is a test email from NRD Form Builder plugin to verify your SMTP settings.';

		if ( wp_mail( $to, $subject, $message ) ) {
			wp_send_json_success( 'Test email sent successfully!' );
		} else {
			wp_send_json_error( 'Failed to send test email. Please check your SMTP settings.' );
		}
	}

	// Elementor Widget
	public function register_elementor_widgets() {
		if ( defined( 'ELEMENTOR_PATH' ) && class_exists( 'Elementor\Widget_Base' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'class-nrd-elementor-widget.php';
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new NRD_Form_Builder_Elementor_Widget() );
		}
	}
}
