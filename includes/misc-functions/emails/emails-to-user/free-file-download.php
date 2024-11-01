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
 * Send the email receipt for a free file download.
 *
 * @access      public
 * @since       1.0.0.
 * @param       object $transaction The transaction for which this receipt is being emailed.
 * @return      bool
 */
function tip_jar_wp_send_free_file_download_email( $email, $file_download_url, $instructions_title, $instructions_description ) {

	$email = sanitize_email( $email );

	if ( ! $email ) {
		return false;
	}

	// Get the user object.
	$user = get_user_by( 'id', $transaction->user_id );

	$email_from = get_bloginfo( 'admin_email' );
	$email_to   = $email;
	// translators: The name of the site. The transaction ID.
	$email_subject = sprintf( __( 'Your file download from %s', 'tip-jar-wp' ), get_bloginfo( 'name' ), $transaction->id );

	$email_message = tip_jar_wp_get_html_free_file_download( $file_download_url, $instructions_title, $instructions_description );

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
 * @param       object $transaction $transaction The transaction for which this receipt is being emailed.
 * @return      bool
 */
function tip_jar_wp_get_html_free_file_download( $file_download_url, $instructions_title, $instructions_description ) {

	if ( ! $file_download_url ) {
		return false;
	}

	$saved_settings = get_option( 'tip_jar_wp_settings' );
	$image          = tip_jar_wp_get_saved_setting( $saved_settings, 'tip_form_image' );

	ob_get_clean();
	ob_start();

	?>
	<div class="tip-jar-wp-confirmation-message" style="font-size: 17px; line-height: 18px; font-weight: 700; color: #000; text-shadow: 0 1px 0 #fff;">
	<?php
	echo esc_textarea( $instructions_title );
	?>
	</div>
	<div class="tip-jar-wp-confirmation-message">
	<?php
	echo esc_textarea( $instructions_description );
	?>
	</div>
	<div class="tip-jar-wp-confirmation-message">
	<?php
	echo esc_textarea( $file_download_url );
	?>
	</div>

	<?php
	$body = ob_get_clean();
	return tip_jar_wp_get_html_email( $body );
}
