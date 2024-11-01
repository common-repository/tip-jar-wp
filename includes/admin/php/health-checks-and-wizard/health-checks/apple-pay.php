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
 * Do a health check to see if Apple Pay is hooked up properly
 *
 * @param    array $health_checks All health checks.
 * @return   array $health_checks
 */
function tip_jar_wp_heath_check_apple_pay( $health_checks ) {

	$settings = get_option( 'tip_jar_wp_settings' );

	// If Apple Pay vars don't exist.
	$apple_pay_connected_domain_status = isset( $settings['stripe_apple_pay_status'] ) ? $settings['stripe_apple_pay_status'] : false;

	// Default unhealthy array.
	$unhealthy_array = array(
		'instruction'        => __( 'Apple Pay is not hooked up! Reconnect with Stripe to fix it.', 'tip-jar-wp' ),
		'fix_it_button_text' => __( 'Let\'s fix it!', 'tip-jar-wp' ),
		'component_data'     => array(
			'server_api_endpoint_set_stripe_connect_success_url' => admin_url() . '?tip_jar_wp_set_tjwp_scsr',
			'tip_jar_wp_set_tjwp_scsr_nonce' => wp_create_nonce( 'tip_jar_wp_set_tjwp_scsr' ),
			'strings'                        => array(
				'title'                      => __( 'Re-connect your Stripe Account to fix Apple Pay', 'tip-jar-wp' ),
				'description'                => __( 'Click the button below to re-connect your Stripe account and fix Apple Pay.', 'tip-jar-wp' ),
				'stripe_connect_button_text' => __( 'Connect your Stripe Account in Live Mode', 'tip-jar-wp' ),
			),
			'stripe_connect_url'             => tip_jar_wp_get_stripe_connect_button_url( 'live' ),
		),
	);

	if ( 'connected' === $apple_pay_connected_domain_status ) {
		$is_healthy = true;
	} else {
		$is_healthy = false;

		// Modify the unhealthy array.
		$unhealthy_array = array(
			'instruction'        => __( 'Apple Pay is not hooked up! Reconnect with Stripe to fix it.', 'tip-jar-wp' ),
			'fix_it_button_text' => __( 'Let\'s fix it!', 'tip-jar-wp' ),
			'component_data'     => array(
				'server_api_endpoint_set_stripe_connect_success_url' => admin_url() . '?tip_jar_wp_set_tjwp_scsr',
				'tip_jar_wp_set_tjwp_scsr_nonce' => wp_create_nonce( 'tip_jar_wp_set_tjwp_scsr' ),
				'strings'                        => array(
					'title'                      => __( 'Re-connect your Stripe Account to fix Apple Pay', 'tip-jar-wp' ),
					'description'                => $apple_pay_connected_domain_status,
					'stripe_connect_button_text' => __( 'Connect your Stripe Account in Live Mode', 'tip-jar-wp' ),
				),
				'stripe_connect_url'             => tip_jar_wp_get_stripe_connect_button_url( 'live' ),
			),
		);

	}

	// Make sure that live mode is enabled, otherwise Apple Pay doesn't work, for some reason.
	if ( $is_healthy ) {
		if ( ! isset( $settings['stripe_live_public_key'] ) || empty( $settings['stripe_live_public_key'] ) ) {
			$is_healthy = false;
		} else {
			$is_healthy = true;
		}
	}

	// Make sure that live mode is enabled, otherwise Apple Pay doesn't work.
	if ( $is_healthy ) {
		if ( ! isset( $settings['stripe_live_public_key'] ) || empty( $settings['stripe_live_public_key'] ) ) {
			$is_healthy = false;
		} else {
			$is_healthy = true;
		}
	}

	// Make sure the Apple Verification File exists.
	if ( $is_healthy ) {
		$apple_verification_file_exists = tip_jar_wp_create_apple_verification_file();

		if ( ! $apple_verification_file_exists ) {
			$is_healthy = false;

			$unhealthy_array = array(
				'instruction'        => __( 'Apple Pay is not hooked up! The Apple Verification file could not be created on your server. Contact your webhost and ask them to create a .well-known directory at your site\'s root directory, and place this file within it. This file verifies your domain with Apple for Apple Pay.', 'tip-jar-wp' ),
				'fix_it_button_text' => __( 'Let\'s fix it!', 'tip-jar-wp' ),
				'component_data'     => array(
					'server_api_endpoint_set_stripe_connect_success_url' => false,
					'tip_jar_wp_set_ctwp_scsr_nonce' => false,
					'strings'                        => array(
						'title'                      => __( 'There\'s an issue with your server...', 'tip-jar-wp' ),
						'description'                => __( 'The Apple Verification file could not be created. Contact your webhost and ask them to create a .well-known directory at your site\'s root directory and make sure it is writable by the WordPress user group. This file verifies your domain with Apple for Apple Pay.', 'tip-jar-wp' ),
						'stripe_connect_button_text' => __( 'Download Apple Verification File (then send to you your webhost)', 'tip-jar-wp' ),
					),
				),
			);

		}
	}

	// If this is a live site.
	if ( tip_jar_wp_is_site_localhost() ) {
		$is_healthy      = false;
		$unhealthy_array = array(
			'instruction'        => __( 'It looks like you are on a localhost. Apple Pay will not work on a localhost, but on a live site it will.', 'tip-jar-wp' ),
			'fix_it_button_text' => __( 'Read more', 'tip-jar-wp' ),
			'component_data'     => array(
				'server_api_endpoint_set_stripe_connect_success_url' => admin_url() . '?tip_jar_wp_set_tjwp_scsr',
				'tip_jar_wp_set_tjwp_scsr_nonce' => wp_create_nonce( 'tip_jar_wp_set_tjwp_scsr' ),
				'strings'                        => array(
					'title'       => __( 'Apple Pay will only work on a Live Site', 'tip-jar-wp' ),
					'description' => __( 'Apple requires that your domain is verified for Apple Pay to work. A localhost is not a verifiable website for Apple.', 'tip-jar-wp' ),
				),
				'stripe_connect_url'             => tip_jar_wp_get_stripe_connect_button_url( 'live' ),
			),
		);
	}

	$health_checks['apple_pay'] = array(
		'priority'        => 700,
		'is_healthy'      => $is_healthy,
		'is_health_check' => true,
		'is_wizard_step'  => false,
		'react_component' => 'Tip_Jar_WP_Stripe_Connect_Health_Check',
		'icon'            => TIP_JAR_WP_PLUGIN_URL . '/assets/images/svg/tipjarwp-logo.svg',
		'unhealthy'       => $unhealthy_array,
		'healthy'         => array(
			'instruction'    => __( 'Apple Pay is all hooked up and ready to go. Beautiful!', 'tip-jar-wp' ),
			'component_data' => array(
				'strings' => array(
					'title'       => __( 'Apple Pay connected!', 'tip-jar-wp' ),
					'description' => __( 'Great job! Apple Pay is all connected and ready to be used on your website!', 'tip-jar-wp' ),
				),
			),
		),
	);

	return $health_checks;

}
add_filter( 'tip_jar_wp_health_checks_and_wizard_vars', 'tip_jar_wp_heath_check_apple_pay' );
