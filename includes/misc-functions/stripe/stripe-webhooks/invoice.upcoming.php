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
 * Handle the Stripe Webhook for invoice.upcoming
 *
 * @since    1.0.0
 * @param    array $webhook_data This is the data from Stripe for this webhook.
 * @return   string The action taken by this webhook handler.
 */
function tip_jar_wp_stripe_webhook_invoice_upcoming( $webhook_data ) {

	// Let's email the user to let them know they are going to be charged.

	// First let's get the arrangement ID with this subscription ID.
	$arrangement = new Tip_Jar_WP_Arrangement( $webhook_data['data']['object']['subscription'], 'gateway_subscription_id' );

	// If no arrangement was found with that sub ID, do nothing.
	if ( ! $arrangement->id ) {

		// translators: The ID of the Stripe subscription that could not be found in the Tip Jar WP Database.
		$action_description = sprintf( __( 'Tip Jar WP found no arrangement found with that sub ID: %s', 'tip-jar-wp' ), $webhook_data['data']['object']['subscription'] );
		return $action_description;

	}

	$next_payment_attempt_date = $webhook_data['data']['object']['next_payment_attempt'];
	$amount_to_be_charged      = $webhook_data['data']['object']['amount_remaining'];

	// Send a renewal reminder.
	$email_sent = tip_jar_wp_send_renewal_reminder_email( $arrangement, $next_payment_attempt_date, $amount_to_be_charged );

	if ( $email_sent ) {
		// translators: The ID of the arrangement for which an email reminder was just sent.
		$action_description = sprintf( __( 'Tip Jar WP sent reminder email to customer for arrangement %s', 'tip-jar-wp' ), $arrangement->id );
	} else {
		// translators: The ID of the arrangement for which an email reminder was just attempted, but failed.
		$action_description = sprintf( __( 'Tip Jar WP tried to send reminder email to customer for arrangement %s, but failed.', 'tip-jar-wp' ), $arrangement->id );
	}

	return $action_description;

}
