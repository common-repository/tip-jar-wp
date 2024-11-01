<?php
/**
 * Tip Jar WP
 *
 * @package     Tip Jar WP
 * @subpackage  Classes/Tip Jar WP
 * @copyright   Copyright (c) 2018, Tip Jar WP
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create a transient which expires in 24 hours containing the download information.
 * This is useful for file downloads which do not require a transaction, but where an email address is required.
 *
 * @access   public
 * @since    1.0.0
 * @param    int    $form_id - The ID of the form containing the attachment we want to download.
 * @param    int    $transaction_id - Optional, but if the form requires a transaction, this is required.
 * @param    string $page_url - Optional. The URL of the page where this download originated.
 * @param    string $email - Optional, but if the form requires an email address, this is required.
 * @return   array Contains a success boolean, error_code upon failure, and the URL where this file can be downloaded upon success. This is a secret URL which should only be delivered to the user where they are verified. for example, in their email inbox.
 */
function tip_jar_wp_create_file_download_transient( $form_id, $transaction_id, $page_url, $email ) {

	// Sanitize the parameters.
	$form_id        = absint( $form_id );
	$transaction_id = absint( $transaction_id );
	$page_url       = sanitize_text_field( wp_unslash( $page_url ) );
	$email          = sanitize_email( $email );

	// Get the form row from the database.
	$form = new Tip_Jar_WP_Form( $form_id );

	// If no form was found...
	if ( ! $form->id ) {
		return array(
			'success'    => false,
			'error_code' => 'invalid_form_id_in_transient',
		);
	}

	// Generate a 1 time username/password combination.
	$file_download_key   = wp_generate_password( 12, false );
	$file_download_value = wp_generate_password( 12, false );

	// Create the secret file download URL.
	$file_download_url = get_bloginfo( 'url' ) . '?tjwp_file_download=' . $file_download_key . '&tjwp_file_download_value=' . $file_download_value;

	set_transient(
		'tjwp_file_download_' . $file_download_key,
		array(
			'file_download_password' => wp_hash_password( $file_download_value ),
			'form_id'                => $form_id,
			'transaction_id'         => $transaction_id,
			'page_url'               => $page_url,
			'email'                  => $email,
		),
		DAY_IN_SECONDS
	);

	return array(
		'success'           => true,
		'file_download_url' => $file_download_url,
	);
}

/**
 * Verify a file download transient, delete it, and return the form ID and download information of the file being downloaded.
 *
 * @access   public
 * @since    1.0.0
 * @param    string $tjwp_file_download - This is the key used to look up the transient. Like a username.
 * @param    string $tjwp_file_download_value - This is the value that will be used to verify, like a password.
 * @return   Tip_Jar_WP_Form The form object which contains the attachment being downloade.
 */
function tip_jar_wp_verify_file_download_transient( $tjwp_file_download, $tjwp_file_download_value ) {

	$file_download_key   = sanitize_text_field( wp_unslash( $tjwp_file_download ) );
	$file_download_value = sanitize_text_field( wp_unslash( $tjwp_file_download_value ) );

	// Get the possibly-existing transient storing the data for this file download.
	$transient_data = get_transient( 'tjwp_file_download_' . $file_download_key );

	// If the transient does not exist, it is likely expired, or this is a brute force attempt.
	if ( ! $transient_data ) {
		return array(
			'success'    => false,
			'error_code' => 'transient_not_found',
		);
	}

	// Make sure the transient has all the data we need.
	if (
		! isset( $transient_data['file_download_password'] ) ||
		! isset( $transient_data['form_id'] ) ||
		! isset( $transient_data['transaction_id'] ) ||
		! isset( $transient_data['page_url'] ) ||
		! isset( $transient_data['email'] )
	) {

		delete_transient( 'tjwp_file_download_' . $file_download_key );

		return array(
			'success'    => false,
			'error_code' => 'values_missing_in_transient',
		);
	}

	// Sanitize all of the values coming out of the database/transient.
	$transient_data['file_download_password'] = sanitize_text_field( $transient_data['file_download_password'] );
	$transient_data['form_id']                = absint( $transient_data['form_id'] );
	$transient_data['transaction_id']         = absint( $transient_data['transaction_id'] );
	$transient_data['page_url']               = sanitize_text_field( $transient_data['page_url'] );
	$transient_data['email']                  = sanitize_email( $transient_data['email'] );

	if ( ! class_exists( 'PasswordHash' ) ) {
		require_once ABSPATH . WPINC . '/class-phpass.php';
	}

	$wp_hasher = new PasswordHash( 8, true );

	// Check if the code entered matches the current code for this user.
	$entered_password = $file_download_value;
	$saved_password   = $transient_data['file_download_password'];

	if ( ! $wp_hasher->CheckPassword( $entered_password, $saved_password ) ) {
		delete_transient( 'tjwp_file_download_' . $file_download_key );

		return array(
			'success'    => false,
			'error_code' => 'invalid_password',
		);
	}

	// Get the form row from the database.
	$form = new Tip_Jar_WP_Form( $transient_data['form_id'] );

	// If no form was found...
	if ( ! $form->id ) {
		delete_transient( 'tjwp_file_download_' . $file_download_key );

		return array(
			'success'    => false,
			'error_code' => 'invalid_form_id_in_transient',
		);
	}

	// Now that the transient ddata has been verified, delete it, and return it's value from this function.
	delete_transient( 'tjwp_file_download_' . $file_download_key );

	return array(
		'success'        => true,
		'form'           => $form,
		'transient_data' => $transient_data,
	);
}
