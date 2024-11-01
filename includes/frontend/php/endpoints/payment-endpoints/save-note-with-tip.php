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
 * Save note with tip API Endpoint It is separated out like this so it can be unit tested.
 *
 * @access   public
 * @since    1.0.0
 * @return   mixed
 */
function tip_jar_wp_save_note_with_tip_endpoint() {

	if ( ! isset( $_GET['tip_jar_wp_save_note_with_tip'] ) ) {
		return false;
	}

	$endpoint_result = tip_jar_wp_save_note_with_tip_handler();

	echo wp_json_encode( $endpoint_result );
	die();
}
add_action( 'init', 'tip_jar_wp_save_note_with_tip_endpoint' );

/**
 * Save note with tip API Endpoint
 *
 * @access   public
 * @since    1.0.0
 * @return   array
 */
function tip_jar_wp_save_note_with_tip_handler() {

	// If the person is not logged in, check their payment session.
	if ( ! is_user_logged_in() ) {

		if (
			! isset( $_POST['tip_jar_wp_user_id'] ) ||
			! isset( $_POST['tip_jar_wp_transaction_id'] ) ||
			! isset( $_POST['tip_jar_wp_session_id'] )
		) {
			return array(
				'success'    => false,
				'error_code' => 'invalid_session',
				'details'    => 'Invalid session',
			);
		}

		// Check if the payment session is valid. This proves the person saving the note with the tip is the person who did the payment, even though they might be logged out.
		$user_id        = absint( $_POST['tip_jar_wp_user_id'] );
		$transaction_id = absint( $_POST['tip_jar_wp_transaction_id'] );
		$session_id     = sanitize_text_field( wp_unslash( $_POST['tip_jar_wp_session_id'] ) );

		// If this payment session does not validate, this is not a valid attempt at updating the note.
		if ( ! tip_jar_wp_payment_session_valid( $user_id, $transaction_id, $session_id ) ) {
			return array(
				'success'    => false,
				'error_code' => 'invalid_session',
				'details'    => 'Invalid session',
			);
		}
	} else {
		// Verify the nonce.
		if ( ! isset( $_POST['tip_jar_wp_note_with_tip_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tip_jar_wp_note_with_tip_nonce'] ) ), 'tip_jar_wp_note_with_tip' ) ) {
			return array(
				'success'    => false,
				'error_code' => 'nonce_failed',
				'details'    => 'Nonce failed.',
			);
		}
	}

	// Check if values were not there that need to be.
	if ( ! is_array( $_POST ) || ! isset( $_POST['tip_jar_wp_transaction_id'] ) || ! isset( $_POST['tip_jar_wp_note_with_tip'] ) ) {
		return array(
			'success'    => false,
			'error_code' => 'missing_values',
			'details'    => 'Note with tip request was incorrect.',
		);
	}

	// Get the transaction object which we are adding the note to.
	$transaction = new Tip_Jar_WP_Transaction( absint( $_POST['tip_jar_wp_transaction_id'] ) );

	if ( 0 === $transaction->id ) {
		return array(
			'success'    => false,
			'error_code' => 'invalid_transaction_id_given',
			'details'    => 'No Transaction found with that ID',
		);
	}

	// If the person is logged in (if logged out, they are already confirmed valid using the payment session above).
	if ( is_user_logged_in() ) {

		// Double check that the user logged in is the same user attached to the transaction being updated.
		if ( intval( $transaction->user_id ) !== intval( get_current_user_id() ) ) {
			return array(
				'success'    => false,
				'error_code' => 'invalid_user',
				'details'    => 'Invalid User',
			);
		}
	}

	$note_content = sanitize_text_field( wp_unslash( $_POST['tip_jar_wp_note_with_tip'] ) );

	$note_with_tip = new Tip_Jar_WP_Note( $transaction->note_with_tip );

	if ( ! $note_with_tip || 0 === $note_with_tip->id ) {
		$note_with_tip = new Tip_Jar_WP_Note();
		$note_data     = array(
			'user_id'        => absint( $transaction->user_id ),
			'transaction_id' => absint( $transaction->id ),
			'is_reply_to'    => 0,
			'note_content'   => $note_content,
		);
		$note_with_tip->create( $note_data );
	}

	$note_with_tip_data = $note_with_tip->update(
		array(
			'note_content' => $note_content,
		)
	);

	$transaction->update(
		array(
			'note_with_tip' => absint( $note_with_tip->id ),
		)
	);

	return array(
		'success'          => $note_with_tip_data['success'],
		'transaction_id'   => $transaction->id,
		'transaction_data' => $note_with_tip_data,
	);

}
