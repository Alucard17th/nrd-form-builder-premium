<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/Alucard17th
 * @since      1.0.0
 *
 * @package    Nrd_Form_Builder
 * @subpackage Nrd_Form_Builder/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Nrd_Form_Builder
 * @subpackage Nrd_Form_Builder/includes
 * @author     Noureddine Eddallal <eddallal.noureddine@gmail.com>
 */
class Nrd_Form_Builder_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( get_option( 'nrd_form_bd_license_active', null ) === null ) {
			add_option( 'nrd_form_bd_license_active', 'inactive' );
		}
		if ( get_option( 'nrd_form_bd_license_key', null ) === null ) {
			add_option( 'nrd_form_bd_license_key', '' );
		}
		if ( get_option( 'nrd_form_bd_api_token', null ) === null ) {
			add_option( 'nrd_form_bd_api_token', '' );
		}
		if ( get_option( 'nrd_fb_default_sheet_id', null ) === null ) {
			add_option( 'nrd_fb_default_sheet_id', '' );
		}
		if ( get_option( 'nrd_fb_default_sheet_tab', null ) === null ) {
			add_option( 'nrd_fb_default_sheet_tab', 'Leads' );
		}
	}
}
