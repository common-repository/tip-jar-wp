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
function tip_jar_wp_get_notes_endpoint() {

	if ( ! isset( $_GET['tip_jar_wp_get_notes'] ) ) {
		return false;
	}

	$endpoint_result = tip_jar_wp_get_notes_handler();

	echo wp_json_encode( $endpoint_result );
	die();
}
add_action( 'init', 'tip_jar_wp_get_notes_endpoint' );

/**
 * Get notes from the frontend
 *
 * @access   public
 * @since    1.0.0
 * @return   array
 */
function tip_jar_wp_get_notes_handler() {

	// Verify the nonce.
	if ( ! isset( $_POST['get_notes_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['get_notes_nonce'] ) ), 'tip_jar_wp_get_notes_nonce' ) ) {
		return array(
			'success'    => false,
			'error_code' => 'nonce_failed',
			'details'    => 'Nonce failed.',
		);
	}

	// Make sure required values are included.
	if (
		! is_array( $_POST ) ||
		! isset( $_POST['current_page'] ) ||
		! isset( $_POST['notes_per_page'] ) ||
		! isset( $_POST['form_id'] ) ) {
		return array(
			'success'    => false,
			'error_code' => 'invalid_params.',
		);
	}

	$current_page   = absint( $_POST['current_page'] );
	$notes_per_page = absint( $_POST['notes_per_page'] );
	$form_id        = absint( $_POST['form_id'] );

	$query_args = array(
		'orderby' => 'date_created',
	);

	if ( ! empty( $form_id ) ) {
		$query_args['column_values_included']['form_id'] = $form_id;
	}

	// Add the number of items to get and the offset to the query.
	if ( $current_page && $notes_per_page ) {

		$offset               = ( $current_page * $notes_per_page ) - $notes_per_page;
		$query_args['number'] = $notes_per_page;
		$query_args['offset'] = $offset;

	}

	$notes = tip_jar_wp_get_notes_frontend( $query_args );

	// If transactions were found.
	return array(
		'success' => true,
		'notes'   => $notes,
	);

}
