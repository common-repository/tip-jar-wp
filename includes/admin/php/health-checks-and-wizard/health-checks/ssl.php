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
 * Do a health check to see if SSL is running or not
 *
 * @since    1.0.0
 * @param    array $health_checks All the health checks.
 * @return   array $health_checks
 */
function tip_jar_wp_heath_check_ssl( $health_checks ) {

	if ( is_ssl() ) {
		$is_healthy = true;
	} else {
		$is_healthy = false;
	}

	if ( ! tip_jar_wp_is_site_reachable_over_ssl() ) {

		$unhealthy_array = array(
			'mode'               => 'no_certificate_exists',
			'instruction'        => __( 'Your website does not have an SSL certificate installed. This is required to accept payments. Contact your webhost and ask them to install an SSL certificate to fix this!', 'tip-jar-wp' ),
			'fix_it_button_text' => __( 'How to fix this', 'tip-jar-wp' ),
			'component_data'     => array(
				'strings' => array(
					'title'                       => __( 'An SSL certificate (https) is required to accept payments.', 'tip-jar-wp' ),
					'description'                 => __( 'Most webhosts can install an SSL certificate for you for free. Contact your webhost and ask them about "LetsEncrypt" if they are requiring that you pay for it. An SSL certificate is required to accept payments with Tip Jar WP. It protects both you and your users by encrypting all of the data passing through your website. Once your webhost has the SSL certificate installed, refresh this page to continue setting up Tip Jar WP.', 'tip-jar-wp' ),
					'required_action_title'       => __( 'Required action:', 'tip-jar-wp' ),
					'required_action_description' => __( 'Contact your web host and ask them to install an SSL certificate', 'tip-jar-wp' ),
				),
			),
		);
		// If a certificate exists, we just aren't running on it.
	} else {

		$unhealthy_array = array(
			'mode'               => 'certificate_exists',
			'instruction'        => __( 'Your website has an SSL certificate, but you aren\'t using it!', 'tip-jar-wp' ),
			'fix_it_button_text' => __( 'Let\'s fix it!', 'tip-jar-wp' ),
			'component_data'     => array(
				'server_api_endpoint_update_wordpress_url' => admin_url() . '?tip_jar_wp_update_wordpress_url',
				'tip_jar_wp_update_wordpress_url_nonce'    => wp_create_nonce( 'tip_jar_wp_update_wordpress_url_nonce' ),
				'strings'                                  => array(
					'title'                     => __( 'You have an SSL certificate, but you aren\'t using it.', 'tip-jar-wp' ),
					'description'               => __( 'To fix this, WordPress needs to add "https" to the start of your domain in its settings. If you\'d like to do that now, click the button below. After clicking you will have to log in again.', 'tip-jar-wp' ),
					'button_text'               => __( 'Change my WordPress URL to include "https"', 'tip-jar-wp' ),
					'update_failed_title'       => __( 'Unable to automatically update.', 'tip-jar-wp' ),
					'update_failed_description' => __( 'To do this manually, go to "Settings" > "General" and update "WordPress Address (URL)" and "Site Address (URL)" to begin with "https"..', 'tip-jar-wp' ),
				),
			),
		);
	}

	$health_checks['ssl'] = array(
		'priority'        => 100,
		'is_healthy'      => $is_healthy,
		'is_health_check' => true,
		'is_wizard_step'  => true,
		'react_component' => 'Tip_Jar_WP_SSL_Health_Check',
		'icon'            => TIP_JAR_WP_PLUGIN_URL . '/assets/images/svg/lock.svg',
		'unhealthy'       => $unhealthy_array,
		'healthy'         => array(
			'instruction'    => __( 'You have an SSL certificate installed and running. Good work.', 'tip-jar-wp' ),
			'component_data' => array(
				'strings' => array(
					'title'                        => __( 'You have an SSL certificate installed. Perfect!', 'tip-jar-wp' ),
					'description'                  => __( 'Great job! Your site is encrypted and running over HTTPS.', 'tip-jar-wp' ),
					'next_wizard_step_button_text' => __( 'Next step', 'tip-jar-wp' ),
				),
			),
		),

	);

	return $health_checks;

}
add_filter( 'tip_jar_wp_health_checks_and_wizard_vars', 'tip_jar_wp_heath_check_ssl' );
