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

// Enqueue the frontend scripts so they can be used inside Gutenberg.
add_action( 'admin_enqueue_scripts', 'tip_jar_wp_enqueue_scripts' );

/**
 * Format the tip jar wp variables for Gutenberg.
 * This is used only for the initial settings in a block. Otherwise the block pulls from the post_content.
 *
 * @since    1.0.0
 * @return   array
 */
function tip_jar_wp_gutenberg_tip_form_vars_defaults() {
	$tip_form_vars = wp_json_encode( tip_jar_wp_tip_form_vars() );

	$gutenberg_defaults = array(
		'json' => array(
			'type'    => 'string',
			'default' => $tip_form_vars,
		),
	);

	return $gutenberg_defaults;
}

/**
 * Enqueue the scripts for this Gutenberg Block.
 *
 * @since    1.0.0
 * @return   void
 */
function tip_jar_wp_block_editor_scripts() {

	// Required things to build Gutenberg Blocks.
	$required_js_files = array(
		'wp-blocks',
		'wp-i18n',
		'wp-element',
		'wp-components',
		'wp-editor',
	);

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.development' : '.production.min';

	// Block strings.
	$plugin_slug             = 'tipjarwp';
	$block_slug              = 'tip-form';
	$block_namespace         = 'tipjarwp/' . $block_slug;
	$script_slug             = $plugin_slug . '-' . $block_slug;
	$script_slug_underscores = str_replace( '-', '_', $plugin_slug ) . '_' . str_replace( '-', '_', $block_slug );
	$style_slug              = $plugin_slug . '-' . $block_slug . '-style';
	$editor_style_slug       = $plugin_slug . '-' . $block_slug . '-editor-style';

	// Register the block.
	wp_register_script(
		$script_slug,
		TIP_JAR_WP_PLUGIN_URL . 'includes/admin/js/build/gutenberg/blocks/tip-form.js',
		$required_js_files,
		TIP_JAR_WP_VERSION,
		true
	);

	// Pass the defaults to the JS so they can be used in the boiler for the attributes.
	// Note that this is used only for the initial settings in a block. Otherwise the block pulls from the post_content.
	// And these are completely ignored.
	wp_localize_script(
		$script_slug,
		'tip_jar_wp_gutenberg_vars',
		array(
			'create_form_endpoint'          => admin_url() . '?tip_jar_wp_create_or_update_form',
			'create_form_nonce'             => wp_create_nonce( 'tip_jar_wp_create_or_update_form_nonce' ),
			'tip_jar_wp_block_default_json' => tip_jar_wp_gutenberg_tip_form_vars_defaults(),
			'tip_jar_wp_dynamic_settings'   => tip_jar_wp_dynamic_tip_form_vars(),
		)
	);

	// Register the block.
	register_block_type(
		$block_namespace,  // Block name with namespace.
		[
			'style'           => $style_slug, // General block style slug.
			'editor_style'    => $editor_style_slug, // Editor block style slug.
			'editor_script'   => $script_slug,  // The block script slug.
			'render_callback' => 'tip_jar_wp_tip_form_block_server_side_render', // The render callback.
		]
	);

}
add_action( 'init', 'tip_jar_wp_block_editor_scripts' );

/**
 * The actual output of the block, rendered on the server side.
 *
 * @since    1.0.0
 * @param    array $passed_in_attributes The saved attibutes to this block.
 * @return   string The output for this block.
 */
function tip_jar_wp_tip_form_block_server_side_render( $passed_in_attributes ) {
	if ( isset( $passed_in_attributes['json'] ) ) {
		// Get the attributes about this block from the post_content.
		$attributes = json_decode( $passed_in_attributes['json'], true );

		// Check if this Block has an ID assigned yet.
		if ( isset( $attributes['id'] ) ) {
			// Make sure the json of the custom Forms table matches what is saved to the Gutenberg block.
			$form   = new Tip_Jar_WP_Form( $attributes['id'] );
			$result = $form->update(
				array(
					'json' => wp_kses_post( $passed_in_attributes['json'] ),
				)
			);

			// Make sure we are using the values from the form for standardization.
			$form       = new Tip_Jar_WP_Form( $attributes['id'] );
			$attributes = json_decode( wp_kses_post( $form->json ), true );
		}
	} else {
		$attributes = array();
	}

	// The default Tip Form variables that are used to make the Tip Form custom.
	$tip_form_vars = tip_jar_wp_tip_form_vars();

	if ( $attributes ) {
		// Loop through each unique attribute, and add/override it in the $tip_form_vars.
		foreach ( $attributes as $key => $value ) {

			if ( 'featured_embed' === $key ) {
				$tip_form_vars[ $key ] = $value;
				// For the featured embed, the value is stored as an encoded url. So we will decode it here and get the oembed value.
				$tip_form_vars['fetched_oembed_html'] = tip_jar_wp_oembed_get( urldecode( $value ) );
			} else {
				$tip_form_vars[ $key ] = $value;
			}
		}
	}

	return tip_jar_wp_generate_output_for_tip_form( $tip_form_vars );
}
