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
 * Endpoint which gets notes attached to transactions.
 *
 * @access   public
 * @since    1.0.0
 * @return   mixed
 */
function tip_jar_wp_add_or_update_note_endpoint() {

	if ( ! isset( $_GET['tip_jar_wp_add_or_update_note'] ) ) {
		return false;
	}

	$endpoint_result = tip_jar_wp_add_or_update_note_handler();

	echo wp_json_encode( $endpoint_result );
	die();
}
add_action( 'init', 'tip_jar_wp_add_or_update_note_endpoint' );

/**
 * Add a note from the frontend
 *
 * @access   public
 * @since    1.0.0
 * @return   array
 */
function tip_jar_wp_add_or_update_note_handler() {

	if ( ! is_user_logged_in() ) {
		return array(
			'success'    => false,
			'error_code' => 'not_logged_in',
			'details'    => 'You must be logged in to leave a comment.',
		);
	}

	// Verify the nonce.
	if ( ! isset( $_POST['add_or_update_note_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['add_or_update_note_nonce'] ) ), 'tip_jar_wp_add_or_update_note_nonce' ) ) {
		return array(
			'success'    => false,
			'error_code' => 'nonce_failed',
			'details'    => 'Nonce failed.',
		);
	}

	// Make sure required values are included.
	if (
		! is_array( $_POST ) ||
		! isset( $_POST['note_id'] ) ||
		! isset( $_POST['note_content'] ) ||
		! isset( $_POST['is_reply_to'] )
	) {
		return array(
			'success'    => false,
			'error_code' => 'invalid_params',
			'details'    => 'Invalid Paramaters.',
		);
	}

	// Sanitize the input values.
	$note_id      = absint( $_POST['note_id'] );
	$note_content = sanitize_text_field( wp_unslash( $_POST['note_content'] ) );
	$is_reply_to  = absint( $_POST['is_reply_to'] );

	// Get the currently logged in user.
	$current_user  = wp_get_current_user();

	// Get the note that this is a reply to.
	$replying_to_note = new Tip_Jar_WP_Note( $is_reply_to, 'id' );

	// Get the transaction that started this thread.
	$transaction = new Tip_Jar_WP_Transaction( $replying_to_note->transaction_id, 'id' );

		// If we are updating a note...
	if ( ! empty( $note_id ) ) {
		$updating_note = new Tip_Jar_WP_Note( $note_id, 'id' );

		if ( ! $updating_note->id ) {
			return array(
				'success'    => false,
				'error_code' => 'invalid_note_id',
				'details'    => 'Invalid note ID.',
			);
		}

		// If this user is not the author of the note being updated, return.
		if ( ! current_user_can( 'do_tipjarwp_manager_things' ) && absint( $current_user->ID ) !== absint( $updating_note->user_id ) ) {
			return array(
				'success'    => false,
				'error_code' => 'invalid_user',
				'details'    => 'Invalid user.',
			);
		}

		// Update the note in question.
		$note_data = array(
			'user_id'        => absint( $current_user->ID ),
			'transaction_id' => absint( $original_note->transaction_id ),
			'is_reply_to'    => $is_reply_to,
			'note_content'   => $note_content,
		);
		$updating_note->update( $note_data );

		return array(
			'success' => true,
			'note'    => $updating_note,
		);

		// If this is a new note being added, not updated.
	} else {

		// If this user is not the author of the original note in this thread, return.
		if ( ! current_user_can( 'do_tipjarwp_manager_things' ) && absint( $current_user->ID ) !== absint( $transaction->user_id ) ) {
			return array(
				'success'    => false,
				'error_code' => 'invalid_user',
				'details'    => 'Invalid user.',
			);
		}

		// Create a new note.
		$note      = new Tip_Jar_WP_Note();
		$note_data = array(
			'user_id'        => absint( $current_user->ID ),
			'transaction_id' => absint( $transaction->id ),
			'is_reply_to'    => $is_reply_to,
			'note_content'   => $note_content,
		);
		$note->create( $note_data );

		// Format note for frontend.
		$formatted_note = array(
			'id'             => $note->id,
			'date'           => $note->date_created,
			'note_content'   => $note->note_content,
			'display_name'   => ! empty( $current_user->display_name ) ? $current_user->display_name : __( 'Anonymous', 'tip-jar-wp' ),
			'user_avatar'    => get_avatar_url( $current_user->user_email ),
			'user_can_reply' => current_user_can( 'do_tipjarwp_manager_things' ) || absint( $current_user->ID ) === absint( $transaction->user_id ),
			'is_reply_to'    => $note->is_reply_to,
			'replies'        => array(),
		);

		return array(
			'success' => true,
			'note'    => $formatted_note,
		);
	}

}
