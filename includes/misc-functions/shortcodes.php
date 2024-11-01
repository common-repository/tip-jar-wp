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
 * Enqueue the admins scripts for the Classic Editor.
 *
 * @since    1.0.0
 * @return   void
 */
function tip_jar_wp_classic_editor_enqueue_scripts() {
	$required_js_files = array( 'tip_jar_wp_js' );
	wp_enqueue_script( 'tip_jar_wp_classic_editor_js', TIP_JAR_WP_PLUGIN_URL . 'includes/admin/js/build/classic-editor/tip-form-classic-editor.js', $required_js_files, TIP_JAR_WP_VERSION, true );
	$required_js_files[] = 'tip_jar_wp_classic_editor_js';

	// Pass the defaults to the JS so they can be used in the boiler for the attributes.
	wp_localize_script(
		'tip_jar_wp_classic_editor_js',
		'tip_jar_wp_classic_editor_vars',
		array(
			'create_or_update_form_endpoint' => admin_url() . '?tip_jar_wp_create_or_update_form',
			'create_or_update_form_nonce'    => wp_create_nonce( 'tip_jar_wp_create_or_update_form_nonce' ),
			'get_form_endpoint'              => admin_url() . '?tip_jar_wp_get_form',
			'get_form_nonce'                 => wp_create_nonce( 'tip_jar_wp_get_form_nonce' ),
			'tip_jar_wp_block_default_json'  => tip_jar_wp_tip_form_vars(),
			'tip_jar_wp_dynamic_settings'    => tip_jar_wp_dynamic_tip_form_vars(),
		)
	);

	tip_jar_wp_localize_editing_strings();

}
add_action( 'admin_enqueue_scripts', 'tip_jar_wp_classic_editor_enqueue_scripts' );

/**
 * Shortcode which is used to output the tipping form
 *
 * @since    1.0.0
 * @param    array $atts The shortcode's attributes.
 * @return   string
 */
function tipjarwp_shortcode_callback( $atts ) {

	$atts = shortcode_atts(
		array(
			'id'         => null,
			'mode'       => null,
			'link_text'  => null,
			'open_style' => null,
		),
		$atts,
		'tipjarwp'
	);

	$sanitized_args = array();

	// Sanitize the Form ID.
	if ( ! empty( $atts['id'] ) ) {
		$sanitized_args['id'] = absint( $atts['id'] );
	}

	if ( isset( $sanitized_args['id'] ) ) {
		$form           = new Tip_Jar_WP_Form( $sanitized_args['id'] );
		$sanitized_args = json_decode( $form->json, true );
	} else {
		$sanitized_args = tip_jar_wp_tip_form_vars();
	}

	// Sanitize the Mode.
	if ( ! empty( $atts['mode'] ) ) {
		$sanitized_args['mode'] = sanitize_text_field( wp_unslash( $atts['mode'] ) );
	}

	// Sanitize the link text.
	if ( ! empty( $atts['link_text'] ) ) {
		$sanitized_args['strings']['link_text'] = sanitize_text_field( wp_unslash( $atts['link_text'] ) );
	}

	// Sanitize the open_style.
	if ( ! empty( $atts['open_style'] ) ) {
		$sanitized_args['open_style'] = sanitize_text_field( wp_unslash( $atts['open_style'] ) );
	}

	// For the featured embed, the value is stored as an encoded url. So we will decode it here and get the oembed value.
	$sanitized_args['fetched_oembed_html'] = isset( $sanitized_args['featured_embed'] ) ? tip_jar_wp_oembed_get( $sanitized_args['featured_embed'] ) : '';

	return tip_jar_wp_generate_output_for_tip_form( $sanitized_args );

}
add_shortcode( 'tipjarwp', 'tipjarwp_shortcode_callback' );

/**
 * Check the content of the wp_editor, and scan it for tip_jar_wp shortcodes, and their corresponding IDs.
 * We will then output "Edit Tip Jar Form 123" buttons for each one above the editor.
 *
 * @since 1.0.0
 *
 * @param string $content        Default editor content.
 * @param string $default_editor The default editor for the current user.
 *                               Either 'html' or 'tinymce'.
 */
function tip_jar_wp_get_shortcodes_in_editor( $content, $default_editor ) {

	$form_ids_in_text = array();
	$pattern          = get_shortcode_regex();

	if (
		preg_match_all( '/' . $pattern . '/s', $content, $matches ) &&
		array_key_exists( 2, $matches )
	) {

		$counter = 0;
		foreach ( $matches[2] as $shortcode_name ) {
			if ( 'tipjarwp' === $shortcode_name ) {
				// Extract the ID.
				$exploded_values = explode( '"', $matches[3][ $counter ] );

				// If this tip form has an ID paramater, it has a form in the table. If not, it's just a tip form using the default settings.
				if ( isset( $exploded_values[1] ) ) {
					$form_ids_in_text[] = $exploded_values[1];
					$counter++;
				}
			}
		}
	}

	// If there are shortcodes that need edit buttons here...
	if ( ! empty( $form_ids_in_text ) ) {
		echo '<div class="tip-jar-wp-media-buttons"><div class="wp-media-buttons">';
		// Loop through each Form ID found, and output a button for it.
		foreach ( $form_ids_in_text as $form_id ) {

			$form = new Tip_Jar_WP_Form( $form_id );

			// Confirm this form actually exists.
			if ( $form->id ) {
				echo '<button class="button" type="button" onclick="tip_jar_wp_set_shortcode_insert_modal_to_open(' . absint( $form_id ) . ', false); return false;">' . esc_textarea( sprintf( __( 'Edit Tip Jar Shortcode #%s', 'tip-jar-wp' ), $form_id ) ) . '</button>';
			}
		}
		echo '</div></div>';
	}

	return $content;
}
add_filter( 'the_editor_content', 'tip_jar_wp_get_shortcodes_in_editor', 10, 2 );

/**
 * Media Button
 *
 * Returns the "Tip Jar Shortcode" TinyMCE button.
 * We also scan the post_content value for tip_jar_wp shortcodes, and output an edit button for each one here.
 *
 * @since  1.0.0
 * @param  string $context The string of buttons that already exist.
 * @return void
 */
function tip_jar_wp_shortcode_button( $context ) {

	echo '<button class="button" type="button" onclick="tip_jar_wp_set_shortcode_insert_modal_to_open( null, true); return false;">' . esc_textarea( __( 'Create New Tip Jar Shortcode', 'tip-jar-wp' ) ) . '</button>';
}
add_action( 'media_buttons', 'tip_jar_wp_shortcode_button', 11 );

/**
 * Output the footer container for the lighbox where the Tip Jar Form editior will appear.
 *
 * @since  1.0.0
 * @return void
 */
function tip_jar_wp_classic_editor_ligtbox() {
	echo '<div id="tip_jar_wp_classic_editor_lightbox"></div>';
}
add_action( 'admin_footer', 'tip_jar_wp_classic_editor_ligtbox' );
