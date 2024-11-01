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
 * Deliver an attachment through PHP. This function does no security/user/email checking.
 * It simply delivers the file. Make sure you do your checks prior to calling this function.
 *
 * @access   public
 * @since    1.0.0
 * @param    array $file_download_data - The unique data we want to store about this file download.
 * @return   void
 */
function tip_jar_wp_deliver_attached_file( $file_download_data ) {

	// Make sure the $file_download_data array has all the data we need.
	if (
		! isset( $file_download_data['user_id'] ) ||
		! isset( $file_download_data['form_id'] ) ||
		! isset( $file_download_data['transaction_id'] ) ||
		! isset( $file_download_data['attachment_id'] ) ||
		! isset( $file_download_data['page_url'] )
	) {
		wp_die( esc_textarea( __( 'Invalid file download values.', 'tip-jar-wp' ) ) );
	}

	$deliverable_file_path = get_attached_file( $file_download_data['attachment_id'] );

	// If no attachment file path was found...
	if ( ! $deliverable_file_path ) {
		wp_die( esc_textarea( __( 'Invalid file download.', 'tip-jar-wp' ) ) );
	}

	$download_log = new Tip_Jar_WP_Download_Log();
	$download_log->create(
		array(
			'user_id'        => absint( $file_download_data['user_id'] ),
			'form_id'        => absint( $file_download_data['form_id'] ),
			'transaction_id' => absint( $file_download_data['transaction_id'] ),
			'attachment_id'  => absint( $file_download_data['attachment_id'] ),
			'page_url'       => sanitize_text_field( $file_download_data['page_url'] ),
		)
	);

	nocache_headers();
	header( 'Robots: none' );
	header( 'Content-Type: ' . get_post_mime_type( $file_download_data['attachment_id'] ) );
	header( 'Content-Description: File Transfer' );
	header( 'Content-Length: ' . filesize( $deliverable_file_path ) );
	header( 'Content-Disposition: attachment; filename="' . basename( $deliverable_file_path ) . '"' );
	header( 'Content-Transfer-Encoding: binary' );

	// Stream the file.
	tip_jar_wp_readfile_chunked( $deliverable_file_path );
	exit;

}

/**
 * Read a file and display its content chunk by chunk.
 *
 * @access   public
 * @since    1.0.0
 * @param    string $filepath - The path to the file.
 * @param    bool   $retbytes - Whether to return the number of bytes or not.
 * @param    int    $chunk_size - The size of the chunks.
 * @return   array
 */
function tip_jar_wp_readfile_chunked( $filepath, $retbytes = true, $chunk_size = 1024 ) {
	$buffer = '';
	$cnt    = 0;
	$handle = fopen( $filepath, 'rb' );

	if ( false === $handle ) {
		return false;
	}

	while ( ! feof( $handle ) ) {
		$buffer = fread( $handle, $chunk_size );
		echo $buffer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		ob_flush();
		flush();

		if ( $retbytes ) {
			$cnt += strlen( $buffer );
		}
	}

	$status = fclose( $handle );

	if ( $retbytes && $status ) {
		return $cnt; // return num. bytes delivered like readfile() does.
	}

	return $status;
}
