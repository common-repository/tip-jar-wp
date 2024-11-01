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
 * @param       object $transaction The transaction being refunded.
 * @return      bool
 */
function tip_jar_wp_send_refund_email( $transaction ) {

	if ( ! $transaction ) {
		return false;
	}

	// Get the user object.
	$user = get_user_by( 'id', $transaction->user_id );

	$email_from = get_bloginfo( 'admin_email' );
	$email_to   = $user->user_email;
	// translators: The name of the site. The transaction ID.
	$email_subject = sprintf( __( 'Your refund receipt from %1$s. Transaction ID: %2$s', 'tip-jar-wp' ), get_bloginfo( 'name' ), $transaction->id );

	$email_message = tip_jar_wp_get_html_refund( $transaction );

	$email_headers = array(
		'Content-Type: text/html; charset=UTF-8',
		// 'From: ' . get_bloginfo( 'name' ) . ' <' . $email_from . '>',
	);

	// Send an email receipt to the purchaser.
	$email_sent = wp_mail( $email_to, $email_subject, $email_message, $email_headers );

	return $email_sent;
}

/**
 * Get the standalone HTML for a refund receipt. Useful for emails.
 *
 * @access      public
 * @since       1.0.0.
 * @param       object $transaction The transaction being refunded.
 * @return      bool
 */
function tip_jar_wp_get_html_refund( $transaction = null ) {

	if ( ! $transaction->id ) {
		return false;
	}

	$user = get_user_by( 'id', $transaction->user_id );

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
		><?php echo esc_textarea( __( 'Your Refund Receipt', 'tip-jar-wp' ) ); ?></div>
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
			><?php echo esc_textarea( __( 'Refund from:', 'tip-jar-wp' ) ); ?> </span>
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
			echo esc_textarea( tip_jar_wp_get_visible_amount( $transaction->charged_amount, $transaction->charged_currency ) );
			echo ' ';
			echo esc_textarea( strtoupper( $transaction->charged_currency ) );
			?>
			</span>
		</div>
		<div>
			<p>
				<a href="
				<?php
				echo esc_url(
					add_query_arg(
						array(
							'tjwp1'     => 'manage_payments',
							'tjwp2'     => 'transaction',
							'tjwp3'     => $transaction->id,
							'tjwpmodal' => '1',
						),
						$transaction->page_url
					)
				);
				?>
					">
					<?php echo esc_textarea( __( 'View full transaction details', 'tip-jar-wp' ) ); ?>
				</a>
			</p>
		</div>
	</div>
	<?php
	$body = ob_get_clean();
	return tip_jar_wp_get_html_email( $body );
}
