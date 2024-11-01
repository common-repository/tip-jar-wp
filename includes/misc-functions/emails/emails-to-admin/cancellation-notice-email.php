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
 * Send the cancellation notice to the user when their subscription has been cancelled.
 *
 * @since  1.0.0.
 * @param object $arrangement The arrangement being cancelled.
 * @return bool
 */
function tip_jar_wp_send_cancellation_notice_email_to_admin( $arrangement ) {

	if ( ! $arrangement ) {
		return false;
	}

	// Get the user object.
	$user = get_user_by( 'id', $arrangement->user_id );

	$email_from = get_bloginfo( 'admin_email' );
	$email_to   = get_bloginfo( 'admin_email' );
	// translators: The name of the site. The arrangement ID.
	$email_subject = sprintf( __( 'A plan has been cancelled on %1$s. Plan ID: %2$s', 'tip-jar-wp' ), get_bloginfo( 'name' ), $arrangement->id );

	$email_message = tip_jar_wp_get_html_cancellation_for_admin( $arrangement );

	$email_headers = array(
		'Content-Type: text/html; charset=UTF-8',
		// 'From: ' . get_bloginfo( 'name' ) . ' <' . $email_from . '>',
	);

	// Send an email receipt to the purchaser.
	$email_sent = wp_mail( $email_to, $email_subject, $email_message, $email_headers );

	return $email_sent;
}

/**
 * Get the standalone HTML for a cancellation notice email
 *
 * @param object $arrangement The arrangement being cancelled.
 * @return bool
 */
function tip_jar_wp_get_html_cancellation_for_admin( $arrangement ) {

	if ( ! $arrangement->id ) {
		return false;
	}

	// translators: The id of the plan (arrangement) being cancelled.
	$cancellation_string = sprintf( __( 'Plan %s has been cancelled.', 'tip-jar-wp' ), $arrangement->id );

	$user = get_user_by( 'id', $arrangement->user_id );

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
		><?php echo esc_textarea( __( 'A plan has been cancelled.', 'tip-jar-wp' ) ); ?></div>
		<div class="tip-jar-wp-receipt-email" style="
			margin-bottom: 15px;"
		>
			<p><?php echo esc_textarea( $cancellation_string ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=tip-jar-wp&mpwpadmin1=arrangements&mpwpadmin2=single_data_view&mpwpadmin3=' . $arrangement->id ) ); ?>">
					<?php echo esc_textarea( __( 'View plan details', 'tip-jar-wp' ) ); ?>
				</a>
			</p>
		</div>
	</div>
	<?php
	$body = ob_get_clean();
	return tip_jar_wp_get_html_email( $body );
}
