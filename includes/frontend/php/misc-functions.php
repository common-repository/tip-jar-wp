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
 * Generate the nonces
 *
 * @access   public
 * @since    1.0.0
 * @return   array
 */
function tip_jar_wp_refresh_and_get_frontend_nonces() {

	return array(
		'tip_jar_wp_email_transaction_receipt_nonce' => wp_create_nonce( 'tip_jar_wp_email_transaction_receipt_nonce' ),
		'note_with_tip_nonce'                        => wp_create_nonce( 'tip_jar_wp_note_with_tip' ),
		'email_login_nonce'                          => wp_create_nonce( 'tip_jar_wp_email_login_nonce' ),
		'login_nonce'                                => wp_create_nonce( 'tip_jar_wp_login_nonce' ),
		'get_transactions_nonce'                     => wp_create_nonce( 'tip_jar_wp_get_transactions_nonce' ),
		'get_transaction_nonce'                      => wp_create_nonce( 'tip_jar_wp_get_transaction_nonce' ),
		'get_arrangements_nonce'                     => wp_create_nonce( 'tip_jar_wp_get_arrangements_nonce' ),
		'get_arrangement_nonce'                      => wp_create_nonce( 'tip_jar_wp_get_arrangement_nonce' ),
		'update_arrangement_nonce'                   => wp_create_nonce( 'tip_jar_wp_update_arrangement_nonce' ),
		'get_arrangement_payment_method_nonce'       => wp_create_nonce( 'tip_jar_wp_get_arrangement_payment_method_nonce' ),
		'cancel_arrangement_nonce'                   => wp_create_nonce( 'tip_jar_wp_cancel_arrangement_nonce' ),
		'file_download_nonce'                        => wp_create_nonce( 'tip_jar_wp_file_download_nonce' ),
		'get_oembed_nonce'                           => wp_create_nonce( 'tip_jar_wp_get_oembed_nonce' ),
	);

}

/**
 * Force the update login cookie upon login.
 *
 * @access   public
 * @since    1.0.0
 * @param    string $logged_in_cookie The logged in cookie.
 * @return   void
 */
function tip_jar_wp_force_update_login_cookie( $logged_in_cookie ) {
	$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
}
add_action( 'set_logged_in_cookie', 'tip_jar_wp_force_update_login_cookie' );

/**
 * Create/Assemble all of the parts used on the frontend in reference to the "current_transaction_info".
 *
 * @since 1.0
 * @param Tip_Jar_WP_Transaction $transaction A transaction object.
 * @return array An formatted/predicatable array which can be used to pass a transaction to frontend endpoints
 */
function tip_jar_wp_transaction_info_format_for_endpoint( $transaction ) {

	// Get the Arrangement that this Transaction is linked with.
	$arrangement = new Tip_Jar_WP_Arrangement( $transaction->arrangement_id );
	$user        = get_user_by( 'id', $transaction->user_id );

	$note_with_tip = new Tip_Jar_WP_Note( $transaction->note_with_tip );

	return array(
		'transaction_id'                       => $transaction->id,
		'transaction_date_created'             => $transaction->date_created,
		'transaction_date_paid'                => $transaction->date_paid,
		'transaction_period_start_date'        => $transaction->period_start_date,
		'transaction_period_end_date'          => $transaction->period_end_date,
		'transaction_charged_amount'           => $transaction->charged_amount,
		'transaction_charged_currency'         => strtoupper( $transaction->charged_currency ),
		'transaction_currency_symbol'          => html_entity_decode( tip_jar_wp_currency_symbol( $transaction->charged_currency ) ),
		'transaction_currency_is_zero_decimal' => tip_jar_wp_is_a_zero_decimal_currency( $transaction->charged_currency ),
		'transaction_note_with_tip'            => $note_with_tip->note_content ? $note_with_tip->note_content : '',
		'arrangement_info'                     => tip_jar_wp_arrangement_info_format_for_endpoint( $arrangement ),
		'email'                                => $user->user_email,
		'payee_name'                           => get_bloginfo( 'name' ),
		'statement_descriptor'                 => $transaction->statement_descriptor,
	);
}

/**
 * Create/Assemble all of the parts used on the frontend in reference to the "current_arrangement_info".
 *
 * @since 1.0
 * @param Tip_Jar_WP_Arrangement $arrangement An arrangement object.
 * @return array An formatted/predicatable array which can be used to pass a transaction to frontend endpoints
 */
function tip_jar_wp_arrangement_info_format_for_endpoint( $arrangement ) {

	if ( '0000-00-00 00:00:00' !== $arrangement->current_period_end ) {
		$maybe_renewal_date = $arrangement->current_period_end;
	} else {
		$maybe_renewal_date = '';
	}

	if (
		! empty( $arrangement->gateway_subscription_id )
	) {
		$webhook_succeeded = true;
	} else {
		$webhook_succeeded  = false;
		$maybe_renewal_date = __( 'Webhook failed!', 'tip-jar-wp' );
	}

	// If this subscription requires an SCA authentication, get the payment intent id from Stripe.
	if ( 'authentication_required' === $arrangement->status_reason ) {

		// Get the data for this subscription from Stripe.
		$s = new Tip_Jar_WP_Stripe_Get(
			array(
				'url' => 'https://api.stripe.com/v1/subscriptions/' . $arrangement->gateway_subscription_id,
			)
		);

		// Execute the call to Stripe.
		$stripe_subscription = $s->call();

		// Get the latest invoice on this subscription.
		$s = new Tip_Jar_WP_Stripe_Get(
			array(
				'url' => 'https://api.stripe.com/v1/invoices/' . $stripe_subscription['latest_invoice'],
			)
		);

		// Execute the call to Stripe.
		$latest_invoice = $s->call();

		// Get the payment intent for that invoice.
		$s = new Tip_Jar_WP_Stripe_Get(
			array(
				'url' => 'https://api.stripe.com/v1/payment_intents/' . $latest_invoice['payment_intent'],
			)
		);

		// Execute the call to Stripe.
		$payment_intent = $s->call();

		$pending_invoice = array(
			'invoice'        => $latest_invoice,
			'payment_intent' => $payment_intent,
		);

	} else {
		$pending_invoice = '';
	}

	// Set the visual status for the arrangement.
	switch ( $arrangement->recurring_status ) {
		case 'on':
			$recurring_status_visible = __( 'Active', 'tip-jar-wp' );
			break;
		case 'active':
			$recurring_status_visible = __( 'Active', 'tip-jar-wp' );
			break;
		case 'past_due':
			$recurring_status_visible = __( 'Past Due', 'tip-jar-wp' );
			break;
		case 'cancelled':
			$recurring_status_visible = __( 'Cancelled', 'tip-jar-wp' );
			break;
		default:
			$recurring_status_visible = ucfirst( $arrangement->recurring_status );
			break;
	}

	return array(
		'id'                       => $arrangement->id,
		'date_created'             => $arrangement->date_created,
		'amount'                   => $arrangement->renewal_amount,
		'currency'                 => $arrangement->currency,
		'is_zero_decimal_currency' => tip_jar_wp_is_a_zero_decimal_currency( $arrangement->currency ),
		'string_after'             => ' ' . __( 'per', 'tip-jar-wp' ) . ' ' . $arrangement->interval_string,
		'recurring_status'         => $arrangement->recurring_status,
		'recurring_status_visible' => $recurring_status_visible,
		'renewal_date'             => $maybe_renewal_date,
		'webhook_succeeded'        => $webhook_succeeded,
		'pending_invoice'          => $pending_invoice,
	);

}

/**
 * Create/Assemble all of the values used to generate the default tip form, passed to the react component (Tip_Jar_WP_Form)
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */
function tip_jar_wp_tip_form_vars() {

	$saved_settings = get_option( 'tip_jar_wp_settings' );

	$featured_image = tip_jar_wp_aq_resize( tip_jar_wp_get_saved_setting( $saved_settings, 'tip_form_image' ), 100, 100 );

	$currency_code = tip_jar_wp_get_saved_setting( $saved_settings, 'default_currency', 'usd' );

	$tip_jar_wp_vars = array(
		'id'                            => null, // This is the ID of the form in the Tip_Jar_WP_Forms table. It is populated by the create_form endpoint whenever a form is created.
		'mode'                          => 'form',
		'open_style'                    => 'in_place',
		'currency_code'                 => strtoupper( $currency_code ),
		'currency_symbol'               => html_entity_decode( tip_jar_wp_currency_symbol( strtolower( $currency_code ) ) ),
		'currency_type'                 => tip_jar_wp_is_a_zero_decimal_currency( $currency_code ) ? 'zero_decimal' : 'decimal',
		'blank_flag_url'                => TIP_JAR_WP_PLUGIN_URL . '/assets/images/flags/blank.gif',
		'flag_sprite_url'               => TIP_JAR_WP_PLUGIN_URL . '/assets/images/flags/flags.png',
		'default_amount'                => tip_jar_wp_get_saved_setting( $saved_settings, 'default_amount', 500 ),
		'top_media_type'                => $featured_image ? 'featured_image' : 'none',
		'featured_image_url'            => $featured_image,
		'featured_embed'                => '',
		'header_media'                  => null,
		'file_download_attachment_data' => null,
		'recurring_options_enabled'     => true,
		'recurring_options'             => array(
			'never'   => array(
				'selected'     => true,
				'after_output' => __( 'One time only', 'tip-jar-wp' ),
			),
			'weekly'  => array(
				'selected'     => false,
				'after_output' => __( 'Every week', 'tip-jar-wp' ),
			),
			'monthly' => array(
				'selected'     => false,
				'after_output' => __( 'Every month', 'tip-jar-wp' ),
			),
			'yearly'  => array(
				'selected'     => false,
				'after_output' => __( 'Every year', 'tip-jar-wp' ),
			),
		),
		'strings'                       => array(
			'current_user_email'                 => '',
			'current_user_name'                  => '',
			'link_text'                          => __( 'Leave a tip', 'tip-jar-wp' ),
			'complete_payment_button_error_text' => __( 'Check info and try again', 'tip-jar-wp' ),
			'payment_verb'                       => tip_jar_wp_get_saved_setting( $saved_settings, 'payment_verb', __( 'Pay', 'tip-jar-wp' ) ),
			'payment_request_label'              => get_bloginfo( 'name' ),
			'form_has_an_error'                  => __( 'Please check and fix the errors above', 'tip-jar-wp' ),
			'general_server_error'               => __( "Something isn't working right at the moment. Please try again.", 'tip-jar-wp' ),
			'form_title'                         => tip_jar_wp_get_saved_setting( $saved_settings, 'tip_form_title', get_bloginfo( 'name' ) ),
			'form_subtitle'                      => tip_jar_wp_get_saved_setting( $saved_settings, 'tip_form_subtitle' ),
			'currency_search_text'               => __( 'Country or Currency here', 'tip-jar-wp' ),
			'other_payment_option'               => __( 'Other payment option', 'tip-jar-wp' ),
			'manage_payments_button_text'        => __( 'Manage your payments', 'tip-jar-wp' ),
			'thank_you_message'                  => tip_jar_wp_get_saved_setting( $saved_settings, 'tip_form_thank_you_message', __( 'Thank you for being a supporter!', 'tip-jar-wp' ) ),
			'payment_confirmation_title'         => get_bloginfo( 'name' ),
			'receipt_title'                      => __( 'Your Receipt', 'tip-jar-wp' ),
			'print_receipt'                      => __( 'Print Receipt', 'tip-jar-wp' ),
			'email_receipt'                      => __( 'Email Receipt', 'tip-jar-wp' ),
			'email_receipt_sending'              => __( 'Sending receipt...', 'tip-jar-wp' ),
			'email_receipt_success'              => __( 'Email receipt successfully sent', 'tip-jar-wp' ),
			'email_receipt_failed'               => __( 'Email receipt failed to send. Please try again.', 'tip-jar-wp' ),
			'receipt_payee'                      => __( 'Paid to', 'tip-jar-wp' ),
			'receipt_statement_descriptor'       => __( 'This will show up on your statement as', 'tip-jar-wp' ),
			'receipt_date'                       => __( 'Date', 'tip-jar-wp' ),
			'receipt_transaction_id'             => __( 'Transaction ID', 'tip-jar-wp' ),
			'receipt_transaction_amount'         => __( 'Amount', 'tip-jar-wp' ),
			'refund_payer'                       => __( 'Refund from', 'tip-jar-wp' ),
			'login'                              => __( 'Log in to manage your payments', 'tip-jar-wp' ),
			'manage_payments'                    => __( 'Manage Payments', 'tip-jar-wp' ),
			'transactions_title'                 => __( 'Your Transactions', 'tip-jar-wp' ),
			'transaction_title'                  => __( 'Transaction Receipt', 'tip-jar-wp' ),
			'transaction_period'                 => __( 'Plan Period', 'tip-jar-wp' ),
			'arrangements_title'                 => __( 'Your Plans', 'tip-jar-wp' ),
			'arrangement_title'                  => __( 'Manage Plan', 'tip-jar-wp' ),
			'arrangement_details'                => __( 'Plan Details', 'tip-jar-wp' ),
			'arrangement_id_title'               => __( 'Plan ID', 'tip-jar-wp' ),
			'arrangement_payment_method_title'   => __( 'Payment Method', 'tip-jar-wp' ),
			'arrangement_amount_title'           => __( 'Plan Amount', 'tip-jar-wp' ),
			'arrangement_renewal_title'          => __( 'Next renewal date', 'tip-jar-wp' ),
			'arrangement_action_cancel'          => __( 'Cancel Plan', 'tip-jar-wp' ),
			'arrangement_action_cant_cancel'     => __( 'Cancelling is currently not available.', 'tip-jar-wp' ),
			'arrangement_action_cancel_double'   => __( 'Are you sure you\'d like to cancel?', 'tip-jar-wp' ),
			'arrangement_cancelling'             => __( 'Cancelling Plan...', 'tip-jar-wp' ),
			'arrangement_cancelled'              => __( 'Plan Cancelled', 'tip-jar-wp' ),
			'arrangement_failed_to_cancel'       => __( 'Failed to cancel plan', 'tip-jar-wp' ),
			'back_to_plans'                      => __( '← Back to Plans', 'tip-jar-wp' ),
			'update_payment_method_verb'         => __( 'Update', 'tip-jar-wp' ),
			'sca_auth_description'               => __( 'Your have a pending renewal payment which requires authorization.', 'tip-jar-wp' ),
			'sca_auth_verb'                      => __( 'Authorize renewal payment', 'tip-jar-wp' ),
			'sca_authing_verb'                   => __( 'Authorizing payment', 'tip-jar-wp' ),
			'sca_authed_verb'                    => __( 'Payment successfully authorized!', 'tip-jar-wp' ),
			'sca_auth_failed'                    => __( 'Unable to authorize! Please try again.', 'tip-jar-wp' ),
			'login_button_text'                  => __( 'Log in', 'tip-jar-wp' ),
			'login_form_has_an_error'            => __( 'Please check and fix the errors above', 'tip-jar-wp' ),
			'uppercase_search'                   => __( 'Search', 'tip-jar-wp' ),
			'lowercase_search'                   => __( 'search', 'tip-jar-wp' ),
			'uppercase_page'                     => __( 'Page', 'tip-jar-wp' ),
			'lowercase_page'                     => __( 'page', 'tip-jar-wp' ),
			'uppercase_items'                    => __( 'Items', 'tip-jar-wp' ),
			'lowercase_items'                    => __( 'items', 'tip-jar-wp' ),
			'uppercase_per'                      => __( 'Per', 'tip-jar-wp' ),
			'lowercase_per'                      => __( 'per', 'tip-jar-wp' ),
			'uppercase_of'                       => __( 'Of', 'tip-jar-wp' ),
			'lowercase_of'                       => __( 'of', 'tip-jar-wp' ),
			'back'                               => __( 'Back to plans', 'tip-jar-wp' ),
			'zip_code_placeholder'               => __( 'Zip/Postal Code', 'tip-jar-wp' ),
			'download_file_button_text'          => __( 'Download File', 'tip-jar-wp' ),
			'input_field_instructions'           => array(
				'tip_amount'           => array(
					'placeholder_text' => tip_jar_wp_get_saved_setting( $saved_settings, 'amount_title', __( 'How much would you like to tip?', 'tip-jar-wp' ) ),
					'initial'          => array(
						'instruction_type'    => 'normal',
						'instruction_message' => tip_jar_wp_get_saved_setting( $saved_settings, 'amount_title', __( 'How much would you like to tip? Choose any currency.', 'tip-jar-wp' ) ),
					),
					'empty'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => tip_jar_wp_get_saved_setting( $saved_settings, 'amount_title', __( 'How much would you like to tip? Choose any currency.', 'tip-jar-wp' ) ),
					),
					'invalid_curency'  => array(
						'instruction_type'    => 'error',
						'instruction_message' => tip_jar_wp_get_saved_setting( $saved_settings, 'amount_title', __( 'Please choose a valid currency.', 'tip-jar-wp' ) ),
					),
				),
				'recurring'            => array(
					'placeholder_text' => __( 'Recurring', 'tip-jar-wp' ),
					'initial'          => array(
						'instruction_type'    => 'normal',
						'instruction_message' => tip_jar_wp_get_saved_setting( $saved_settings, 'recurring_title', __( 'How often would you like to give this?', 'tip-jar-wp' ) ),
					),
					'success'          => array(
						'instruction_type'    => 'success',
						'instruction_message' => tip_jar_wp_get_saved_setting( $saved_settings, 'recurring_title', __( 'How often would you like to give this?', 'tip-jar-wp' ) ),
					),
					'empty'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => tip_jar_wp_get_saved_setting( $saved_settings, 'recurring_title', __( 'How often would you like to give this?', 'tip-jar-wp' ) ),
					),
				),
				'name'                 => array(
					'placeholder_text' => __( 'Name on Credit Card', 'tip-jar-wp' ),
					'initial'          => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Enter the name on your card.', 'tip-jar-wp' ),
					),
					'success'          => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'Enter the name on your card.', 'tip-jar-wp' ),
					),
					'empty'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Please enter the name on your card.', 'tip-jar-wp' ),
					),
				),
				'privacy_policy'       => array(
					'terms_title'     => __( 'Terms and conditions', 'tip-jar-wp' ),
					'terms_body'      => tip_jar_wp_get_saved_setting( $saved_settings, 'tip_form_terms' ),
					'terms_show_text' => __( 'View Terms', 'tip-jar-wp' ),
					'terms_hide_text' => __( 'Hide Terms', 'tip-jar-wp' ),

					'initial'         => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'I agree to the terms.', 'tip-jar-wp' ),
					),
					'unchecked'       => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Please agree to the terms.', 'tip-jar-wp' ),
					),
					'checked'         => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'I agree to the terms.', 'tip-jar-wp' ),
					),
				),
				'email'                => array(
					'placeholder_text'     => __( 'Your email address', 'tip-jar-wp' ),
					'initial'              => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Enter your email address', 'tip-jar-wp' ),
					),
					'success'              => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'Enter your email address', 'tip-jar-wp' ),
					),
					'blank'                => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Enter your email address', 'tip-jar-wp' ),
					),
					'not_an_email_address' => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Make sure you have entered a valid email address', 'tip-jar-wp' ),
					),
				),
				'note_with_tip'        => array(
					'placeholder_text'  => __( 'Your note here...', 'tip-jar-wp' ),
					'initial'           => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Attach a note to your tip (optional)', 'tip-jar-wp' ),
					),
					'empty'             => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Attach a note to your tip (optional)', 'tip-jar-wp' ),
					),
					'not_empty_initial' => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Attach a note to your tip (optional)', 'tip-jar-wp' ),
					),
					'saving'            => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Saving note...', 'tip-jar-wp' ),
					),
					'success'           => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'Note successfully saved!', 'tip-jar-wp' ),
					),
					'error'             => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Unable to save note note at this time. Please try again.', 'tip-jar-wp' ),
					),
				),
				'email_for_login_code' => array(
					'placeholder_text' => __( 'Your email address', 'tip-jar-wp' ),
					'initial'          => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Enter your email to log in.', 'tip-jar-wp' ),
					),
					'success'          => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'Enter your email to log in.', 'tip-jar-wp' ),
					),
					'blank'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Enter your email to log in.', 'tip-jar-wp' ),
					),
					'empty'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Enter your email to log in.', 'tip-jar-wp' ),
					),
				),
				'login_code'           => array(
					'initial' => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Check your email and enter the login code.', 'tip-jar-wp' ),
					),
					'success' => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'Check your email and enter the login code.', 'tip-jar-wp' ),
					),
					'blank'   => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Check your email and enter the login code.', 'tip-jar-wp' ),
					),
					'empty'   => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Check your email and enter the login code.', 'tip-jar-wp' ),
					),
				),
				'stripe_all_in_one'    => array(
					'initial'                  => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Enter your credit card details here.', 'tip-jar-wp' ),
					),
					'empty'                    => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Enter your credit card details here.', 'tip-jar-wp' ),
					),
					'success'                  => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Enter your credit card details here.', 'tip-jar-wp' ),
					),
					'invalid_number'           => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card number is not a valid credit card number.', 'tip-jar-wp' ),
					),
					'invalid_expiry_month'     => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s expiration month is invalid.', 'tip-jar-wp' ),
					),
					'invalid_expiry_year'      => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s expiration year is invalid.', 'tip-jar-wp' ),
					),
					'invalid_cvc'              => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s security code is invalid.', 'tip-jar-wp' ),
					),
					'incorrect_number'         => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card number is incorrect.', 'tip-jar-wp' ),
					),
					'incomplete_number'        => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card number is incomplete.', 'tip-jar-wp' ),
					),
					'incomplete_cvc'           => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s security code is incomplete.', 'tip-jar-wp' ),
					),
					'incomplete_expiry'        => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s expiration date is incomplete.', 'tip-jar-wp' ),
					),
					'incomplete_zip'           => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s zip code is incomplete.', 'tip-jar-wp' ),
					),
					'expired_card'             => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card has expired.', 'tip-jar-wp' ),
					),
					'incorrect_cvc'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s security code is incorrect.', 'tip-jar-wp' ),
					),
					'incorrect_zip'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s zip code failed validation.', 'tip-jar-wp' ),
					),
					'invalid_expiry_year_past' => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s expiration year is in the past', 'tip-jar-wp' ),
					),
					'card_declined'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card was declined.', 'tip-jar-wp' ),
					),
					'missing'                  => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'There is no card on a customer that is being charged.', 'tip-jar-wp' ),
					),
					'processing_error'         => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'An error occurred while processing the card.', 'tip-jar-wp' ),
					),
					'invalid_request_error'    => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Unable to process this payment, please try again or use alternative method.', 'tip-jar-wp' ),
					),
					'invalid_sofort_country'   => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The billing country is not accepted by SOFORT. Please try another country.', 'tip-jar-wp' ),
					),
				),
			),
		),
	);

	return $tip_jar_wp_vars;
}

/**
 * Create/Assemble all of the dynamic values used to generate the default tip form, passed to the react component (Tip_Jar_WP_Form)
 * Dynamic values are different from saved values, because they need to be generated on the fly. For example, whether the user is logged in or not.
 *
 * @since 1.0
 * @return array $dynamic_tip_jar_wp_vars A list of the dynamic variables and their current values.
 */
function tip_jar_wp_dynamic_tip_form_vars() {

	$saved_settings = get_option( 'tip_jar_wp_settings' );

	$featured_image = tip_jar_wp_aq_resize( tip_jar_wp_get_saved_setting( $saved_settings, 'tip_form_image' ), 100, 100 );

	// Get the default state from the URL variables.
	// If this is a bookmarked URL. Nonce is not checked here because this is not a form submission, but a URL.
	foreach ( $_GET as $url_variable => $url_variable_value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Skip any URL vars that aren't relevant to tjwp. Skip the modal vars too.
		if ( false === strpos( $url_variable, 'tjwp' ) || true === strpos( $url_variable, 'tjwpmodal' ) ) {
			continue;
		}

		// These values came from the $_GET array, meaning they need to be sanitized.
		$visual_state_key                        = sanitize_text_field( wp_unslash( $url_variable ) );
		$visual_state_value                      = sanitize_text_field( wp_unslash( $url_variable_value ) );
		$tjwp_url_variables[ $visual_state_key ] = $visual_state_value;

	}

	// Level 1 - Eventually we'll make this more robust, but for now 3 levels is as deep as has been needed.
	if ( isset( $tjwp_url_variables['tjwp1'] ) ) {
		$all_current_visual_states                                 = array();
		$all_current_visual_states[ $tjwp_url_variables['tjwp1'] ] = array();
		// Level 2.
		if ( isset( $tjwp_url_variables['tjwp2'] ) ) {
			$all_current_visual_states[ $tjwp_url_variables['tjwp1'] ][ $tjwp_url_variables['tjwp2'] ] = array();
			// Level 3.
			if ( isset( $tjwp_url_variables['tjwp3'] ) ) {
				$all_current_visual_states[ $tjwp_url_variables['tjwp1'] ][ $tjwp_url_variables['tjwp2'] ][ $tjwp_url_variables['tjwp3'] ] = array();
			}
		}
	} else {
		$all_current_visual_states = 'inherit';
	}

	// Now we will handle the modal vars.
	if ( isset( $_GET['tjwpmodal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tjwp_modal_value = sanitize_text_field( wp_unslash( $_GET['tjwpmodal'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Add that modal value to the modal visual state, which will be passed to our react app.
		$modal_visual_state[ $tjwp_modal_value ] = array();
	} else {
		$modal_visual_state = false;
	}

	$currency_code = tip_jar_wp_get_saved_setting( $saved_settings, 'default_currency', 'usd' );

	$user = wp_get_current_user();

	// Set the user's card name if they are logged in.
	if (
		isset( $user->first_name ) &&
		! empty( $user->first_name ) &&
		isset( $user->last_name ) &&
		! empty( $user->last_name )
	) {
		$user_card_name = $user->first_name . ' ' . $user->last_name;
	} else {
		$user_card_name = '';
	}

	$permalink = false;

	// Attempt to get the current URL from the the $_SERVER variable.
	if (
		isset( $_SERVER['SERVER_NAME'] ) &&
		isset( $_SERVER['REQUEST_URI'] )
	) {

		$everything_after_the_main_url = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$values_before_question_mark   = strtok( $everything_after_the_main_url, '?' );

		$non_tjwp_url_variables = array();

		// Loop through each query string value in the URL, and we'll remove any tipjarwp related variables.
		foreach ( $_GET as $url_variable_name => $url_variable_value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// If this value is not a tipjarwp value, add it to the query string again.
			if ( false === strpos( $url_variable_name, 'tjwp' ) ) {
				$sanitized_key                            = sanitize_text_field( wp_unslash( $url_variable_name ) );
				$sanitized_value                          = sanitize_text_field( wp_unslash( $url_variable_value ) );
				$non_tjwp_url_variables[ $sanitized_key ] = $sanitized_value;
			}
		}

		$permalink = add_query_arg( $non_tjwp_url_variables, 'https://' . sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) . $values_before_question_mark );
	}

	// If permalink is still empty, default to the base URL of the site.
	if ( empty( $permalink ) || ! $permalink ) {
		$permalink = get_bloginfo( 'url' );
	}

	$dynamic_tip_jar_wp_vars = array(
		'date_format'                 => get_option( 'date_format' ),
		'time_format'                 => get_option( 'time_format' ),
		'wordpress_permalink_only'    => $permalink,
		'all_default_visual_states'   => $all_current_visual_states,
		'modal_visual_state'          => $modal_visual_state,
		'user_is_logged_in'           => is_user_logged_in(),
		'stripe_api_key'              => tip_jar_wp_get_stripe_publishable_key(),
		'stripe_account_country_code' => tip_jar_wp_stripe_get_account_country_code(),
		'setup_link'                  => admin_url( 'admin.php?page=tip-jar-wp&mpwpadmin1=welcome&mpwpadmin_lightbox=do_wizard_health_check' ),
		'close_button_url'            => TIP_JAR_WP_PLUGIN_URL . '/assets/images/closebtn.png',
	);

	return $dynamic_tip_jar_wp_vars;
}

/**
 * Create/Assemble an array of all of the strings used for Editing in the Gutenberg Block.
 *
 * @since 1.0
 * @return array $editing_strings All of the editing strings used when the Tip Form is in editing mode.
 */
function tip_jar_wp_editing_strings() {

	$editing_strings = array(
		'edit'                             => __( 'Edit', 'tip-jar-wp' ),
		'view'                             => __( 'View', 'tip-jar-wp' ),
		'remove'                           => __( 'Remove', 'tip-jar-wp' ),
		'choose_image'                     => __( 'Choose image', 'tip-jar-wp' ),
		'select_an_item_for_upload'        => __( 'Select an item', 'tip-jar-wp' ),
		'use_uploaded_item'                => __( 'Use item', 'tip-jar-wp' ),
		'choose_file_to_be_delivered'      => __( 'Choose the file to be delievered to the user', 'tip-jar-wp' ),
		'enable_file_download_mode'        => __( 'Enable File Download Mode', 'tip-jar-wp' ),
		'disable_file_download_mode'       => __( 'Disable File Download Mode', 'tip-jar-wp' ),
		'deliverable_file_title'           => __( 'Deliverable File', 'tip-jar-wp' ),
		'deliverable_file_description'     => __( 'This file will be given to the user after they pay (or enter $0):', 'tip-jar-wp' ),
		'require_users_email_title'        => __( 'Require User\'s Email?', 'tip-jar-wp' ),
		'require_users_email_description'  => __( 'Do you want to require the user to enter their email to get this file?', 'tip-jar-wp' ),
		'email_required'                   => __( 'Email required', 'tip-jar-wp' ),
		'email_not_required'               => __( 'Email not required', 'tip-jar-wp' ),
		'instructions_to_user_title'       => __( 'Instructions to user', 'tip-jar-wp' ),
		'instructions_to_user_description' => __( 'This is what the user will see above the download button. Use it to give them instructions, or just say "thanks" for downloading.', 'tip-jar-wp' ),
		'instructions_title'               => __( 'Instructions Title', 'tip-jar-wp' ),
		'instructions_description'         => __( 'Instructions Description', 'tip-jar-wp' ),
		'file_download_mode_description'   => __( 'File Download Mode allows you to give the user a file after they pay. Leave this disabled for a normal tip form.', 'tip-jar-wp' ),
		'tip_forms_display_style'          => __( 'Tip Form\'s Display Style', 'tip-jar-wp' ),
		'how_should_the_tip_form_display'  => __( 'How should the Tip Form display?', 'tip-jar-wp' ),
		'embed_in_place'                   => __( 'Embed in-place', 'tip-jar-wp' ),
		'start_as_a_button'                => __( 'Start as a button', 'tip-jar-wp' ),
		'start_as_a_text_link'             => __( 'Start as a text link', 'tip-jar-wp' ),
		'with_the_text'                    => __( 'with the text', 'tip-jar-wp' ),
		'which'                            => __( 'which', 'tip-jar-wp' ),
		'opens_in_place'                   => __( 'opens in-place', 'tip-jar-wp' ),
		'opens_in_modal'                   => __( 'opens in modal (pop-up)', 'tip-jar-wp' ),
		'when_clicked'                     => __( 'when clicked.', 'tip-jar-wp' ),
		'enable_recurring_options'         => __( 'Enable recurring options? (Currently disabled)', 'tip-jar-wp' ),
		'disable_recurring_options'        => __( 'Disable recurring options', 'tip-jar-wp' ),
		'agreement_text'                   => __( 'Agreement text', 'tip-jar-wp' ),
		'view_terms_button_text'           => __( '"View Terms" button text', 'tip-jar-wp' ),
		'terms_and_conditions_title'       => __( 'Terms and Conditions Title', 'tip-jar-wp' ),
		'terms_and_conditions_body'        => __( 'Terms and Conditions Body (leave this blank to hide on front-end)', 'tip-jar-wp' ),
		'optional_subtitle_here'           => __( 'Optional subtitle here.', 'tip-jar-wp' ),
		'optional_title_here'              => __( 'Optional title here.', 'tip-jar-wp' ),
		'optional_header_media_here'       => __( '"Optional place to display audio or video."', 'tip-jar-wp' ),
		'insert_shortcode_area_title'      => __( 'When you\'re ready, insert the shortcode.', 'tip-jar-wp' ),
		'insert_shortcode_area_title'      => __( 'When you\'re ready, insert the shortcode.', 'tip-jar-wp' ),
		'insert_shortcode'                 => __( 'Insert Shortcode', 'tip-jar-wp' ),
		'update_shortcode'                 => __( 'Update Shortcode', 'tip-jar-wp' ),
		'update_shortcode'                 => __( 'Update Shortcode', 'tip-jar-wp' ),
		'cancel_shortcode'                 => __( 'Cancel', 'tip-jar-wp' ),
		'media_above_payment_form'         => __( 'Set the media to show above payment form', 'tip-jar-wp' ),
		'description_top_media_type'       => __( 'What would you like to show above the payment form?', 'tip-jar-wp' ),
	);

	return $editing_strings;
}

/**
 * Re-usable function to localize the editing strings.
 * This is used by both the Gutenberg Block, and the Shortcode Editor.
 *
 * @since 1.0
 * @return void
 */
function tip_jar_wp_localize_editing_strings() {
	?>
	<script type="text/javascript">
		var tip_jar_wp_editing_strings = <?php echo wp_kses_post( wp_json_encode( tip_jar_wp_editing_strings() ) ); ?>;
	</script>
	<?php
}
