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
 * Do a health check to see if the wizard has been run.
 *
 * @since    1.0.0
 * @param    array $health_checks All the health checks.
 * @return   array $health_checks
 */
function tip_jar_wp_heath_do_wizard( $health_checks ) {

	$current_wizard_status = get_option( 'tip_jar_wp_wizard_status' );
	if ( 'completed' !== $current_wizard_status ) {
		$is_healthy = false;
	} else {
		$is_healthy = true;
	}

	if ( 'in_progress' === $current_wizard_status ) {
		$instruction        = __( 'The set-up helper is in progress. Finish it to complete the set-up of Tip Jar WP.', 'tip-jar-wp' );
		$fix_it_button_text = __( 'Complete set-up!', 'tip-jar-wp' );
	} else {
		$instruction        = __( 'Run the set-up helper to get started with Tip Jar WP!', 'tip-jar-wp' );
		$fix_it_button_text = __( 'Let\'s get started!', 'tip-jar-wp' );
	}

	$health_checks['do_wizard'] = array(
		'priority'        => 10,
		'is_healthy'      => $is_healthy,
		'is_health_check' => true,
		'is_wizard_step'  => true,
		'react_component' => 'Tip_Jar_WP_Do_Wizard_Health_Check',
		'icon'            => TIP_JAR_WP_PLUGIN_URL . '/assets/images/svg/tipjarwp-logo.svg',
		'unhealthy'       => array(
			'instruction'        => $instruction,
			'fix_it_button_text' => $fix_it_button_text,
			'health_check_icon'  => 'dashicons-yes',
			'component_data'     => array(
				'strings'            => array(
					'title'                        => __( 'Welcome to Tip Jar WP', 'tip-jar-wp' ),
					'description'                  => __( 'You\'re almost ready to start accepting single and recurring payments through your WordPress! There\'s a few steps to make sure everything is set up. Let\'s go through them one by one.', 'tip-jar-wp' ),
					'do_later_button_text'         => __( 'Later', 'tip-jar-wp' ),
					'next_wizard_step_button_text' => __( 'Get started', 'tip-jar-wp' ),
				),
				'server_api_endpoint_tip_jar_wp_start_wizard' => admin_url() . '?tip_jar_wp_start_wizard',
				'start_wizard_nonce' => wp_create_nonce( 'tip_jar_wp_start_wizard' ),
				'server_api_endpoint_tip_jar_wp_wizard_later' => admin_url() . '?tip_jar_wp_wizard_later',
				'wizard_later_nonce' => wp_create_nonce( 'tip_jar_wp_wizard_later' ),

			),
		),
		'healthy'         => array(
			'instruction'              => __( 'You have completed the helper to get started with Tip Jar WP. You can run it again anytime if you\'d like to be sure everything is configured correctly.', 'tip-jar-wp' ),
			'fix_it_again_button_text' => __( 'Run the set-up helper again', 'tip-jar-wp' ),
		),
	);

	return $health_checks;

}
add_filter( 'tip_jar_wp_health_checks_and_wizard_vars', 'tip_jar_wp_heath_do_wizard' );
