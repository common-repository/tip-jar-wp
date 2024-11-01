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
 * Endpoint which cancels an arrangement for the currenly logged in user. It is separated out like this so it can be unit tested.
 *
 * @access   public
 * @since    1.0.0
 * @return   mixed
 */
function tip_jar_wp_cancel_arrangement_endpoint() {

	if ( ! isset( $_GET['tip_jar_wp_cancel_arrangement'] ) ) {
		return false;
	}

	$endpoint_result = tip_jar_wp_cancel_arrangement_handler();

	echo wp_json_encode( $endpoint_result );
	die();
}
add_action( 'init', 'tip_jar_wp_cancel_arrangement_endpoint' );

/**
 * Cancel a single arrangement from the frontend
 *
 * @access   public
 * @since    1.0.0
 * @return   mixed
 */
function tip_jar_wp_cancel_arrangement_handler() {

	// Verify the nonce
	if ( ! isset( $_POST['tip_jar_wp_cancel_arrangement_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tip_jar_wp_cancel_arrangement_nonce'] ) ), 'tip_jar_wp_cancel_arrangement_nonce' ) ) {
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

	// If json_decode failed, the JSON is invalid.
	if ( ! is_array( $_POST ) || ! isset( $_POST['tip_jar_wp_arrangement_id'] ) ) {
		return array(
			'success'        => false,
			'error_code'     => 'invalid_params',
			// 'frontend_nonces' => tip_jar_wp_refresh_and_get_frontend_nonces(),
			'user_logged_in' => $user->ID ? true : false,
		);
	}

	$tip_jar_wp_arrangement_id = absint( $_POST['tip_jar_wp_arrangement_id'] );

	$arrangement = new Tip_Jar_WP_Arrangement( $tip_jar_wp_arrangement_id );

	if ( 0 === $arrangement->id ) {
		return array(
			'success'        => false,
			'error_code'     => 'no_arrangement_found',
			'details'        => 'No Plan found with that ID',
			// 'frontend_nonces' => tip_jar_wp_refresh_and_get_frontend_nonces(),
			'user_logged_in' => $user->ID ? true : false,
		);
	}

	if ( absint( $user->ID ) !== absint( $arrangement->user_id ) ) {
		return array(
			'success'        => false,
			'error_code'     => 'invalid_user',
			'details'        => 'Invalid user',
			// 'frontend_nonces' => tip_jar_wp_refresh_and_get_frontend_nonces(),
			'user_logged_in' => $user->ID ? true : false,
		);
	}

	// If arrangement was found, cancel it.
	$cancellation_result = tip_jar_wp_cancel_stripe_subscription( $arrangement, 'cancelled_by_user' );

	if ( ! $cancellation_result['success'] ) {
		return array(
			'success'          => false,
			'error_code'       => $cancellation_result['error_code'],
			'details'          => $cancellation_result['details'],
			'arrangement_info' => array(
				'arrangement_id'                  => $arrangement->id,
				'arrangement_date'                => $arrangement->date,
				'arrangement_amount_per_interval' => tip_jar_wp_get_visible_amount( $arrangement->renewal_amount, $arrangement->currency ) . ' ' . __( 'per', 'tip-jar-wp' ) . ' ' . $arrangement->interval_string,
				'recurring_status'                => $arrangement->recurring_status,
			),
			// 'frontend_nonces' => tip_jar_wp_refresh_and_get_frontend_nonces(),
			'user_logged_in'   => $user->ID ? true : false,
		);
	} else {
		return array(
			'success'          => true,
			'arrangement_info' => tip_jar_wp_arrangement_info_format_for_endpoint( $arrangement ),
			// 'frontend_nonces' => tip_jar_wp_refresh_and_get_frontend_nonces(),
			'user_logged_in'   => $user->ID ? true : false,
		);
	}

}
