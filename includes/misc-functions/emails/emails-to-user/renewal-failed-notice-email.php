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
 * Send a "Renewal Failed" email to the user when their subscription has a failed invoice.
 *
 * @access      public
 * @since       1.0.0.
 * @param       object $arrangement The arrangement attempting to be renewed.
 * @return      bool
 */
function tip_jar_wp_send_renewal_failed_email( $arrangement ) {

	if ( ! $arrangement ) {
		return false;
	}

	// Get the user object.
	$user = get_user_by( 'id', $arrangement->user_id );

	$email_from = get_bloginfo( 'admin_email' );
	$email_to   = $user->user_email;
	// translators: The name of the site. The arrangement ID.
	$email_subject = sprintf( __( 'Your plan could not be paid %1$s. Plan ID: %2$s', 'tip-jar-wp' ), get_bloginfo( 'name' ), $arrangement->id );

	$email_message = tip_jar_wp_get_html_renewal_failed( $arrangement );

	$email_headers = array(
		'Content-Type: text/html; charset=UTF-8',
		// 'From: ' . get_bloginfo( 'name' ) . ' <' . $email_from . '>',
	);

	// Send the email to the purchaser.
	$email_sent = wp_mail( $email_to, $email_subject, $email_message, $email_headers );

	return $email_sent;
}

/**
 * Get the standalone HTML for a "renewal failed" email notification.
 *
 * @param       object $arrangement The arrangement being cancelled.
 * @return      bool
 */
function tip_jar_wp_get_html_renewal_failed( $arrangement ) {

	if ( ! $arrangement->id ) {
		return false;
	}

	$action_text = __( 'View plan details', 'tip-jar-wp' );

	switch ( $arrangement->status_reason ) {

		// It could not be renewed because there was a card error.
		case 'card_declined':
			// translators: The id of the plan (arrangement) being cancelled.
			$cancellation_string = sprintf( __( 'Your plan (id: %s) could not be paid due to your card being declined. If needed, you can log in below to update your card.', 'tip-jar-wp' ), $arrangement->id );
			$action_text         = __( 'Update Card Details', 'tip-jar-wp' );
			break;

		// It could not be renewed because there was a card error.
		case 'card_error':
			// translators: The id of the plan (arrangement) being cancelled.
			$cancellation_string = sprintf( __( 'Your plan (id: %s) could not be paid due to a card error. If needed, you can log in below to update your card.', 'tip-jar-wp' ), $arrangement->id );
			$action_text         = __( 'Update Card Details', 'tip-jar-wp' );
			break;

		// Another payment failure took place.
		case 'payment_failure':
			// translators: The id of the plan (arrangement) being cancelled.
			$cancellation_string = sprintf( __( 'Your plan (id: %s) could not be paid. If needed, you can log in below to update your payment method.', 'tip-jar-wp' ), $arrangement->id );
			$action_text         = __( 'Update Payment Method', 'tip-jar-wp' );
			break;

		case 'unknown':
			// translators: The id of the plan (arrangement) being cancelled.
			$cancellation_string = sprintf( __( 'Your plan (id: %s) could not be paid due to an error. If needed, you can log in below to update your payment method.', 'tip-jar-wp' ), $arrangement->id );
			$action_text         = __( 'Update Payment Method', 'tip-jar-wp' );
			break;

		// It could not be renewed because a PaymentIntent needed to be confirmed.
		case 'authentication_required':
			// translators: The id of the plan (arrangement) being cancelled.
			$cancellation_string = sprintf( __( 'Your payment needs to be confirmed before your plan (id: %s) can be renewed. Click the link below to log in and confirm.', 'tip-jar-wp' ), $arrangement->id );
			$action_text         = __( 'Log in', 'tip-jar-wp' );
			break;

		default:
			// translators: The id of the plan (arrangement) being cancelled.
			$cancellation_string = sprintf( __( 'Your plan (id: %s) could not be paid due to an error. If needed, you can log in below to update your payment method.', 'tip-jar-wp' ), $arrangement->id );
			$action_text         = __( 'Update Payment Method', 'tip-jar-wp' );
			break;
	}

	$user                = get_user_by( 'id', $arrangement->user_id );
	$initial_transaction = new Tip_Jar_WP_Transaction( $arrangement->initial_transaction_id );

	$saved_settings = get_option( 'tip_jar_wp_settings' );
	$image          = tip_jar_wp_get_saved_setting( $saved_settings, 'tip_form_image' );

	ob_get_clean();
	ob_start();

	?>
	<div class="tip-jar-wp-receipt" style="margin: 30px 0px 0px 0px;">
		<div class="tip-jar-wp-receipt-title" style="
			font-size: 17px;
			line-height: 18px;
			font-weight: 700;
			color: #000;
			text-shadow: 0 1px 0 #fff;
			margin-bottom:10px;"
		><?php echo esc_textarea( __( 'Your plan could not be renewed!', 'tip-jar-wp' ) ); ?></div>
		<div class="tip-jar-wp-receipt-email" style="
			margin-bottom: 15px;"
		><?php echo esc_textarea( $cancellation_string ); ?></div>
		<div>
			<p>
				<a href="
				<?php
				echo esc_url(
					add_query_arg(
						array(
							'tjwp1'     => 'manage_payments',
							'tjwp2'     => 'arrangement',
							'tjwp3'     => $arrangement->id,
							'tjwpmodal' => '1',
						),
						$initial_transaction->page_url
					)
				);
				?>
					">
					<?php echo esc_textarea( $action_text ); ?>
				</a>
			</p>
		</div>
	</div>
	<?php
	$body = ob_get_clean();
	return tip_jar_wp_get_html_email( $body );
}
