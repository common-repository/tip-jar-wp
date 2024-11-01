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
 * Get Arrangement History, with queryable variables to modify results.
 *
 * @since    1.0.0
 * @param mixed $query_args {
 *     Optional. Array or query string of item query parameters. Default empty.
 *
 *     @type int          $number         Maximum number of items to retrieve. Default 20.
 *     @type int          $offset         Number of items to offset the query. Default 0.
 *     @type string|array $orderby        Transactions status or array of statuses. To use 'meta_value'
 *                                        or 'meta_value_num', `$meta_key` must also be provided.
 *                                        To sort by a specific `$meta_query` clause, use that
 *                                        clause's array key. Accepts 'id', 'user_id', 'name',
 *                                        'email', 'payment_ids', 'purchase_value', 'purchase_count',
 *                                        'notes', 'date_created', 'meta_value', 'meta_value_num',
 *                                        the value of `$meta_key`, and the array keys of `$meta_query`.
 *                                        Also accepts false, an empty array, or 'none' to disable the
 *                                        `ORDER BY` clause. Default 'id'.
 *     @type string       $order          How to order retrieved items. Accepts 'ASC', 'DESC'.
 *                                        Default 'DESC'.
 *     @type string|array $columns_values_included        String or array of item IDs to include. Default empty.
 *     @type string|array $columns_values_excluded        String or array of item IDs to exclude. Default empty.
 *                                        empty.
 *     @type string       $search         Search term(s) to retrieve matching items for. Searches
 *                                        through item names. Default empty.
 *     @type string|array $search_columns Columns to search using the value of `$search`. Default 'name'.
 *     @type array        $date_query     Date query clauses to limit retrieved items by.
 *                                        See `WP_Date_Query`. Default empty.
 *     @type bool         $count          Whether to return a count (true) instead of an array of
 *                                        item objects. Default false.
 *     @type bool         $no_found_rows  Whether to disable the `SQL_CALC_FOUND_ROWS` query.
 *
 * @param    array $columns_to_return The columns we would like to get.
 * @return   array
 */
function tip_jar_wp_get_arrangement_history_frontend( $query_args = array(), $columns_to_return = ['id'] ) {

	$arrangements_db = new Tip_Jar_WP_Arrangements_DB();
	$arrangements    = $arrangements_db->get_arrangements( $query_args );

	// Create an array of rows that we'll use to output the rows in React.
	$rows = array();

	// Loop through each arrangement.
	foreach ( $arrangements as $arrangement ) {

		// Get the User's Info.
		$user = get_userdata( $arrangement->user_id );

		if ( empty( $user ) ) {
			continue;
		}

		// Format the row data.
		$row = array();

		if ( array_key_exists( 'manage', $columns_to_return ) ) {
			$row['manage'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Manage', 'tip-jar-wp' ),
				'description'              => __( 'This is simply a button which can be used to manage this plan.', 'tip-jar-wp' ),
				'value'                    => __( 'Manage', 'tip-jar-wp' ),
			);
		}

		if ( array_key_exists( 'status', $columns_to_return ) ) {
			$row['status'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Status', 'tip-jar-wp' ),
				'description'              => __( 'This indicates the status of the plan.', 'tip-jar-wp' ),
				'value'                    => ucfirst( $arrangement->recurring_status ),
			);
		}

		if ( array_key_exists( 'id', $columns_to_return ) ) {
			$row['id'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'ID', 'tip-jar-wp' ),
				'description'              => __( 'This is the ID of the arrangement in your WordPress database.', 'tip-jar-wp' ),
				'value'                    => $arrangement->id,
			);
		}

		if ( array_key_exists( 'date_created', $columns_to_return ) ) {
			$row['date_created'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Created Date', 'tip-jar-wp' ),
				'description'              => __( 'This is the date the Plan was created.', 'tip-jar-wp' ),
				'value_type'               => 'date',
				'value_format_function'    => 'tip_jar_wp_list_view_format_date',
				'value'                    => $arrangement->date_created,
			);
		}

		if ( array_key_exists( 'user', $columns_to_return ) ) {
			$row['user'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'User', 'tip-jar-wp' ),
				'description'              => __( 'This is the email of the user who tipped you.', 'tip-jar-wp' ),
				'value'                    => $user->user_email,
			);
		}

		if ( array_key_exists( 'initial_transaction_id', $columns_to_return ) ) {
			$row['initial_transaction_id'] = array(
				'show_in_list_view'        => false,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Initial Transaction ID', 'tip-jar-wp' ),
				'description'              => __( 'This is ID of the original transaction in thus plan.', 'tip-jar-wp' ),
				'value'                    => $arrangement->initial_transaction_id,
			);
		}

		if ( array_key_exists( 'amount_per_interval', $columns_to_return ) ) {
			$row['amount_per_interval'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Interval', 'tip-jar-wp' ),
				'description'              => __( 'This is space between payments.', 'tip-jar-wp' ),
				'value_type'               => 'money',
				'value_format_function'    => 'tip_jar_wp_list_view_format_money',
				'currency'                 => $arrangement->currency,
				'is_zero_decimal_currency' => tip_jar_wp_is_a_zero_decimal_currency( $arrangement->currency ),
				'cents'                    => $arrangement->renewal_amount,
				'string_after'             => ' ' . __( 'per', 'tip-jar-wp' ) . ' ' . $arrangement->interval_string,
			);
		}

		if ( array_key_exists( 'currency', $columns_to_return ) ) {
			$row['currency'] = array(
				'show_in_list_view'        => false,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Currency', 'tip-jar-wp' ),
				'description'              => __( 'This is currency of the arrangement.', 'tip-jar-wp' ),
				'value'                    => $arrangement->currency,
			);
		}

		if ( array_key_exists( 'initial_amount', $columns_to_return ) ) {
			$row['initial_amount'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Initial Amount', 'tip-jar-wp' ),
				'description'              => __( 'This is the initial amount of the plan.', 'tip-jar-wp' ),
				'value_type'               => 'money',
				'value_format_function'    => 'tip_jar_wp_list_view_format_money',
				'currency'                 => $arrangement->currency,
				'is_zero_decimal_currency' => tip_jar_wp_is_a_zero_decimal_currency( $arrangement->currency ),
				'cents'                    => $arrangement->initial_amount,
				'string_after'             => ' (' . strtoupper( $arrangement->currency ) . ')',

			);
		}

		if ( array_key_exists( 'renewal_amount', $columns_to_return ) ) {
			$row['renewal_amount'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Renewal Amount', 'tip-jar-wp' ),
				'description'              => __( 'This is the renewal amount of the plan.', 'tip-jar-wp' ),
				'value_type'               => 'money',
				'value_format_function'    => 'tip_jar_wp_list_view_format_money',
				'currency'                 => $arrangement->currency,
				'is_zero_decimal_currency' => tip_jar_wp_is_a_zero_decimal_currency( $arrangement->currency ),
				'cents'                    => $arrangement->renewal_amount,
				'string_after'             => ' (' . strtoupper( $arrangement->currency ) . ')',
			);
		}

		if ( array_key_exists( 'recurring_status', $columns_to_return ) ) {
			$row['recurring_status'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Recurring Status', 'tip-jar-wp' ),
				'description'              => __( 'This is the status of the plan.', 'tip-jar-wp' ),
				'value'                    => $arrangement->recurring_status,
			);
		}

		$rows[] = $row;
	}

	return array(
		'rows'  => $rows,
		'count' => $arrangements_db->count( $query_args ),
	);

}

/**
 * Get Transaction History, with queryable variables to modify results.
 *
 * @since 1.0.0
 * @param array $query_args {
 *     Optional. Array or query string of item query parameters. Default empty.
 *
 *     @type int          $number         Maximum number of items to retrieve. Default 20.
 *     @type int          $offset         Number of items to offset the query. Default 0.
 *     @type string|array $orderby        Transactions status or array of statuses. To use 'meta_value'
 *                                        or 'meta_value_num', `$meta_key` must also be provided.
 *                                        To sort by a specific `$meta_query` clause, use that
 *                                        clause's array key. Accepts 'id', 'user_id', 'name',
 *                                        'email', 'payment_ids', 'purchase_value', 'purchase_count',
 *                                        'notes', 'date_created', 'meta_value', 'meta_value_num',
 *                                        the value of `$meta_key`, and the array keys of `$meta_query`.
 *                                        Also accepts false, an empty array, or 'none' to disable the
 *                                        `ORDER BY` clause. Default 'id'.
 *     @type string       $order          How to order retrieved items. Accepts 'ASC', 'DESC'.
 *                                        Default 'DESC'.
 *     @type string|array $columns_values_included        String or array of item IDs to include. Default empty.
 *     @type string|array $columns_values_excluded        String or array of item IDs to exclude. Default empty.
 *                                        empty.
 *     @type string       $search         Search term(s) to retrieve matching items for. Searches
 *                                        through item names. Default empty.
 *     @type string|array $search_columns Columns to search using the value of `$search`. Default 'name'.
 *     @type array        $date_query     Date query clauses to limit retrieved items by.
 *                                        See `WP_Date_Query`. Default empty.
 *     @type bool         $count          Whether to return a count (true) instead of an array of
 *                                        item objects. Default false.
 *     @type bool         $no_found_rows  Whether to disable the `SQL_CALC_FOUND_ROWS` query.
 *                                        Default true.
 * @param  array $columns_to_return The columns we want to return.
 * @return array
 */
function tip_jar_wp_get_transaction_history_frontend( $query_args = array(), $columns_to_return = ['id'] ) {

	$transactions_db = new Tip_Jar_WP_Transactions_DB();
	$transactions    = $transactions_db->get_transactions( $query_args );

	// Create an array of rows that we'll use to output the rows in React.
	$rows = array();

	// Loop through each transaction.
	foreach ( $transactions as $transaction ) {

		// Get the User's Info.
		$user = get_userdata( $transaction->user_id );

		if ( empty( $user ) ) {
			continue;
		}

		// Format the row data.
		$row = array();

		if ( array_key_exists( 'manage', $columns_to_return ) ) {
			$row['manage'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Manage', 'tip-jar-wp' ),
				'description'              => __( 'This is simply a button which can be used to manage this transaction.', 'tip-jar-wp' ),
				'value'                    => __( 'Receipt', 'tip-jar-wp' ),
			);
		}

		if ( array_key_exists( 'id', $columns_to_return ) ) {
			$row['id'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'ID', 'tip-jar-wp' ),
				'description'              => __( 'This is the ID of the transaction in your WordPress database.', 'tip-jar-wp' ),
				'value'                    => $transaction->id,
			);
		}

		if ( array_key_exists( 'date_created', $columns_to_return ) ) {
			$row['date_created'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Date', 'tip-jar-wp' ),
				'description'              => __( 'This is the date the transaction was created.', 'tip-jar-wp' ),
				'value_type'               => 'date',
				'value_format_function'    => 'tip_jar_wp_list_view_format_date',
				'value'                    => $transaction->date_created,
			);
		}

		if ( array_key_exists( 'date_paid', $columns_to_return ) ) {
			$row['date_paid'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Date', 'tip-jar-wp' ),
				'description'              => __( 'This is the date the transaction was paid.', 'tip-jar-wp' ),
				'value_type'               => 'date',
				'value_format_function'    => 'tip_jar_wp_list_view_format_date',
				'value'                    => $transaction->date_paid,
			);
		}

		if ( array_key_exists( 'user', $columns_to_return ) ) {
			$row['user'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'User', 'tip-jar-wp' ),
				'description'              => __( 'This is the email of the user who tipped you.', 'tip-jar-wp' ),
				'value'                    => $user->user_email,
			);
		}

		if ( array_key_exists( 'note_with_tip', $columns_to_return ) ) {
			$row['note_with_tip'] = array(
				'show_in_list_view'        => false,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Note with tip', 'tip-jar-wp' ),
				'description'              => __( 'This is the note the user provided with their tip.', 'tip-jar-wp' ),
				'value'                    => $transaction->note_with_tip,
			);
		}

		if ( array_key_exists( 'amount', $columns_to_return ) ) {
			$row['amount'] = array(
				'show_in_list_view'        => true,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Amount', 'tip-jar-wp' ),
				'description'              => __( 'This is the amount of the tip.', 'tip-jar-wp' ),
				'value_type'               => 'money',
				'value_format_function'    => 'tip_jar_wp_list_view_format_money',
				'currency'                 => $transaction->charged_currency,
				'is_zero_decimal_currency' => tip_jar_wp_is_a_zero_decimal_currency( $transaction->charged_currency ),
				'cents'                    => $transaction->charged_amount,
				'string_after'             => ' (' . strtoupper( $transaction->charged_currency ) . ')',
			);
		}

		if ( array_key_exists( 'gateway_fee', $columns_to_return ) ) {
			$row['gateway_fee'] = array(
				'show_in_list_view'        => false,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Stripe Fee', 'tip-jar-wp' ),
				'description'              => __( 'This is the amount Stripe charged to process this transaction.', 'tip-jar-wp' ),
				'value_type'               => 'money',
				'value_format_function'    => 'tip_jar_wp_list_view_format_money',
				'currency'                 => $transaction->home_currency,
				'is_zero_decimal_currency' => tip_jar_wp_is_a_zero_decimal_currency( $transaction->home_currency ),
				'cents'                    => $transaction->gateway_fee_hc,
				'string_after'             => ' (' . strtoupper( $transaction->home_currency ) . ')',
			);
		}

		if ( array_key_exists( 'earnings_hc', $columns_to_return ) ) {
			$row['earnings_hc'] = array(
				'show_in_list_view'        => false,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Actual earnings after Stripe fees', 'tip-jar-wp' ),
				'description'              => __( 'This is the amount that Stripe will/did deposit into your bank account.', 'tip-jar-wp' ),
				'value_type'               => 'money',
				'value_format_function'    => 'tip_jar_wp_list_view_format_money',
				'currency'                 => $transaction->home_currency,
				'is_zero_decimal_currency' => tip_jar_wp_is_a_zero_decimal_currency( $transaction->home_currency ),
				'cents'                    => $transaction->earnings_hc,
				'string_after'             => ' (' . strtoupper( $transaction->home_currency ) . ')',
			);
		}

		if ( array_key_exists( 'page_url', $columns_to_return ) ) {
			$row['page_url'] = array(
				'show_in_list_view'        => false,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Page URL', 'tip-jar-wp' ),
				'description'              => __( 'This is the URL of the page where the tip took place.', 'tip-jar-wp' ),
				'value'                    => $transaction->page_url,
			);
		}

		if ( array_key_exists( 'method', $columns_to_return ) ) {
			$row['method'] = array(
				'show_in_list_view'        => false,
				'show_in_single_data_view' => true,
				'title'                    => __( 'Payment Method', 'tip-jar-wp' ),
				'description'              => __( 'This is the method used to pay (Apple Pay, Google Pay, Basic Card, etc).', 'tip-jar-wp' ),
				'value'                    => $transaction->method,
			);
		}

		$rows[] = $row;
	}

	return array(
		'rows'  => $rows,
		'count' => $transactions_db->count( $query_args ),
	);

}

/**
 * Query function which returns notes attached to transactions.
 *
 * @access   public
 * @since    1.0.1.3
 * @param    array $query_args The args used to set the query.
 * @return   array
 */
function tip_jar_wp_get_notes_frontend( $query_args ) {

	$notes_db        = new Tip_Jar_WP_Notes_DB();
	$transactions_db = new Tip_Jar_WP_Transactions_DB();
	$transactions    = $transactions_db->get_transactions( $query_args );
	$current_user    = wp_get_current_user();

	// Create an array of notes that we'll use to output the rows in React.
	$notes = array();

	// Loop through each transaction.
	foreach ( $transactions as $transaction ) {

		$note_with_tip = new Tip_Jar_WP_Note( $transaction->note_with_tip );

		// If no note has been attached to this transaction, skip it.
		if ( ! $note_with_tip || 0 === $note_with_tip->id || empty( $note_with_tip->note_content ) ) {
			continue;
		}

		// Get the replies to this note.
		$reply_query_args = array(
			'number'                 => $query_args['number'],
			'column_values_included' => array(
				'is_reply_to' => $note_with_tip->id,
			),
		);
		$replies          = tip_jar_wp_get_note_replies_frontend( $reply_query_args, $transaction );

		$user    = get_user_by( 'id', $note_with_tip->user_id );
		$notes[] = array(
			'id'                       => $note_with_tip->id,
			'date'                     => $note_with_tip->date_created,
			'note_content'             => $note_with_tip->note_content,
			'display_name'             => ! empty( $user->display_name ) ? $user->display_name : __( 'Anonymous', 'tip-jar-wp' ),
			'user_avatar'              => get_avatar_url( $user->user_email ),
			'user_can_reply'           => current_user_can( 'do_tipjarwp_manager_things' ) || absint( $current_user->ID ) === absint( $transaction->user_id ),
			'amount'                   => $transaction->charged_amount,
			'currency'                 => $transaction->charged_currency,
			'is_zero_decimal_currency' => tip_jar_wp_is_a_zero_decimal_currency( $transaction->charged_currency ),
			'replies'                  => $replies,
		);
	}

	return $notes;
}

/**
 * Query function which returns replies attached to a note, with replies to those replies at infinite recurrsion depth.
 *
 * @access   public
 * @since    1.0.1.3
 * @param    array $reply_query_args The args used to set the query.
 * @param    array $transaction The transaction DB row which created the original note in this thread.
 * @return   array
 */
function tip_jar_wp_get_note_replies_frontend( $reply_query_args, $transaction ) {

	$notes_db     = new Tip_Jar_WP_Notes_DB();
	$current_user = wp_get_current_user();

	// Create an array of notes that we'll use to output the rows in React.
	$replies = array();

	// We want to get replies by oldest->newest so a thread is easy to follow.
	$reply_query_args['orderby'] = 'date_created';
	$reply_query_args['order']   = 'ASC';

	// Get the replies using the query args passed in.
	$replies_found = $notes_db->get_notes( $reply_query_args );

	foreach ( $replies_found as $reply_key => $reply ) {

		// If the note is empty, skit it.
		if ( empty( $reply->note_content ) ) {
			continue;
		}

		// Check if there are replies to this reply.
		$reply_query_args['column_values_included']['is_reply_to'] = $reply->id;
		$replies_to_reply = tip_jar_wp_get_note_replies_frontend( $reply_query_args, $transaction );

		$reply_user            = get_user_by( 'id', $reply->user_id );
		$replies[ $reply_key ] = array(
			'id'             => $reply->id,
			'date'           => $reply->date_created,
			'note_content'   => $reply->note_content,
			'display_name'   => ! empty( $reply_user->display_name ) ? $reply_user->display_name : __( 'Anonymous', 'tip-jar-wp' ),
			'user_avatar'    => get_avatar_url( $reply_user->user_email ),
			'user_can_reply' => current_user_can( 'do_tipjarwp_manager_things' ) || absint( $current_user->ID ) === absint( $transaction->user_id ),
			'replies'        => $replies_to_reply,
		);
	}

	return $replies;
}
