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
 * Do a health check to see if Stripe Live webhooks are hooked up properly
 *
 * @param    array $health_checks All the health checks.
 * @return   array $health_checks
 */
function tip_jar_wp_heath_check_stripe_live_webhook_signature( $health_checks ) {

	$saved_settings = get_option( 'tip_jar_wp_settings' );

	$webhook_signature = tip_jar_wp_get_saved_setting( $saved_settings, 'stripe_webhook_signing_secret_live_mode' );

	$webhook_validated = tip_jar_wp_validate_live_stripe_webhook( $webhook_signature );

	// If Stripe live mode signing secret does not validate.
	if ( ! $webhook_validated['success'] ) {
		$is_healthy = false;
	} else {
		$is_healthy = true;
	}

	// If this is a live site.
	if ( ! tip_jar_wp_is_site_localhost() ) {
		$unhealthy_array = array(
			'mode'               => 'live_site',
			'instruction'        => __( 'Your Stripe webhook is missing (Live Mode). This is required for Stripe to talk to your website in live mode.', 'tip-jar-wp' ),
			'fix_it_button_text' => __( 'Let\'s fix it!', 'tip-jar-wp' ),
			'component_data'     => array(
				'server_api_endpoint_set_stripe_connect_success_url' => admin_url() . '?tip_jar_wp_set_tjwp_scsr',
				'tip_jar_wp_set_tjwp_scsr_nonce' => wp_create_nonce( 'tip_jar_wp_set_tjwp_scsr' ),
				'strings'                        => array(
					'title'                => __( 'Let\'s get your Stripe Webhooks set up (Live Mode).', 'tip-jar-wp' ),
					'description'          => __( 'Stripe webhooks are the way Stripe "talks" to the Tip Jar WP code on your website. There are 3 steps to take here.', 'tip-jar-wp' ),
					'ready_to_get_started' => __( 'Ready to get started?', 'tip-jar-wp' ),
				),
				'steps'                          => array(
					'step1' => array(
						'title'       => __( '1. Copy the "Endpoint URL"', 'tip-jar-wp' ),
						'description' => __( 'Copy this url:', 'tip-jar-wp' ),
						'url_to_copy' => str_replace( 'https://', '', get_bloginfo( 'url' ) . '?tip_jar_wp_stripe_webhook' ), // Stripe pre-fills the https so we remove it. All user tests showed people ended up with double "https://".
					),
					'step2' => array(
						'title'                      => __( '2. Create Stripe Webhook', 'tip-jar-wp' ),
						'description'                => array(
							'line_1' => __( 'A) Click the button below to log into your Stripe Account.', 'tip-jar-wp' ),
							'line_2' => __( 'B) Then click "Add Endpoint" and paste the Endpoint URL.', 'tip-jar-wp' ),
							'line_3' => __( 'C) Under "Events to send", click on "Receive all events".', 'tip-jar-wp' ),
							'line_4' => __( 'PLEASE NOTE: this link is not in the dropdown menu, but underneath it.', 'tip-jar-wp' ),
						),
						'stripe_connect_button_text' => __( 'Go to my Stripe Account in a new tab', 'tip-jar-wp' ),
						'stripe_connect_url'         => 'https://dashboard.stripe.com/account/webhooks',
					),
					'step3' => array(
						'title'       => __( '3. Copy the "Signing secret" and paste here', 'tip-jar-wp' ),
						'description' => __( 'Under the section called "Signing secret", copy the secret text and paste it here', 'tip-jar-wp' ),
						'input_field' => array(
							'id'                      => 'stripe_webhook_signing_secret_live_mode',
							'react_component'         => 'MP_WP_Admin_Input_Field',
							'type'                    => 'text',
							'saved_value'             => tip_jar_wp_get_saved_setting( $saved_settings, 'stripe_webhook_signing_secret_live_mode' ),
							'default_value'           => '',
							'client_validation_callback_function' => 'tip_jar_wp_validate_simple_input',
							'server_validation_callback_function' => 'tip_jar_wp_validate_live_stripe_webhook',
							'server_api_endpoint_url' => admin_url() . '?tip_jar_wp_save_setting',
							'nonce'                   => wp_create_nonce( 'stripe_webhook_signing_secret_live_mode' ),
							'instruction_codes'       => array(
								'empty_initial'          => array(
									'instruction_type'    => 'normal',
									'instruction_message' => __( 'Paste the signing secret here.', 'tip-jar-wp' ),
								),
								'empty_not_initial'      => array(
									'instruction_type'    => 'normal',
									'instruction_message' => __( 'Paste the signing secret here.', 'tip-jar-wp' ),
								),
								'error'                  => array(
									'instruction_type'    => 'error',
									'instruction_message' => __( 'Paste the signing secret here.', 'tip-jar-wp' ),
								),
								'invalid_length'         => array(
									'instruction_type'    => 'error',
									'instruction_message' => __( 'Double check the length of the key.', 'tip-jar-wp' ),
								),
								'does_not_contain_whsec' => array(
									'instruction_type'    => 'error',
									'instruction_message' => __( 'Make sure they key you copied begins with "whsec_".', 'tip-jar-wp' ),
								),
								'success'                => array(
									'instruction_type'    => 'success',
									'instruction_message' => __( 'Paste the signing secret here.', 'tip-jar-wp' ),
								),
							),
						),
					),
				),
			),
		);
		// If this is a localhost.
	} else {
		$unhealthy_array = array(
			'mode'               => 'localhost',
			'instruction'        => __( 'Live Stripe Webhooks: It looks like you are on a localhost. Stripe will not be able to send webhooks to your website. Webhooks are very important and will only work on a live site.', 'tip-jar-wp' ),
			'fix_it_button_text' => __( 'Read more', 'tip-jar-wp' ),
			'component_data'     => array(
				'strings' => array(
					'title'       => __( 'Stripe is not able to send webhooks to local websites.', 'tip-jar-wp' ),
					'description' => __( 'Stripe webhooks are the way Stripe "talks" to the Tip Jar WP code on your website. But Stripe can\'t send a webhook to a website that it can\'t reach. Because this is a locally hosted website that is not online, Stripe will not be able to communicate back to your website. However, on a live site, this works without any issues.', 'tip-jar-wp' ),
				),
			),
		);
	}

	$health_checks['stripe_live_webhook_signature'] = array(
		'priority'        => 300,
		'is_healthy'      => $is_healthy,
		'is_health_check' => true,
		'is_wizard_step'  => TIP_JAR_WP_WIZARD_TEST_MODE ? false : true, // The constant allows the wizard to onboard you in Stripe Test mode if true.
		'react_component' => 'Tip_Jar_WP_Stripe_Webhook_Health_Check',
		'icon'            => TIP_JAR_WP_PLUGIN_URL . '/assets/images/svg/webhook.svg',
		'unhealthy'       => $unhealthy_array,
		'healthy'         => array(
			'instruction'    => __( 'Your Stripe Live-Mode webhooks are all set up. Great work!', 'tip-jar-wp' ),
			'component_data' => array(
				'strings' => array(
					'title'                        => __( 'Stripe webhook successfully connected (Live Mode).', 'tip-jar-wp' ),
					'description'                  => __( 'Stripe can now "talk" back to your website. This is important for things like recurring payments, and other important things that happen over at Stripe.', 'tip-jar-wp' ),
					'next_wizard_step_button_text' => __( 'Next step', 'tip-jar-wp' ),
				),
			),
		),
	);

	return $health_checks;

}
add_filter( 'tip_jar_wp_health_checks_and_wizard_vars', 'tip_jar_wp_heath_check_stripe_live_webhook_signature' );
