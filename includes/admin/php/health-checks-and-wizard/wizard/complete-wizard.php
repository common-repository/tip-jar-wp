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
 * @param    array $health_checks All the health checks and wizard steps.
 * @return   array $health_checks
 */
function tip_jar_wp_heath_check_wizard_complete( $health_checks ) {

	$health_checks['complete_wizard'] = array(
		'priority'        => 99999999999,
		'is_healthy'      => true,
		'is_health_check' => false,
		'is_wizard_step'  => true,
		'react_component' => 'Tip_Jar_WP_Complete_Wizard',
		'icon'            => TIP_JAR_WP_PLUGIN_URL . '/assets/images/svg/tipjarwp-logo.svg',
		'healthy'         => array(
			'component_data' => array(
				'server_api_endpoint_complete_wizard' => admin_url() . '?tip_jar_wp_complete_wizard',
				'complete_wizard_nonce'               => wp_create_nonce( 'tip_jar_wp_complete_wizard' ),
				'strings'                             => array(
					'title'                       => __( 'You\'re all set!', 'tip-jar-wp' ),
					'description'                 => __( 'You are ready to start accepting single and recurring payments through your WordPress using Tip Jar WP.', 'tip-jar-wp' ),
					'complete_wizard_button_text' => __( 'Complete', 'tip-jar-wp' ),
				),
			),
		),
	);

	return $health_checks;

}
add_filter( 'tip_jar_wp_health_checks_and_wizard_vars', 'tip_jar_wp_heath_check_wizard_complete' );
