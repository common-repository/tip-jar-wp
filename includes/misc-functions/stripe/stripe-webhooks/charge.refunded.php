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
 * Handle the Stripe Webhook for charge.refunded
 *
 * @since    1.0.0
 * @param    array $webhook_data This is the data from Stripe for this webhook.
 * @return   string A description of the action taken by this webhook handler.
 */
function tip_jar_wp_stripe_webhook_charge_refunded( $webhook_data ) {

	$action_description = '';

	// A transaction at Stripe was refunded. Reflect that here as well by creating a new refund transaction.
	$refunds_data = $webhook_data['data']['object']['refunds']['data'];

	// Loop through all refunds in this webhook.
	foreach ( $refunds_data as $refund_data ) {

		// Fetch the balance transaction from Stripe since Stripe doesn't tell us info about the fees.
		$s = new Tip_Jar_WP_Stripe_Get(
			array(
				'url' => 'https://api.stripe.com/v1/balance/history/' . $refund_data['balance_transaction'],
			)
		);

		// Execute the call to Stripe.
		$balance_transaction = $s->call();

		if ( ! isset( $balance_transaction['currency'] ) ) {
			$action_description = wp_json_encode( $balance_transaction ) . '. ';
		}

		$home_currency  = $balance_transaction['currency'];
		$gateway_fee_hc = $balance_transaction['fee'];
		$earnings_hc    = $balance_transaction['net'];

		// Get the transaction object which is being refunded.
		$transaction_to_refund = new Tip_Jar_WP_Transaction( $refund_data['charge'], 'charge_id' );

		// Let us also make sure that any corresponding arrangements are cancelled.
		$arrangement_to_cancel = new Tip_Jar_WP_Arrangement( $transaction_to_refund->arrangement_id, 'id' );
		if ( $arrangement_to_cancel->gateway_subscription_id && 'on' === $arrangement_to_cancel->recurring_status ) {
			tip_jar_wp_cancel_stripe_subscription( $arrangement_to_cancel, 'refunded' );
		}

		// Create a new transaction entry in the Tip Jar WP "transactions" table for this refund.
		$transaction_data = array(
			'event_id'             => $webhook_data['id'],
			'user_id'              => $transaction_to_refund->user_id,
			'type'                 => 'refund',
			'gateway'              => 'Stripe',
			'method'               => $transaction_to_refund->method,
			'page_url'             => $transaction_to_refund->page_url,
			'charged_amount'       => '-' . $refund_data['amount'], // Stripe sends this as a positive, despite sending negatives in the balance transaction.
			'charged_currency'     => $refund_data['currency'],
			'home_currency'        => $home_currency,
			'gateway_fee_hc'       => $gateway_fee_hc,
			'earnings_hc'          => $earnings_hc,
			'charge_id'            => $refund_data['id'],
			'refund_id'            => $transaction_to_refund->id,
			'statement_descriptor' => $transaction_to_refund->statement_descriptor,
			'arrangement_id'       => $transaction_to_refund->arrangement_id,
			'is_live_mode'         => $webhook_data['livemode'],
		);

		$refund_transaction = new Tip_Jar_WP_Transaction();
		$refund_transaction->create( $transaction_data );

		// Add the refund transaction ID to the original transaction, the one that is being refunded.
		$transaction_data = array(
			'refund_id' => $refund_transaction->id,
		);
		$transaction_data = $transaction_to_refund->update( $transaction_data );

		// Email the customer a receipt for this refund.
		tip_jar_wp_send_refund_email( $refund_transaction );

	}

	// translators: The id of the transaction that was refunded.
	$action_description = $action_description . sprintf( __( 'Tip Jar WP refunded transaction %s', 'tip-jar-wp' ), $refund_transaction->id );

	return $action_description;

}
