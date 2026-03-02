<?php

/*************************************************************************

Plugin Name: Enable Virtual Card Upload - Vcard,Vcf
Plugin URI: https://prodabo.com
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
