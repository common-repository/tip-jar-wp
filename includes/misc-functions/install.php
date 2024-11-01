<?php
/**
 * Install Function
 *
 * @package     Tip Jar WP
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2018, Tip Jar WP
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Install
 *
 * Runs on plugin install
 *
 * @since 1.0
 * @global $wpdb
 * @param  bool $network_wide If the plugin is being network-activated.
 * @return void
 */
function tip_jar_wp_install( $network_wide = false ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) { // phpcs:ignore

			switch_to_blog( $blog_id );
			tip_jar_wp_run_install();
			restore_current_blog();

		}
	} else {

		tip_jar_wp_run_install();

	}

	update_option( 'tip_jar_wp_just_activated', true );

}
register_activation_hook( TIP_JAR_WP_PLUGIN_FILE, 'tip_jar_wp_install' );

/**
 * Run the Tip_Jar_WP Install process
 *
 * @since  1.0.0
 * @return void
 */
function tip_jar_wp_run_install() {

	// Create the databases.
	tip_jar_wp()->transactions_db->create_table();
	tip_jar_wp()->arrangements_db->create_table();
	tip_jar_wp()->download_logs_db->create_table();
	tip_jar_wp()->forms_db->create_table();
	tip_jar_wp()->logs_db->create_table();

	// Create the Apple Pay verification file in the site root.
	tip_jar_wp_create_apple_verification_file();

}

/**
 * When a new Blog is created in multisite, see if Tip Jar WP is network activated, and run the installer
 *
 * @since  1.0.0
 * @param  int    $blog_id The Blog ID created.
 * @param  int    $user_id The User ID set as the admin.
 * @param  string $domain  The URL.
 * @param  string $path    Site Path.
 * @param  int    $site_id The Site ID.
 * @param  array  $meta    Blog Meta.
 * @return void
 */
function tip_jar_wp_new_blog_created( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	if ( is_plugin_active_for_network( plugin_basename( TIP_JAR_WP_PLUGIN_FILE ) ) ) {

		switch_to_blog( $blog_id );
		tip_jar_wp_install();
		restore_current_blog();

	}

}
add_action( 'wpmu_new_blog', 'tip_jar_wp_new_blog_created', 10, 6 );

/**
 * Drop our custom tables when a mu site is deleted
 *
 * @since  1.0.0
 * @param  array $tables  The tables to drop.
 * @param  int   $blog_id The Blog ID being deleted.
 * @return array          The tables to drop
 */
function tip_jar_wp_wpmu_drop_tables( $tables, $blog_id ) {

	switch_to_blog( $blog_id );
	$transactions_db = new Tip_Jar_WP_Transactions_DB();
	if ( $transactions_db->installed() ) {
		$tables[] = $transactions_db->table_name;
	}
	$arrangements_db = new Tip_Jar_WP_Arrangements_DB();
	if ( $arrangements_db->installed() ) {
		$tables[] = $arrangements_db->table_name;
	}
	$download_logs_db = new Tip_Jar_WP_Download_Logs_DB();
	if ( $download_logs_db->installed() ) {
		$tables[] = $download_logs_db->table_name;
	}
	$forms_db = new Tip_Jar_WP_Forms_DB();
	if ( $forms_db->installed() ) {
		$tables[] = $forms_db->table_name;
	}
	$logs_db = new Tip_Jar_WP_Logs_DB();
	if ( $logs_db->installed() ) {
		$tables[] = $logs_db->table_name;
	}
	$notes_db = new Tip_Jar_WP_Notes_DB();
	if ( $notes_db->installed() ) {
		$tables[] = $notes_db->table_name;
	}
	restore_current_blog();

	return $tables;

}
add_filter( 'wpmu_drop_tables', 'tip_jar_wp_wpmu_drop_tables', 10, 2 );

/**
 * Flush the rewrite rules after activation
 *
 * @since  1.0.0
 * @return void
 */
function tip_jar_wp_handle_after_activation_actions() {

	$tip_jar_wp_just_activated = get_option( 'tip_jar_wp_just_activated' );

	if ( ! $tip_jar_wp_just_activated ) {
		return;
	}

	// Delete the just activated flag.
	delete_option( 'tip_jar_wp_just_activated' );

	// Flush the rewrite rules.
	flush_rewrite_rules();

}
add_action( 'shutdown', 'tip_jar_wp_handle_after_activation_actions' );
