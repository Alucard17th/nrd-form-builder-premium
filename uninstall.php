<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://github.com/Alucard17th
 * @since      1.0.0
 *
 * @package    Nrd_Form_Builder
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$delete_for_site = static function () {
	delete_option( 'nrd_form_bd_license_active' );
	delete_option( 'nrd_form_bd_license_key' );
	delete_option( 'nrd_form_bd_api_token' );
	delete_option( 'nrd_fb_sa_json_enc' );
	delete_option( 'nrd_fb_default_sheet_id' );
	delete_option( 'nrd_fb_default_sheet_tab' );

	delete_transient( 'nrd_form_bd_license_status_cache' );
	delete_transient( 'nrd_form_builder_puc_update' );
};

if ( is_multisite() ) {
	$site_ids = get_sites( array( 'fields' => 'ids' ) );
	foreach ( $site_ids as $site_id ) {
		switch_to_blog( (int) $site_id );
		$delete_for_site();
		restore_current_blog();
	}
} else {
	$delete_for_site();
}

$timestamp = wp_next_scheduled( 'check_plugin_license' );
if ( $timestamp ) {
	wp_unschedule_event( $timestamp, 'check_plugin_license' );
}
