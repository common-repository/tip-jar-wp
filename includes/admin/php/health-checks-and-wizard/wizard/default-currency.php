<?php
/**
 * Tip Jar WP
 *
 * @package     Tip Jar WP
 * @subpackage  Classes/Tip Jar WP
 * @copyright   Copyright (c) 2019, Tip Jar WP
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create a wizard step to help the user set the default currency for their site.
 *
 * @since    1.0.0
 * @param    array $wizard_steps All the health checks and wizard steps.
 * @return   array $wizard_steps
 */
function tip_jar_wp_default_currency_wizard_step( $wizard_steps ) {

	$saved_settings = get_option( 'tip_jar_wp_settings' );

	$wizard_steps['default_currency'] = array(
		'priority'        => 600,
		'is_healthy'      => false,
		'is_health_check' => false,
		'is_wizard_step'  => true,
		'react_component' => 'Tip_Jar_WP_Setting_Wizard',
		'icon'            => TIP_JAR_WP_PLUGIN_URL . '/assets/images/svg/tipjarwp-logo.svg',
		'unhealthy'       => array(
			'component_data' => array(
				'server_api_endpoint_complete_wizard' => admin_url() . '?tip_jar_wp_complete_wizard',
				'complete_wizard_nonce'               => wp_create_nonce( 'tip_jar_wp_complete_wizard' ),
				'strings'                             => array(
					'title'       => __( 'Set a default currency', 'tip-jar-wp' ),
					'description' => __( 'This is the currency that will be selected by default when users see your form', 'tip-jar-wp' ),
				),
				'input_field'                         => array(
					'react_component'                     => 'MP_WP_Admin_Select_Field',
					'type'                                => 'text',
					'default_value'                       => 'USD',
					'saved_value'                         => tip_jar_wp_get_saved_setting( $saved_settings, 'default_currency' ),
					'initially_available_values'          => tip_jar_wp_get_currencies(),
					'fetch_options_endpoint'              => admin_url() . '?tip_jar_wp_ajax_get_currencies',
					'client_validation_callback_function' => 'tip_jar_wp_validate_simple_input',
					'server_validation_callback_function' => 'tip_jar_wp_validate_currency_input',
					'server_api_endpoint_url'             => admin_url() . '?tip_jar_wp_save_setting',
					'nonce'                               => wp_create_nonce( 'default_currency' ),
					'instruction_codes'                   => array(
						'empty_initial'     => array(
							'instruction_type'    => 'normal',
							'instruction_message' => __( 'Enter the 3-letter currency code tips should use', 'tip-jar-wp' ),
						),
						'empty_not_initial' => array(
							'instruction_type'    => 'normal',
							'instruction_message' => __( 'Enter the 3-letter currency code tips should use', 'tip-jar-wp' ),
						),
						'error'             => array(
							'instruction_type'    => 'error',
							'instruction_message' => __( 'Enter the 3-letter currency code tips should use', 'tip-jar-wp' ),
						),
						'invalid_selection' => array(
							'instruction_type'    => 'error',
							'instruction_message' => 'Please enter a valid 3-letter currency.',
						),
						'success'           => array(
							'instruction_type'    => 'success',
							'instruction_message' => __( 'Enter the 3-letter currency code tips should use', 'tip-jar-wp' ),
						),
					),
					'no_matching_values_text'             => __( 'No matching currencies found. Try another search', 'tip-jar-wp' ),
				),
				'success_svg'                         => tip_jar_wp_get_svg_icon( '#f9b429' ),
			),
		),
		'healthy'         => array(
			'component_data' => array(
				'strings' => array(
					'title'                        => __( 'You have successfully chosen a default currency.', 'tip-jar-wp' ),
					'description'                  => __( 'This is the currency that your users will see by default.', 'tip-jar-wp' ),
					'next_wizard_step_button_text' => __( 'Next step', 'tip-jar-wp' ),
				),
			),
		),
	);

	return $wizard_steps;

}
add_filter( 'tip_jar_wp_health_checks_and_wizard_vars', 'tip_jar_wp_default_currency_wizard_step' );
