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
 * Do a health check to see if Stripe Test mode is hooked up properly
 *
 * @param    array $health_checks All the health checks.
 * @return   array $health_checks
 */
function tip_jar_wp_heath_check_stripe_test_mode( $health_checks ) {

	$settings = get_option( 'tip_jar_wp_settings' );

	// If Stripe test mode has been not been connected yet.
	if ( ! isset( $settings['stripe_test_public_key'] ) || empty( $settings['stripe_test_public_key'] ) ) {
		$is_healthy = false;
	} else {
		$is_healthy = true;
	}

	$health_checks['stripe_test_mode'] = array(
		'priority'        => TIP_JAR_WP_WIZARD_TEST_MODE ? 200 : 500,
		'is_healthy'      => $is_healthy,
		'is_health_check' => true,
		'is_wizard_step'  => TIP_JAR_WP_WIZARD_TEST_MODE ? true : false, // The constant allows the wizard to onboard you in Stripe Test mode if true.
		'react_component' => 'Tip_Jar_WP_Stripe_Connect_Health_Check',
		'icon'            => TIP_JAR_WP_PLUGIN_URL . '/assets/images/svg/hard-hat.svg',
		'unhealthy'       => array(
			'instruction'        => __( 'If you would like to run test payments in test mode, click here to connect Stripe\'s test mode. Setting up test mode is not required to accept real payments.', 'tip-jar-wp' ),
			'fix_it_button_text' => __( 'Set up Stripe\'s Test Mode (Optional)', 'tip-jar-wp' ),
			'health_check_icon'  => 'dashicons-info',
			'component_data'     => array(
				'server_api_endpoint_set_stripe_connect_success_url' => admin_url() . '?tip_jar_wp_set_tjwp_scsr',
				'tip_jar_wp_set_tjwp_scsr_nonce' => wp_create_nonce( 'tip_jar_wp_set_tjwp_scsr' ),
				'strings'                        => array(
					'title'                      => __( 'Connect your Stripe Account in Test Mode', 'tip-jar-wp' ),
					'description'                => __( 'This makes it possible to run test payments, and it\'s a good thing to have in place.', 'tip-jar-wp' ),
					'stripe_connect_button_text' => __( 'Connect your Stripe Account in Test Mode', 'tip-jar-wp' ),
				),
				'stripe_connect_url'             => tip_jar_wp_get_stripe_connect_button_url( 'test' ),
			),
		),
		'healthy'         => array(
			'instruction'    => __( 'Stripe (Test Mode) is connected and ready to help run tests when needed.', 'tip-jar-wp' ),
			'component_data' => array(
				'strings' => array(
					'title'                        => __( 'Stripe is now connected to your website in Test Mode.', 'tip-jar-wp' ),
					'description'                  => __( 'Now, if you want to test payments in test mode, you can by using a test credit card.', 'tip-jar-wp' ),
					'next_wizard_step_button_text' => __( 'Next step', 'tip-jar-wp' ),
				),
			),
		),
	);

	return $health_checks;

}
add_filter( 'tip_jar_wp_health_checks_and_wizard_vars', 'tip_jar_wp_heath_check_stripe_test_mode' );
