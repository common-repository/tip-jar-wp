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
 * Send the email receipt for a transaction.
 *
 * @access      public
 * @since       1.0.0.
 * @param       object $transaction The transaction for which a receipt is being emailed.
 * @return      bool
 */
function tip_jar_wp_send_receipt_email_to_admin( $transaction ) {

	if ( ! $transaction ) {
		return false;
	}

	// Get the user object.
	$user = get_user_by( 'id', $transaction->user_id );

	$email_from = get_bloginfo( 'admin_email' );
	$email_to   = get_bloginfo( 'admin_email' );
	// translators: The name of the site. The transaction ID.
	$email_subject = sprintf( __( 'New transaction on %1$s. Transaction ID: %2$s', 'tip-jar-wp' ), get_bloginfo( 'name' ), $transaction->id );
	$email_message = tip_jar_wp_get_html_receipt_for_admin( $transaction );

	$email_headers = array(
		'Content-Type: text/html; charset=UTF-8',
		// 'From: ' . get_bloginfo( 'name' ) . ' <' . $email_from . '>',
	);

	// Send an email receipt to the purchaser.
	$email_sent = wp_mail( $email_to, $email_subject, $email_message, $email_headers );

	return $email_sent;
}

/**
 * Get the standalone HTML for a receipt. Useful for emails.
 *
 * @access      public
 * @since       1.0.0.
 * @param       object $transaction The transaction for which a receipt is being emailed.
 * @return      bool
 */
function tip_jar_wp_get_html_receipt_for_admin( $transaction = null ) {

	if ( ! $transaction->id ) {
		return false;
	}

	// Fetch the trnasaction object fresh.
	$transaction = new Tip_Jar_WP_Transaction( $transaction->id );
	$arrangement = new Tip_Jar_WP_Arrangement( $transaction->arrangement_id );

	$user           = get_user_by( 'id', $transaction->user_id );
	$saved_settings = get_option( 'tip_jar_wp_settings' );
	$image          = tip_jar_wp_get_saved_setting( $saved_settings, 'tip_form_image' );

	// If this is a recurring plan...
	if ( ! empty( $arrangement->renewal_amount ) && ! empty( $arrangement->interval_string ) ) {
		$visible_plan_amount = tip_jar_wp_get_visible_amount( $arrangement->renewal_amount, $arrangement->currency );
		$plan_amount         = __( 'This transaction is part of an automatically recurring plan:', 'tip-jar-wp' ) . ' ' . $visible_plan_amount . ' ' . __( 'per', 'tip-jar-wp' ) . ' ' . $arrangement->interval_string;
	} else {
		$plan_amount = __( 'This transaction is a single, one-time transaction, not part of an automatically recurring plan.', 'tip-jar-wp' );
	}

	ob_get_clean();
	ob_start();

	?>
	<div class="tip-jar-wp-receipt" style="margin: 30px 0px 0px 0px;">
		<div class="tip-jar-wp-receipt-title" style="
		font-size: 17px;
		line-height: 18px;
		font-weight: 700;
		color: #000;
		text-shadow: 0 1px 0 #fff;"
		><?php echo esc_textarea( __( 'New transaction details:', 'tip-jar-wp' ) ); ?></div>
		<div class="tip-jar-wp-receipt-email" style="
		margin-bottom: 15px;"
		><?php echo esc_textarea( $user->user_email ); ?></div>
		<div class="tip-jar-wp-receipt-payee">
			<span class="tip-jar-wp-receipt-line-item-title tip-jar-wp-receipt-payee-title" style="
			margin: 0;
			line-height: 18px;
			font-weight: 700;
			color: #000;
			text-shadow: 0 1px 0 #fff;"
			><?php echo esc_textarea( __( 'Paid to:', 'tip-jar-wp' ) ); ?> </span>
			<span class="tip-jar-wp-receipt-line-item-value tip-jar-wp-receipt-payee-value"><?php echo esc_textarea( get_bloginfo( 'Name' ) ); ?></span>
		</div>
		<div class="tip-jar-wp-receipt-transaction-id">
			<span class="tip-jar-wp-receipt-line-item-title tip-jar-wp-receipt-transaction-id-title" style="
			margin: 0;
			line-height: 18px;
			font-weight: 700;
			color: #000;
			text-shadow: 0 1px 0 #fff;"
			><?php echo esc_textarea( __( 'Transaction ID:', 'tip-jar-wp' ) ); ?> </span>
			<span class="tip-jar-wp-receipt-line-item-value tip-jar-wp-receipt-transaction-id-value"><?php echo esc_textarea( $transaction->id ); ?></span>
		</div>
		<div class="tip-jar-wp-receipt-transaction-date">
			<span class="tip-jar-wp-receipt-line-item-title tip-jar-wp-receipt-date-title" style="
			margin: 0;
			line-height: 18px;
			font-weight: 700;
			color: #000;
			text-shadow: 0 1px 0 #fff;"
			><?php echo esc_textarea( __( 'Date:', 'tip-jar-wp' ) ); ?> </span>
			<span class="tip-jar-wp-receipt-line-item-value tip-jar-wp-receipt-date-value"><?php echo esc_textarea( $transaction->date_created ); ?></span>
		</div>
		<div class="tip-jar-wp-receipt-amount">
			<span class="tip-jar-wp-receipt-line-item-title tip-jar-wp-receipt-transaction-amount-title" style="
			margin: 0;
			line-height: 18px;
			font-weight: 700;
			color: #000;
			text-shadow: 0 1px 0 #fff;"
			><?php echo esc_textarea( __( 'Amount:', 'tip-jar-wp' ) ); ?> </span>
			<span class="tip-jar-wp-receipt-line-item-value tip-jar-wp-receipt-transaction-amount-value">
			<?php
			// Show the transaction amount.
			echo esc_textarea( tip_jar_wp_get_visible_amount( $transaction->charged_amount, $transaction->charged_currency ) );
			echo ' ';
			echo esc_textarea( strtoupper( $transaction->charged_currency ) );
			?>
			</span>
		</div>
		<div class="tip-jar-wp-receipt-amount">
			<span class="tip-jar-wp-receipt-line-item-title tip-jar-wp-receipt-arrangement-amount-title" style="
			margin: 0;
			line-height: 18px;
			font-weight: 700;
			color: #000;
			text-shadow: 0 1px 0 #fff;"
			><?php echo esc_textarea( __( 'Plan:', 'tip-jar-wp' ) ); ?> </span>
			<span class="tip-jar-wp-receipt-line-item-value tip-jar-wp-receipt-arrangement-amount-value">
			<?php
			// Show the plan amount if a plan exists.
			if ( ! empty( $plan_amount ) ) {
				echo esc_textarea( $plan_amount );
			}
			?>
			</span>
		</div>
		<div class="tip-jar-wp-receipt-statement-descriptor">
			<span class="tip-jar-wp-receipt-line-item-title tip-jar-wp-receipt-transaction-statement-descriptor-title" style="
			margin: 0;
			line-height: 18px;
			font-weight: 700;
			color: #000;
			text-shadow: 0 1px 0 #fff;"
			><?php echo esc_textarea( __( 'This will show up on their statement as:', 'tip-jar-wp' ) ); ?> </span>
			<span class="tip-jar-wp-receipt-line-item-value tip-jar-wp-receipt-transaction-statement-descriptor">
			<?php
			echo esc_textarea( $transaction->statement_descriptor );
			?>
			</span>
		</div>
		<div>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=tip-jar-wp&mpwpadmin1=transactions&mpwpadmin2=single_data_view&mpwpadmin3=' . $transaction->id ) ); ?>">
					<?php echo esc_textarea( __( 'View transaction details', 'tip-jar-wp' ) ); ?>
				</a>
			</p>
			<p>
				<?php
				// Show the plan link, if a plan exists for this transaction.
				if (
					! empty( $plan_amount ) &&
					! empty( $arrangement->renewal_amount ) &&
					! empty( $arrangement->interval_string )
				) {
					?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=tip-jar-wp&mpwpadmin1=arrangements&mpwpadmin2=single_data_view&mpwpadmin3=' . $arrangement->id ) ); ?>">
						<?php echo esc_textarea( __( 'View plan details', 'tip-jar-wp' ) ); ?>
					</a>
					<?php
				}
				?>
			</p>
		</div>
	</div>
	<?php
	$body = ob_get_clean();
	return tip_jar_wp_get_html_email( $body );
}
