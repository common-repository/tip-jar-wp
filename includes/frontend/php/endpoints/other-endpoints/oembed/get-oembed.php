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
 * Accept a value, and attempt to get the oembed code for it (YouTube, Instagram, etc).
 *
 * @access   public
 * @since    1.0.0
 * @return   mixed
 */
function tip_jar_wp_get_oembed() {

	if ( ! isset( $_GET['tip_jar_wp_get_oembed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}

	$endpoint_result = tip_jar_wp_get_oembed_handler();

	echo wp_json_encode( $endpoint_result );
	die();
}
add_action( 'init', 'tip_jar_wp_get_oembed' );

/**
 * Attempt to convert a string into oembed code.
 *
 * @access   public
 * @since    1.0.0
 * @return   array
 */
function tip_jar_wp_get_oembed_handler() {

	// Verify the nonce.
	if ( ! isset( $_POST['tip_jar_wp_get_oembed_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tip_jar_wp_get_oembed_nonce'] ) ), 'tip_jar_wp_get_oembed_nonce' ) ) {
		return array(
			'success'    => false,
			'error_code' => 'nonce_failed',
			'details'    => __( 'Nonce failed.', 'tip-jar-wp' ),
		);
	}

	// Check if values were not there that need to be.
	if ( ! is_array( $_POST ) || ! isset( $_POST['tip_jar_wp_oembed_string_source'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return array(
			'success'    => false,
			'error_code' => 'missing_values',
			'details'    => 'No value was passed to the oembed.',
		);
	}

	$oembed_string_source = sanitize_text_field( wp_unslash( $_POST['tip_jar_wp_oembed_string_source'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$embed_code = tip_jar_wp_oembed_get( $oembed_string_source );

	if ( ! $embed_code ) {
		return array(
			'success'              => false,
			'error_code'           => 'no_oembed_found',
			'details'              => 'That was not a valid oembed source.',
			'oembed_string_source' => $oembed_string_source,
		);
	}

	return array(
		'success'      => true,
		'success_type' => 'oembed_successfully_found',
		'oembed_html'  => $embed_code,
	);

}

/**
 * Custom abstraction for wp_oembed_get to better handle instagram and other sources.
 *
 * @access   public
 * @since    1.0.1.3
 * @param    string $oembed_string_source The source string/url being converted to an oembed.
 * @return   string
 */
function tip_jar_wp_oembed_get( $oembed_string_source ) {

	// If this is an instagram post, build the iframe code only, instead of loding their freaking js script just to embed something.
	if ( false !== strpos( $oembed_string_source, 'https://www.instagram.com/p/' ) ) {

		$insta_response = json_decode( wp_remote_retrieve_body( wp_remote_post( 'https://api.instagram.com/oembed?url=' . $oembed_string_source ) ), true );
		$height         = absint( $insta_response['thumbnail_height'] ) + 54;
		$width          = absint( $insta_response['thumbnail_width'] );

		// Extract the values like the id of the post, the height, and the width.
		$exploder          = explode( 'https://www.instagram.com/p/', $oembed_string_source );
		$exploder          = explode( '/', $exploder[1] );
		$instagram_post_id = $exploder[0];

		$embed_code = '<iframe class="instagram-media instagram-media-rendered" id="instagram-embed-0" src="https://www.instagram.com/p/' . $instagram_post_id . '/embed/captioned/" allowtransparency="true" allowfullscreen="true" frameborder="0" width="' . $width . 'px" height="' . $height . 'px" data-instgrm-payload-id="instagram-media-payload-0" scrolling="yes"></iframe>';
	} else {
		$embed_code = wp_oembed_get( $oembed_string_source );
	}

	return $embed_code;
}

/**
 * Override some wp_oembed_get defaults so that YouTube plays inline on mobile.
 * Without this, the youtube video plays fullscreen on ios no matter what.
 *
 * @access   public
 * @since    1.0.1.3
 * @param    string $html The HTML WordPress's wp_oembed_get function returns.
 * @param    string $url The original URL.
 * @param    array  $args The args that were passed to wp_oembed_get.
 * @return   string
 */
function tip_jar_wp_oembed_make_youtube_play_inline_on_mobile( $html, $url, $args ) {

	// Modify youtube params.
	if ( false !== strpos( $html, 'youtube.com/embed/' ) ) {
		$html = str_replace( '?feature=oembed', '?rel=0&playsinline=1&modestbranding=1&autohide=1&showinfo=0&controls=0', $html );
	}

	// Return oEmbed html.
	return $html;

}
add_filter( 'oembed_result', 'tip_jar_wp_oembed_make_youtube_play_inline_on_mobile', 12, 3 );
