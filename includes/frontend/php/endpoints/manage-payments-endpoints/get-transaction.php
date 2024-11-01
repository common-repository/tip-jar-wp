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
 * Endpoint which gets a transaction for the currently-logged-in user. It is separated out like this so it can be unit tested.
 *
 * @access   public
 * @since    1.0.0
 * @return   mixed
 */
function tip_jar_wp_get_transaction_endpoint() {

	if ( ! isset( $_GET['tip_jar_wp_get_transaction'] ) ) {
		return false;
	}

	$endpoint_result = tip_jar_wp_get_transaction_handler();

	echo wp_json_encode( $endpoint_result );
	die();
}
add_action( 'init', 'tip_jar_wp_get_transaction_endpoint' );

/**
 * Get a single transaction from the frontend
 *
 * @access   public
 * @since    1.0.0
 * @return   array
 */
function tip_jar_wp_get_transaction_handler() {

	// Verify the nonce.
	if ( ! isset( $_POST['tip_jar_wp_get_transaction_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tip_jar_wp_get_transaction_nonce'] ) ), 'tip_jar_wp_get_transaction_nonce' ) ) {
		return array(
			'success'    => false,
			'error_code' => 'nonce_failed',
			'details'    => 'Nonce failed.',
		);
	}

	$user = wp_get_current_user();

	// If no current user was found.
	if ( ! $user->ID ) {
		return array(
			'success'        => false,
			'error_code'     => 'not_logged_in',
			// 'frontend_nonces' => tip_jar_wp_refresh_and_get_frontend_nonces(),
			'user_logged_in' => $user->ID ? true : false,
		);
	}

	// If values are missing.
	if ( ! is_array( $_POST ) || ! isset( $_POST['tip_jar_wp_transaction_id'] ) ) {
		return array(
			'success'        => false,
			'error_code'     => 'invalid_params',
			'details'        => 'Invalid Params',
			// 'frontend_nonces' => tip_jar_wp_refresh_and_get_frontend_nonces(),
			'user_logged_in' => $user->ID ? true : false,
		);
	}

	$tip_jar_wp_transaction_id = absint( $_POST['tip_jar_wp_transaction_id'] );

	$transaction = new Tip_Jar_WP_Transaction( $tip_jar_wp_transaction_id );

	if ( 0 === $transaction->id ) {
		return array(
			'success'        => false,
			'error_code'     => 'no_matching_transaction_found',
			'details'        => 'No Transaction found with that ID',
			// 'frontend_nonces' => tip_jar_wp_refresh_and_get_frontend_nonces(),
			'user_logged_in' => $user->ID ? true : false,
		);
	}

	if ( absint( $user->ID ) !== absint( $transaction->user_id ) ) {
		return array(
			'success'        => false,
			'error_code'     => 'invalid_user',
			'details'        => 'Invalid user' . $user->ID . '-' . $transaction->user_id,
			// 'frontend_nonces' => tip_jar_wp_refresh_and_get_frontend_nonces(),
			'user_logged_in' => $user->ID ? true : false,
		);
	}

	// If transactions were found.
	return array(
		'success'          => true,
		'transaction_info' => tip_jar_wp_transaction_info_format_for_endpoint( $transaction ),
		// 'frontend_nonces' => tip_jar_wp_refresh_and_get_frontend_nonces(),
		'user_logged_in'   => $user->ID ? true : false,
	);

}
