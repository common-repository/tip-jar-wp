<?php
/*
Plugin Name: Tip Jar WP
Plugin URI: https://tipjarwp.com
Description: Accept single or recurring tips on your WordPress site in seconds through Apple Pay, Google Pay, Credit Card, and saved-in-browser credit cards.
Version: 2.1.0
Author: Tip Jar WP
Text Domain: tip-jar-wp
Domain Path: languages
License: GPLv3
*/

/*
Copyright 2019  Tip Jar WP  (email : support@tipjarwp.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup plugin constants.
 *
 * @access private
 * @since 1.0
 * @return void
 */
function tip_jar_wp_setup_constants() {

	// Plugin version.
	if ( ! defined( 'TIP_JAR_WP_VERSION' ) ) {

		$tip_jar_wp_version = '2.1.0';

		// If SCRIPT_DEBUG is enabled, break the browser cache.
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			define( 'TIP_JAR_WP_VERSION', $tip_jar_wp_version . time() );
		} else {
			define( 'TIP_JAR_WP_VERSION', $tip_jar_wp_version );
		}
	}

	// Plugin Folder Path.
	if ( ! defined( 'TIP_JAR_WP_PLUGIN_DIR' ) ) {
		define( 'TIP_JAR_WP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

	// Plugin Folder URL.
	if ( ! defined( 'TIP_JAR_WP_PLUGIN_URL' ) ) {
		define( 'TIP_JAR_WP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	// Plugin Root File.
	if ( ! defined( 'TIP_JAR_WP_PLUGIN_FILE' ) ) {
		define( 'TIP_JAR_WP_PLUGIN_FILE', __FILE__ );
	}

	// The default mode for the onboarding wizard.
	if ( ! defined( 'TIP_JAR_WP_WIZARD_TEST_MODE' ) ) {
		define( 'TIP_JAR_WP_WIZARD_TEST_MODE', false );
	}

}
tip_jar_wp_setup_constants();

/**
 * Installation functions
 */
require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/install.php';

if ( ! class_exists( 'Tip_Jar_WP' ) ) {

	/**
	 * Main Tip_Jar_WP Class.
	 *
	 * @since 1.0
	 */
	final class Tip_Jar_WP {

		/**
		 * The instance of Tip_Jar_WP
		 *
		 * @var Tip_Jar_WP
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Tip Jar WPs Transactions DB Object
		 *
		 * @var object|Tip_Jar_WP_Transactions_DB
		 * @since 1.0
		 */
		public $transactions_db;

		/**
		 * Tip Jar WPs Arrangements DB Object
		 *
		 * @var object|Tip_Jar_WP_Arrangements_DB
		 * @since 1.0
		 */
		public $arrangements_db;

		/**
		 * Tip Jar WPs Download Logs DB Object
		 *
		 * @var object|Tip_Jar_WP_Download_Logs_DB
		 * @since 1.0
		 */
		public $download_logs_db;

		/**
		 * Tip Jar WPs Forms DB Object
		 *
		 * @var object|Tip_Jar_WP_Forms_DB
		 * @since 1.0
		 */
		public $forms_db;

		/**
		 * Tip Jar WPs Notes DB Object
		 *
		 * @var object|Tip_Jar_WP_Forms_DB
		 * @since 1.0.1.3
		 */
		public $notes_db;

		/**
		 * Tip Jar WPs Logs DB Object
		 *
		 * @var object|Tip_Jar_WP_Logs_DB
		 * @since 1.0
		 */
		public $logs_db;

		/**
		 * Main Tip_Jar_WP Instance.
		 *
		 * @since 1.0
		 * @static
		 * @static var array $instance
		 * @uses Tip_Jar_WP::includes() Include the required files.
		 * @uses Tip_Jar_WP::load_textdomain() load the language files.
		 * @see Tip_Jar_WP()
		 * @return object|Tip_Jar_WP The Tip_Jar_WP singleton
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Tip_Jar_WP ) ) {
				self::$instance = new Tip_Jar_WP();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->transactions_db  = new Tip_Jar_WP_Transactions_DB();
				self::$instance->arrangements_db  = new Tip_Jar_WP_Arrangements_DB();
				self::$instance->download_logs_db = new Tip_Jar_WP_Download_Logs_DB();
				self::$instance->notes_db         = new Tip_Jar_WP_Notes_DB();
				self::$instance->forms_db         = new Tip_Jar_WP_Forms_DB();
				self::$instance->logs_db          = new Tip_Jar_WP_Logs_DB();

				// Create the databases.
				tip_jar_wp()->transactions_db->create_table();
				tip_jar_wp()->arrangements_db->create_table();
				tip_jar_wp()->download_logs_db->create_table();
				tip_jar_wp()->notes_db->create_table();
				tip_jar_wp()->forms_db->create_table();
				tip_jar_wp()->logs_db->create_table();
			}

			return self::$instance;
		}

		/**
		 * Throw error on object clone as this only needs to be instatiated once.
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			wp_die( esc_textarea( __( 'This class can only be instantiated once.', 'tip-jar-wp' ) ) );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			wp_die( esc_textarea( __( 'This class can only be instantiated once.', 'tip-jar-wp' ) ) );
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function includes() {

			/**
			 * Base Database
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/database-functions/class-tip-jar-wp-db.php';

			/**
			 * Transactions Database and Object
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/database-functions/class-tip-jar-wp-general-query.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/database-functions/class-tip-jar-wp-transactions-db.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/objects/class-tip-jar-wp-transaction.php';

			/**
			 * Arrangements Database and Object
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/database-functions/class-tip-jar-wp-arrangements-db.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/objects/class-tip-jar-wp-arrangement.php';

			/**
			 * Download Logs Database and Object
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/database-functions/class-tip-jar-wp-download-logs-db.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/objects/class-tip-jar-wp-download-log.php';

			/**
			 * Forms Database and Object
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/database-functions/class-tip-jar-wp-forms-db.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/objects/class-tip-jar-wp-form.php';

			/**
			 * Notes Database and Object
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/database-functions/class-tip-jar-wp-notes-db.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/objects/class-tip-jar-wp-note.php';

			/**
			 * Logs Database and Object
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/database-functions/class-tip-jar-wp-logs-db.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/objects/class-tip-jar-wp-log.php';

			/**
			 * Misc Functions
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/misc-functions.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/session-handler.php';

			/**
			 * Email Functions
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/emails/emails.php';

			/**
			 * File Download/Verification Functions
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/file-delivery/file-delivery.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/file-delivery/file-download-transient.php';

			/**
			 * Admin Stuff
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/admin/php/admin-setup.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/admin/php/admin-queries.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/admin/php/endpoints.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/admin/php/health-checks-and-wizard/setup.php';

			/**
			 * Stripe Stuff
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/stripe/stripe-connect.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/stripe/stripe-functions.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/stripe/stripe-webhooks/stripe-webhooks.php';

			// Stripe API Classes.
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/stripe/stripe-classes/class-tip-jar-wp-stripe.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/stripe/stripe-classes/class-tip-jar-wp-stripe-get.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/stripe/stripe-classes/class-tip-jar-wp-stripe-delete.php';

			/**
			 * Frontend Stuff
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/enqueue-scripts.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/misc-functions.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/frontend-queries.php';
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/frontend/php/endpoints/endpoints.php';

			/**
			 * Functions for generating form output.
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/output-form-functions.php';

			/**
			 * Shortcodes
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/shortcodes.php';

			/**
			 * Validation Functions
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/validation-functions.php';

			/**
			 * Image resizer
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/misc-functions/resizer.php';

			/**
			 * Gutenberg
			 */
			require TIP_JAR_WP_PLUGIN_DIR . 'includes/admin/php/gutenberg/blocks/tip-form/tip-form-block.php';

		}

		/**
		 * Loads the plugin language files.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function load_textdomain() {

			// Load the included language files.
			load_plugin_textdomain( 'tip-jar-wp', false, basename( dirname( __FILE__ ) ) . '/languages/' );

			// Load any custom language files from /wp-content/languages/tip-jar-wp for the current locale.
			$locale = apply_filters( 'plugin_locale', get_locale(), 'tip-jar-wp' );

			$mofile = sprintf( '%1$s-%2$s.mo', 'tip-jar-wp', $locale );
			load_textdomain( 'tip-jar-wp', WP_LANG_DIR . '/tip-jar-wp/' . $mofile );

		}

	}

}

/**
 * Function which returns the Tip Jar WP Singleton
 *
 * @since 1.0
 * @return Tip_Jar_WP
 */
function tip_jar_wp() {
	return Tip_Jar_WP::instance();
}

/**
 * Start Tip_Jar_WP.
 *
 * @since 1.0
 * @return Tip_Jar_WP
 */
function tip_jar_wp_initialize() {
	return tip_jar_wp();
}
add_action( 'plugins_loaded', 'tip_jar_wp_initialize' );
