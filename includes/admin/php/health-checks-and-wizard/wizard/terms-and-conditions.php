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
 * Create a wizard step to help the user set the terms and conditions for the tip form
 *
 * @since    1.0.0
 * @param    array $wizard_steps All the health checks and wizard steps.
 * @return   array $wizard_steps
 */
function tip_jar_wp_terms_and_conditions_wizard_step( $wizard_steps ) {

	$saved_settings = get_option( 'tip_jar_wp_settings' );

	$wizard_steps['tip_form_terms'] = array(
		'priority'        => 1000,
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
					'title'       => __( 'Would you like to show a "Terms and Conditions" checkbox?', 'tip-jar-wp' ),
					'description' => __( 'If you would like to show a "Terms and Conditions" checkbox on the payment form, and require users to agree prior to payment, enter those terms and conditions below. If you would rather not show that, leave this blank.', 'tip-jar-wp' ),
				),
				'input_field'                         => array(
					'react_component'                     => 'MP_WP_Admin_TextArea_Field',
					'saved_value'                         => tip_jar_wp_get_saved_setting( $saved_settings, 'tip_form_terms' ),
					'client_validation_callback_function' => 'tip_jar_wp_validate_simple_input',
					'server_validation_callback_function' => 'tip_jar_wp_validate_simple_input',
					'server_api_endpoint_url'             => admin_url() . '?tip_jar_wp_save_setting',
					'nonce'                               => wp_create_nonce( 'tip_form_terms' ),
					'default_value'                       => '',
					'instruction_codes'                   => array(
						'empty_initial'     => array(
							'instruction_type'    => 'normal',
							'instruction_message' => __( 'Enter the terms and conditions for the tip form.', 'tip-jar-wp' ),
						),
						'empty_not_initial' => array(
							'instruction_type'    => 'normal',
							'instruction_message' => __( 'Enter the terms and conditions for the tip form.', 'tip-jar-wp' ),
						),
						'error'             => array(
							'instruction_type'    => 'error',
							'instruction_message' => __( 'Enter the terms and conditions for the tip form.', 'tip-jar-wp' ),
						),
						'success'           => array(
							'instruction_type'    => 'success',
							'instruction_message' => __( 'Enter the terms and conditions for the tip form.', 'tip-jar-wp' ),
						),
					),
				),
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
add_filter( 'tip_jar_wp_health_checks_and_wizard_vars', 'tip_jar_wp_terms_and_conditions_wizard_step' );
