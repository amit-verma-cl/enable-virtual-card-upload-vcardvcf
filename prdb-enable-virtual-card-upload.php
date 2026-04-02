<?php

/*************************************************************************

Plugin Name: Enable Virtual Card Upload - Vcard,Vcf
Plugin URI: https://www.linkedin.com/in/avcodelord/
Description: Enables upload of virtual card (vcf,vcard) files.
Version: 2.3.1
Author: Amit verma
Author URI: https://www.linkedin.com/in/avcodelord/
Text Domain: enable-virtual-card-upload

**************************************************************************/


if ( !defined( 'ABSPATH' ) ) {
	exit;
}


class PRDBEnableVcardUpload {

	/**
	 * Allowed real MIME values that are commonly reported for vCard files.
	 *
	 * @var array
	 */
	private $allowed_real_mimes = array(
		'text/vcard',
		'text/x-vcard',
		'text/plain',
		'application/octet-stream',
	);

	/**
	 * Construct the plugin object
	 * @since    1.0.0
	 */
	public function __construct() {
		add_filter( 'upload_mimes', array( $this, 'enable_vcard_upload' ), 10, 2 );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'allow_vcard_real_mime' ), 10, 5 );
		add_action( 'admin_init', array( $this, 'handle_donation_notice_dismiss' ) );
		add_action( 'admin_notices', array( $this, 'render_donation_notice' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ) );
	} // END public function __construct

	/**
	 * Activate the plugin
	 */
	public static function activate() {
		// Do nothing
	} // END public static function activate

	/**
	 * Deactivate the plugin
	 */
	public static function deactivate() {
		// Do nothing
	} // END public static function deactivate

	/**
	 * Handle a donation notice dismissal request.
	 */
	public function handle_donation_notice_dismiss() {
		if ( ! is_admin() ) {
			return;
		}

		if ( empty( $_GET['prdb_donation_notice_dismiss'] ) ) {
			return;
		}

		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'prdb_donation_notice_dismiss' ) ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( $user_id ) {
			update_user_meta( $user_id, 'prdb_vcard_donation_notice_dismissed', time() );
		}

		$redirect_url = remove_query_arg( array( 'prdb_donation_notice_dismiss', '_wpnonce' ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Render the donation notice on the plugins admin page.
	 */
	public function render_donation_notice() {
		if ( ! is_admin() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'plugins' !== $screen->base ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$dismissed = get_user_meta( $user_id, 'prdb_vcard_donation_notice_dismissed', true );
		if ( $dismissed && ( time() - (int) $dismissed ) < MONTH_IN_SECONDS ) {
			return;
		}

		$donation_url = esc_url( 'https://ko-fi.com/amitvermacl' );
		$dismiss_url  = wp_nonce_url( add_query_arg( 'prdb_donation_notice_dismiss', '1' ), 'prdb_donation_notice_dismiss' );

		echo '<div class="notice notice-info is-dismissible">';
		echo '<p>' . esc_html__( 'If you like Enable Virtual Card Upload - Vcard,Vcf and it helps you, buy me a coffee.', 'enable-virtual-card-upload' );
		echo ' <a href="' . $donation_url . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Donate via Ko-fi', 'enable-virtual-card-upload' ) . '</a>.';
		echo ' <a href="' . esc_url( $dismiss_url ) . '">' . esc_html__( 'Dismiss', 'enable-virtual-card-upload' ) . '</a>';
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Add a donate link to the plugin action links on the plugins page.
	 */
	public function add_plugin_action_links( $links ) {
		$donation_url = esc_url( 'https://ko-fi.com/amitvermacl' );
		$links[] = '<a href="' . $donation_url . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Donate', 'enable-virtual-card-upload' ) . '</a>';
		return $links;
	}

	/**
	 * Add vcf/vcard supprt
	 * @since 1.0.0
	 */
	public function enable_vcard_upload( $mime_types = array(), $user = null ) {
		$mime_types['vcf'] = 'text/vcard';
		$mime_types['vcard'] = 'text/vcard';
		return $mime_types;
	}

	/**
	 * Allow vCard uploads when real MIME detection returns acceptable text variants.
	 *
	 * @param array       $wp_check_filetype_and_ext Values from wp_check_filetype_and_ext().
	 * @param string      $file                      Full path to uploaded file.
	 * @param string      $filename                  User supplied filename.
	 * @param array       $mimes                     Allowed MIME types.
	 * @param string|bool $real_mime                 Real MIME from fileinfo.
	 * @return array
	 */
	public function allow_vcard_real_mime( $wp_check_filetype_and_ext, $file, $filename, $mimes, $real_mime = false ) {
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		if ( ! in_array( $ext, array( 'vcf', 'vcard' ), true ) ) {
			return $wp_check_filetype_and_ext;
		}

		if ( ! empty( $wp_check_filetype_and_ext['ext'] ) && ! empty( $wp_check_filetype_and_ext['type'] ) ) {
			return $wp_check_filetype_and_ext;
		}

		if ( ! empty( $real_mime ) && ! in_array( strtolower( $real_mime ), $this->allowed_real_mimes, true ) ) {
			return $wp_check_filetype_and_ext;
		}

		$wp_check_filetype_and_ext['ext']  = $ext;
		$wp_check_filetype_and_ext['type'] = 'text/vcard';

		if ( ! isset( $wp_check_filetype_and_ext['proper_filename'] ) ) {
			$wp_check_filetype_and_ext['proper_filename'] = false;
		}

		return $wp_check_filetype_and_ext;
	}
}


$GLOBALS['PRDBEnableVcardUpload'] = new PRDBEnableVcardUpload();
