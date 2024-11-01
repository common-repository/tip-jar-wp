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
 * Make sure that all default values which should exist in the args for a tip jar, do exist.
 * This covers cases where the json has been saved to the forms table, but then new strings are added later to the tip_jar_wp_tip_form_vars function.
 *
 * @since    1.0.1.3
 * @param    array $tip_form_vars Any values in this array will override the default values in tip_jar_wp_tip_form_vars().
 * @return   string
 */
function tip_jar_wp_parse_tip_form_args( $tip_form_vars ) {
	// Make sure all of the fields that should exist do.
	$default_vars             = tip_jar_wp_tip_form_vars();
	$tip_form_vars            = wp_parse_args( $tip_form_vars, $default_vars );
	$tip_form_vars['strings'] = tip_jar_wp_wp_parse_args( $tip_form_vars['strings'], $default_vars['strings'] );
	return $tip_form_vars;
}

/**
 * Function which is shared by Gutenberg Blocks and Shortcodes to generate output for a Tip Form.
 *
 * @since    1.0.0
 * @param    array $tip_form_vars Any values in this array will override the default values in tip_jar_wp_tip_form_vars().
 * @return   string
 */
function tip_jar_wp_generate_output_for_tip_form( $tip_form_vars ) {

	global $tip_jar_wp_forms_on_page;
	$form_number = is_array( $tip_jar_wp_forms_on_page ) ? count( $tip_jar_wp_forms_on_page ) + 1 : 1;

	$dynamic_tip_form_vars = tip_jar_wp_dynamic_tip_form_vars();

	// Loop through each tip form variable and remove any values that are dynamic.
	foreach ( $dynamic_tip_form_vars as $dynamic_tip_form_var_key => $dynamic_tip_form_var_value ) {
		unset( $tip_form_vars[ $dynamic_tip_form_var_key ] );
	}

	// Add this Form's JSON to the global array so we can output it into the footer.
	$tip_jar_wp_forms_on_page[ $form_number ] = array(
		'unique_vars'  => tip_jar_wp_parse_tip_form_args( $tip_form_vars ),
		'dynamic_vars' => $dynamic_tip_form_vars,
	);

	// If this tip form is set to open "in_modal", output the actual React Component in the footer, and only the button/link to open it here.
	if (
		! empty( $tip_form_vars['mode'] ) &&
		'form' !== $tip_form_vars['mode'] &&
		'in_modal' === $tip_form_vars['open_style']
	) {
		if ( 'text_link' === $tip_form_vars['mode'] ) {
			return '<a class="tip-jar-wp-a-tag tip-jar-wp-modal-link" onclick="tip_jar_wp_set_modal_to_open( ' . esc_attr( $form_number ) . ' )">' . $tip_form_vars['strings']['link_text'] . '</a>';
		}

		if ( 'button' === $tip_form_vars['mode'] ) {
				return '<button class="button tip-jar-wp-button" onclick="tip_jar_wp_set_modal_to_open( ' . esc_attr( $form_number ) . ' )">' . $tip_form_vars['strings']['link_text'] . '</button>';
		}
	}

	// Otherwise only output the element without an ID.
	return '<span id="tip-jar-wp-element-' . esc_attr( $form_number ) . '" class="tip-jar-wp-element" tip-jar-wp-form-number="' . esc_attr( $form_number ) . '"></span>';

}

/**
 * Output Tip Jar WP JSON for a Tip Form into the footer in a hidden div
 *
 * @since    1.0.0
 * @global   array $tip_jar_wp_forms_on_page The JSON for each Tip form on the page.
 * @return   void
 */
function tip_jar_wp_json_in_footer() {

	global $tip_jar_wp_forms_on_page;

	if ( empty( $tip_jar_wp_forms_on_page ) ) {
		return;
	}

	$allowed_tags                                  = wp_kses_allowed_html( 'post' );
	$allowed_tags['div']['hidden']                 = true;
	$allowed_tags['div']['tip-jar-wp-form-number'] = true;

	foreach ( $tip_jar_wp_forms_on_page as $form_number => $form_json ) {
		echo wp_kses( '<div hidden style="display:none;" id="tip-jar-wp-element-unique-vars-json-' . esc_attr( $form_number ) . '" class="tip-jar-wp-element-json" tip-jar-wp-form-number="' . esc_attr( $form_number ) . '">' . esc_textarea( wp_json_encode( $form_json['unique_vars'] ) ) . '</div>', $allowed_tags );
		echo wp_kses( '<div hidden style="display:none;" id="tip-jar-wp-element-dynamic-vars-json-' . esc_attr( $form_number ) . '" class="tip-jar-wp-element-json" tip-jar-wp-form-number="' . esc_attr( $form_number ) . '">' . esc_textarea( wp_json_encode( $form_json['dynamic_vars'] ) ) . '</div>', $allowed_tags );

		// If this tip form is set to open "in_modal", output the actual React Component here in the footer, where it sits silently until opened.
		if (
			! empty( $form_json['unique_vars']['mode'] ) &&
			'form' !== $form_json['unique_vars']['mode'] &&
			'in_modal' === $form_json['unique_vars']['open_style']
		) {
			echo '<span id="tip-jar-wp-element-' . esc_attr( $form_number ) . '" class="tip-jar-wp-element" tip-jar-wp-form-number="' . esc_attr( $form_number ) . '"></span>';
		}
	}

}
add_action( 'wp_footer', 'tip_jar_wp_json_in_footer' );
