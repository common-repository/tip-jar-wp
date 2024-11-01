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
 * Endpoint which gets arrangements for the currently-logged-in user. It is separated out like this so it can be unit tested.
 *
 * @access   public
 * @since    1.0.0
 * @return   mixed
 */
function tip_jar_wp_get_arrangements_endpoint() {

	if ( ! isset( $_GET['tip_jar_wp_get_arrangements'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}

	$endpoint_result = tip_jar_wp_get_arrangements_handler();

	echo wp_json_encode( $endpoint_result );
	die();
}
add_action( 'init', 'tip_jar_wp_get_arrangements_endpoint' );

/**
 * Get Arrangements from the frontend
 *
 * @access   public
 * @since    1.0.0
 * @return   array
 */
function tip_jar_wp_get_arrangements_handler() {

	// Verify the nonce.
	if ( ! isset( $_POST['tip_jar_wp_get_arrangements_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tip_jar_wp_get_arrangements_nonce'] ) ), 'tip_jar_wp_get_arrangements_nonce' ) ) {
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
	if (
		! is_array( $_POST ) ||
		! isset( $_POST['tip_jar_wp_current_page'] ) ||
		! isset( $_POST['tip_jar_wp_items_per_page'] ) ||
		! isset( $_POST['tip_jar_wp_search_term'] ) ) {
		return array(
			'success'    => false,
			'error_code' => 'invalid_params',
		);
	}

	$tip_jar_wp_current_page   = absint( $_POST['tip_jar_wp_current_page'] );
	$tip_jar_wp_items_per_page = absint( $_POST['tip_jar_wp_items_per_page'] );
	$tip_jar_wp_search_term    = sanitize_text_field( wp_unslash( $_POST['tip_jar_wp_search_term'] ) );

	$query_args = array(
		'orderby'                => 'id',
		'column_values_included' => array(
			'user_id'         => $user->ID,
			// Query test arrangements in test mode, and live ones in live mode.
			'is_live_mode'    => tip_jar_wp_stripe_is_live_mode() ? 1 : 0,
			// We only want agreements with active subscriptions for the frontend.
			'interval_string' => array(
				'week'  => 'week',
				'month' => 'month',
				'year'  => 'year',
			),
		),
	);

	// If there was a search term submitted.
	if ( $tip_jar_wp_search_term ) {

		// Add the search term to the query.
		$query_args['search']         = '*' . $tip_jar_wp_search_term;
		$query_args['search_columns'] = array(
			'id',
			'user_id',

			// 'date_created',
			// 'initial_transaction_id',

			'interval_count',
			'interval_string',

			// 'currency',
			'initial_amount',
			'renewal_amount',

			// 'recurring_status',
			// 'gateway_subscription_id'
		);

	}

	// Add the number of items to get and the offset to the query.
	if ( $tip_jar_wp_current_page && $tip_jar_wp_items_per_page ) {

		$offset               = ( $tip_jar_wp_current_page * $tip_jar_wp_items_per_page ) - $tip_jar_wp_items_per_page;
		$query_args['number'] = $tip_jar_wp_items_per_page;
		$query_args['offset'] = $offset;

	}

	$columns = array(
		'id'                  => __( 'ID', 'tip-jar-wp' ),
		'amount_per_interval' => __( 'Plan', 'tip-jar-wp' ),
		'date_created'        => __( 'Date', 'tip-jar-wp' ),
		'status'              => __( 'Status', 'tip-jar-wp' ),
		'manage'              => __( 'Action', 'tip-jar-wp' ),
	);

	$arrangements = tip_jar_wp_get_arrangement_history_frontend( $query_args, $columns );

	// If arrangements were found.
	return array(
		'success'        => true,
		'columns'        => $columns,
		'rows'           => $arrangements['rows'],
		'total_items'    => $arrangements['count'],
		// 'frontend_nonces' => tip_jar_wp_refresh_and_get_frontend_nonces(),
		'user_logged_in' => $user->ID ? true : false,
	);

}
