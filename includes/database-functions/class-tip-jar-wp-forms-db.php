<?php
/**
 * Tips DB class
 *
 * This class is for interacting with the forms database table
 *
 * @package     Tip Jar WP
 * @subpackage  Classes/DB Tips
 * @copyright   Copyright (c) 2018, Tip Jar WP
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tip_Jar_WP_Forms_DB Class
 *
 * @since 1.0.0
 */
class Tip_Jar_WP_Forms_DB extends Tip_Jar_WP_DB {

	/**
	 * The metadata type.
	 *
	 * @since  1.0.0
	 * @var string
	 */
	public $meta_type = 'form';

	/**
	 * The name of the date column.
	 *
	 * @since  1.0
	 * @var string
	 */
	public $date_key = 'date_created';

	/**
	 * The name of the cache group.
	 *
	 * @since  1.0.0
	 * @var string
	 */
	public $cache_group = 'tip-jar-wp-forms';

	/**
	 * Get things started
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'tip_jar_wp_forms';
		$this->primary_key = 'id';
		$this->version     = '1.0';

	}

	/**
	 * Get columns and formats.
	 *
	 * @since   1.0.0
	 */
	public function get_columns() {
		return array(
			'id'           => '%d',
			'date_created' => '%s',
			'json'         => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @since   1.0.0
	 */
	public function get_column_defaults() {
		return array(
			'date_created' => gmdate( 'Y-m-d H:i:s' ),
			'json'         => '',
		);
	}

	/**
	 * Add a form
	 *
	 * @param array $data The data about this form.
	 * @since 1.0.0
	 */
	public function add( $data = array() ) {

		$defaults = $this->get_column_defaults();

		$args = wp_parse_args( $data, $defaults );

		if ( isset( $args['id'] ) ) {
			$form = $this->get_form( $args['id'] );
		} else {
			$form = false;
		}

		if ( $form ) {

			// Update an existing form.
			$this->update( $form->id, $args );

			return $form->id;

		} else {

			return $this->insert( $args, 'form' );

		}

	}

	/**
	 * Insert a new form
	 *
	 * @since   1.0.0
	 * @param   array $data The data about this form.
	 * @return  int
	 */
	public function insert( $data ) {

		// Then insert this new form.
		$result = parent::insert( $data );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Update a form
	 *
	 * @since   1.0.0
	 * @param   int    $row_id The id of the row being updated.
	 * @param   array  $data The data about this form.
	 * @param   string $where A where clause.
	 * @return  bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {
		$result = parent::update( $row_id, $data, $where );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Delete a form
	 *
	 * @since   1.0.0
	 * @param   int $id The id of the form being updated.
	 * @return  bool The result of the udpate
	 */
	public function delete( $id = false ) {

		if ( empty( $id ) ) {
			return false;
		}

		$form = $this->get_form( $id );

		if ( $form->id > 0 ) {

			global $wpdb;

			$result = $wpdb->delete( $this->table_name, array( 'id' => $form->id ), array( '%d' ) );

			if ( $result ) {
				$this->set_last_changed();
			}

			return $result;

		} else {
			return false;
		}

	}

	/**
	 * Checks if a form exists
	 *
	 * @since    1.0.0
	 * @param    string $value The value we are checking for existence.
	 * @param    string $field The field in question.
	 * @return   bool Whether it exists or not.
	 */
	public function exists( $value = '', $field = 'email' ) {

		$columns = $this->get_columns();
		if ( ! array_key_exists( $field, $columns ) ) {
			return false;
		}

		return (bool) $this->get_column_by( 'id', $field, $value );

	}

	/**
	 * Retrieves a single form from the database
	 *
	 * @since  1.0.0
	 * @param  mixed  $id  The form ID., or any value we are looking for.
	 * @param  string $column_slug Any column in the forms table.
	 * @return mixed  Upon success, an object of the transaction. Upon failure, NULL
	 */
	public function get_form( $id = 0, $column_slug = 'id' ) {

		if ( empty( $id ) ) {
			return false;
		}

		if ( ! $id ) {
			return false;
		}

		$args                           = array( 'number' => 1 );
		$args['column_values_included'] = array(
			$column_slug => $id,
		);

		$query = new Tip_Jar_WP_General_Query( '', $this );

		$results = $query->query( $args );

		if ( empty( $results ) ) {
			return false;
		}

		return array_shift( $results );
	}

	/**
	 * Retrieve forms from the database
	 *
	 * @since   1.0.0
	 * @param   array $args The arguments being used to filter a request for forms.
	 * @return  mixed $id  The form ID., or any value we are looking for.
	 */
	public function get_forms( $args = array() ) {
		$args['count'] = false;

		$query = new Tip_Jar_WP_General_Query( '', $this );

		return $query->query( $args );
	}


	/**
	 * Count the total number of forms in the database
	 *
	 * @since   1.0.0
	 * @param   array $args The arguments being used to filter a request for forms.
	 * @return  mixed The number of results.
	 */
	public function count( $args = array() ) {

		$args['count']  = true;
		$args['offset'] = 0;

		$query   = new Tip_Jar_WP_General_Query( '', $this );
		$results = $query->query( $args );

		return $results;
	}

	/**
	 * Sets the last_changed cache key for forms.
	 *
	 * @since  1.0
	 * @return void
	 */
	public function set_last_changed() {
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
	}

	/**
	 * Retrieves the value of the last_changed cache key for forms.
	 *
	 * @since  1.0
	 * @return string When it was was changed.
	 */
	public function get_last_changed() {
		if ( function_exists( 'wp_cache_get_last_changed' ) ) {
			return wp_cache_get_last_changed( $this->cache_group );
		}

		$last_changed = wp_cache_get( 'last_changed', $this->cache_group );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, $this->cache_group );
		}

		return $last_changed;
	}

	/**
	 * Create the table
	 *
	 * @since   1.0.0
	 */
	public function create_table() {

		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = 'CREATE TABLE ' . $this->table_name . ' (
		id int(20) unsigned NOT NULL AUTO_INCREMENT,
		date_created datetime NOT NULL,
		json longtext NOT NULL,
		PRIMARY KEY  (id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;';

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

}
