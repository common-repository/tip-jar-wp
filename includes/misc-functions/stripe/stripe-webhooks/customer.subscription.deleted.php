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
 * Handle the Stripe Webhook for customer.subscription.deleted
 *
 * @since    1.0.0
 * @param    array $webhook_data This is the data from Stripe for this webhook.
 * @return   string The action taken by this webhook handler.
 */
function tip_jar_wp_stripe_webhook_customer_subscription_deleted( $webhook_data ) {

	// Let's email the user to let them know they are going to be charged.

	// First let's get the arrangement ID with this subscription ID.
	$arrangement = new Tip_Jar_WP_Arrangement( $webhook_data['data']['object']['id'], 'gateway_subscription_id' );

	// If no arrangement was found with that sub ID, do nothing.
	if ( ! $arrangement->id ) {

		// translators: The Stripe subscription ID that was not found in the Tip Jar WP database.
		$action_description = sprintf( __( 'Tip Jar WP found no arrangement found with that sub ID: %s', 'tip-jar-wp' ), $webhook_data['data']['object']['id'] );
		return $action_description;

	}

	// If we didn't send a Request to Stripe for this, it's almost definitely failing because the payment failed.
	if ( ! $webhook_data['data']['request'] ) {
		$reason = 'payment_failure';
	} else {
		$reason = 'general';
	}

	// Update the status of the arrangement so it matches Stripe, and log the reason for the cancellation.
	$arrangement->update(
		array(
			'recurring_status' => 'cancelled',
			'status_reason'    => $reason,
		)
	);

	// Send a renewal reminder to user and admin.
	tip_jar_wp_send_cancellation_notice_email_to_admin( $arrangement );
	$email_sent = tip_jar_wp_send_cancellation_notice_email( $arrangement );

	if ( $email_sent ) {
		// translators: The plan/arrangement ID for which a cancellation email was sent.
		$action_description = sprintf( __( 'Tip Jar WP sent cancellation email to customer for arrangement %s', 'tip-jar-wp' ), $arrangement->id );
	} else {
		// translators: The plan/arrangement ID for which a cancellation email was unable to be sent.
		$action_description = sprintf( __( 'Tip Jar WP tried to send cancellation email to customer for arrangement %s, but failed.', 'tip-jar-wp' ), $arrangement->id );
	}

	return $action_description;

}
