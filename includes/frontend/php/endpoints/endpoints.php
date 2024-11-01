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

// Endpoints while creating a payment.
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/payment-endpoints/get-payment-intent.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/payment-endpoints/email-transaction-receipt.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/payment-endpoints/save-note-with-tip.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/payment-endpoints/validate-currency.php';

// Manage Payments endpoints.
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/manage-payments-endpoints/check-if-user-logged-in.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/manage-payments-endpoints/login-email.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/manage-payments-endpoints/attempt-user-login.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/manage-payments-endpoints/get-arrangements.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/manage-payments-endpoints/get-arrangement.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/manage-payments-endpoints/get-subscription-payment-method.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/manage-payments-endpoints/update-arrangement.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/manage-payments-endpoints/cancel-arrangement.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/manage-payments-endpoints/get-transactions.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/manage-payments-endpoints/get-transaction.php';

// File Download endpoints.
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/other-endpoints/file-download/free/file-download-url-creation-free.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/other-endpoints/file-download/free/file-verification-free.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/other-endpoints/file-download/transaction/file-download-url-creation-transaction.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/other-endpoints/file-download/transaction/file-verification-transaction.php';

// Oembed Get endpoint.
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/other-endpoints/oembed/get-oembed.php';

// Notes Get endpoint.
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/other-endpoints/notes/get-notes.php';
require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/other-endpoints/notes/add-note.php';
