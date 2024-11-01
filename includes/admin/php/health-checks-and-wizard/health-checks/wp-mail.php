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
 * Do a health check to see if wp_mail is sending or not
 *
 * @param    array $health_checks All the health checks.
 * @return   array $health_checks
 */
function tip_jar_wp_heath_check_wp_mail( $health_checks ) {

	// Check if wp_mail has been confirmed as working or not.
	$settings = get_option( 'tip_jar_wp_settings' );

	// If wp_mail has been not been confirmed yet.
	if ( ! isset( $settings['wp_mail_confirmed'] ) || ! $settings['wp_mail_confirmed'] ) {
		$is_healthy = false;
	} else {
		$is_healthy = true;
	}

	// Check if there is an outstanding email token.
	$saved_email_token = get_transient( 'tip_jar_wp_mail_health_check_token' );
	if ( ! empty( $saved_email_token ) ) {
		$force_to_step = 'test_email_successfuly_sent';
	} else {
		$force_to_step = false;
	}

	$main_title = __( 'Let\'s check if your website can send emails.', 'tip-jar-wp' );

	$health_checks['wp_mail'] = array(
		'priority'        => 400,
		'is_healthy'      => $is_healthy,
		'is_health_check' => true,
		'is_wizard_step'  => true,
		'react_component' => 'Tip_Jar_WP_WP_Mail_Health_Check',
		'icon'            => TIP_JAR_WP_PLUGIN_URL . '/assets/images/svg/lock.svg',
		'unhealthy'       => array(
			'instruction'        => __( 'Your emails may not be sending, which includes transaction receipts!', 'tip-jar-wp' ),
			'fix_it_button_text' => __( 'Let\'s fix it!', 'tip-jar-wp' ),
			'component_data'     => array(
				'force_to_step' => $force_to_step,
				'strings'       => array(
					'title'       => $main_title,
					'description' => __( 'It is important that your website can send emails properly. If you are on a hosting server that has been blacklisted by spam catchers (which is unfortunately common with most webhosts), your emails won\'t get through to your users! That includes transaction receipts from Tip Jar WP. But the good news is that there are ways to fix it.', 'tip-jar-wp' ),
				),
				'steps'         => array(
					'step1'                         => array(
						'title'                     => __( '1. Test an email', 'tip-jar-wp' ),
						'restart_title'             => __( 'Great! Let\'s try the test email again', 'tip-jar-wp' ),
						'custom_email_plugin_title' => __( 'Once you have installed and configured your email plugin, come back here and try the test email again.', 'tip-jar-wp' ),
						'description'               => __( 'Enter your email:', 'tip-jar-wp' ),
						'email_address_to_send'     => get_bloginfo( 'admin_email' ),
						'send_test_email'           => __( 'Send test email', 'tip-jar-wp' ),
						'sending_test_email'        => __( 'Sending test email...', 'tip-jar-wp' ),
						'email_sent'                => __( 'Test email sent.', 'tip-jar-wp' ),
						'unable_to_attempt_email'   => __( 'Unable to attempt email test. Please try again.', 'tip-jar-wp' ),
						'attempted_but_server_response_incorrect' => __( 'Something is wrong on your server. Try deactivating 3rd party plugins then try again.', 'tip-jar-wp' ),
						'attempted_and_failed'      => __( 'Unable to send! You will need to configure an email sending plugin to fix this.', 'tip-jar-wp' ),
						'server_api_endpoint_sent_test_email' => admin_url() . '?tip_jar_wp_send_test_email',
						'send_test_email_nonce'     => wp_create_nonce( 'tip_jar_wp_send_test_email' ),
					),
					'test_email_successfuly_sent'   => array(
						'title'                            => __( '2. Check your email', 'tip-jar-wp' ),
						'description'                      => __( 'In a new tab, check your email and click the link in the email.', 'tip-jar-wp' ),
						'did_not_get_email_button_text'    => __( 'Didn\'t get the email?', 'tip-jar-wp' ),
						'server_api_endpoint_reset_wp_mail_health_check' => admin_url() . '?tip_jar_wp_reset_wp_mail_health_check',
						'reset_wp_mail_health_check_nonce' => wp_create_nonce( 'tip_jar_wp_reset_wp_mail_health_check' ),
					),
					'attempted_and_failed'          => array(
						'title'                      => __( 'Hmm. It looks like your emails are not getting through. Let\'s fix it!', 'tip-jar-wp' ),
						'description'                => __( 'In order to fix this, you will need to install an email-sending plugin, which routes your emails through a solid email sender. There are a few good options out there, including AmazonSES, Mandrill, and SendGrid. We recommend using SendGrid, which is free for most small websites. Follow the steps below to get started with SendGrid.', 'tip-jar-wp' ),
						'install_sendgrid_text'      => __( 'Install the SendGrid WordPress Plugin for me', 'tip-jar-wp' ),
						'use_my_own'                 => __( 'I\'ll manually install a different email plugin', 'tip-jar-wp' ),
						'server_api_endpoint_install_sendgrid' => admin_url() . '?tip_jar_wp_install_sendgrid',
						'install_sendgrid_nonce'     => wp_create_nonce( 'install_sendgrid_nonce' ),
						'sendgrid_install_succeeded' => __( 'SendGrid successfully installed.', 'tip-jar-wp' ),
						'sendgrid_install_failed'    => __( 'Unable to automatically install SendGrid.', 'tip-jar-wp' ),
					),
					'create_sendgrid_account'       => array(
						'title'        => __( '1. Create a free SendGrid Account', 'tip-jar-wp' ),
						'description'  => __( 'In order to let SendGrid know who you are, you\'ll need to register a free account with them.', 'tip-jar-wp' ),
						'register_with_sendgrid_button_text' => __( 'Create a SendGrid Account', 'tip-jar-wp' ),
						'sendgrid_url' => 'https://signup.sendgrid.com/',
					),
					'already_have_sendgrid_account' => array(
						'title'                         => __( '2. Already have a SendGrid account?', 'tip-jar-wp' ),
						'description'                   => __( 'Great! Log in and create an API Key with "Full Access", and paste it below.', 'tip-jar-wp' ),
						'grab_your_api_key_button_text' => __( 'Log in and create API Key', 'tip-jar-wp' ),
						'grab_api_key_url'              => 'https://app.sendgrid.com/settings/api_keys',
					),
					'enter_sendgrid_api_key'        => array(
						'title'                       => __( '3. Enter your SendGrid API Key', 'tip-jar-wp' ),
						'paste_sendgrid_api_key_text' => __( 'Copy the API key you just created from your SendGrid account, and paste it below.', 'tip-jar-wp' ),
						'input_field'                 => array(
							'id'                      => 'tip_jar_wp_save_sendgrid_api_key',
							'react_component'         => 'MP_WP_Admin_Input_Field',
							'type'                    => 'text',
							'saved_value'             => get_option( 'sendgrid_api_key' ),
							'default_value'           => '',
							'client_validation_callback_function' => 'tip_jar_wp_validate_simple_input',
							'server_validation_callback_function' => 'tip_jar_wp_validate_sendgrid_api_key',
							'server_api_endpoint_url' => admin_url() . '?tip_jar_wp_save_sendgrid_api_key',
							'nonce'                   => wp_create_nonce( 'tip_jar_wp_save_sendgrid_api_key' ),
							'instruction_codes'       => array(
								'empty_initial'     => array(
									'instruction_type'    => 'normal',
									'instruction_message' => __( 'Paste your SendGrid API Key here.', 'tip-jar-wp' ),
								),
								'empty_not_initial' => array(
									'instruction_type'    => 'normal',
									'instruction_message' => __( 'Paste your SendGrid API Key here.', 'tip-jar-wp' ),
								),
								'error'             => array(
									'instruction_type'    => 'error',
									'instruction_message' => __( 'Paste your SendGrid API Key here.', 'tip-jar-wp' ),
								),
								'success'           => array(
									'instruction_type'    => 'success',
									'instruction_message' => __( 'Paste your SendGrid API Key here.', 'tip-jar-wp' ),
								),
							),
							'help_text'               => array(
								'title' => __( 'SendGrid API Key', 'tip-jar-wp' ),
								'body'  => __( 'Copy and Paste your SendGrid API Key here.', 'tip-jar-wp' ),
							),
						),
					),
				),
			),
		),
		'healthy'         => array(
			'instruction'              => __( 'You confirmed a WordPress email made it to you. If you find your users are suddenly not getting emails, run this check again.', 'tip-jar-wp' ),
			'fix_it_again_button_text' => __( 'Test another email', 'tip-jar-wp' ),
			'component_data'           => array(
				'strings' => array(
					'title'                        => __( 'You confirmed a WordPress email made it to you. Great Job!', 'tip-jar-wp' ),
					'description'                  => __( 'Excellent. This is a super important part of making sure your website is fully operational, and that your users get their transaction receipts.', 'tip-jar-wp' ),
					'next_wizard_step_button_text' => __( 'Next step', 'tip-jar-wp' ),
				),
			),
		),
	);

	return $health_checks;

}
add_filter( 'tip_jar_wp_health_checks_and_wizard_vars', 'tip_jar_wp_heath_check_wp_mail' );
