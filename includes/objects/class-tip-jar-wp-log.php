<?php
/**
 * Log Object
 *
 * @package     Tip Jar WP
 * @subpackage  Classes/Log
 * @copyright   Copyright (c) 2018, Tip Jar WP
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tip_Jar_WP_Log Class
 *
 * @since 1.0.0
 */
class Tip_Jar_WP_Log {

	/**
	 * The Unique Log Number
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $id = 0;

	/**
	 * The log's creation date
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $date_created;

	/**
	 * The data in this log.
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	public $log_data;

	/**
	 * The Database Abstraction
	 *
	 * @since  1.0.0
	 * @var object
	 */
	protected $db;

	/**
	 * Get things going
	 *
	 * @since 1.0.0
	 * @param int    $id The value with which to get the log object.
	 * @param string $column_slug The column from which to check for the value.
	 */
	public function __construct( $id = false, $column_slug = 'id' ) {

		$this->db = tip_jar_wp()->logs_db;

		if ( false === $id || ( is_numeric( $id ) && absint( $id ) !== (int) $id ) ) {
			return false;
		}

		$log = $this->db->get_log( $id, $column_slug );

		if ( empty( $log ) || ! is_object( $log ) ) {
			return false;
		}

		$this->setup_log( $log );

	}

	/**
	 * Given the log data, let's set the variables
	 *
	 * @since  1.0.0
	 * @param  object $log The Log Object.
	 * @return bool                If the setup was successful or not
	 */
	private function setup_log( $log ) {

		if ( ! is_object( $log ) ) {
			return false;
		}

		foreach ( $log as $key => $value ) {

			switch ( $key ) {

				default:
					$this->$key = $value;
					break;

			}
		}

		// Log ID is the only thing that is necessary, make sure it exists.
		if ( ! empty( $this->id ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0.0
	 * @param mixed $key The value to retreive about this log.
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			// translators: The key that could not be retrieved about an log.
			return new WP_Error( 'tip-jar-wp-log-invalid-property', sprintf( __( 'Can\'t get property %s', 'tip-jar-wp' ), $key ) );

		}

	}

	/**
	 * Creates a log
	 *
	 * @since  1.0.0
	 * @param  array $data Array of attributes for a log.
	 * @return mixed False if not a valid creation, Log ID if user is found or valid creation
	 */
	public function create( $data = array() ) {

		if ( 0 !== $this->id || empty( $data ) ) {
			return false;
		}

		$defaults = array(
			'log_data' => '',
		);

		$args = wp_parse_args( $data, $defaults );
		$args = $this->sanitize_columns( $args );

		$created = false;

		$newly_added_id = $this->db->add( $args );

		// The DB class 'add' implies an update if the log being asked to be created already exists.
		if ( $newly_added_id ) {

			// We've successfully added/updated the log, reset the class vars with the new data.
			$log = $this->db->get_log( $newly_added_id );

			// Setup the log data with the values from DB.
			$this->setup_log( $log );

			$created = $this->id;
		}

		return $created;

	}

	/**
	 * Update a log record
	 *
	 * @since  1.0.0
	 * @param  array $data Array of data attributes for a log (checked via whitelist).
	 * @return bool         If the update was successful or not
	 */
	public function update( $data = array() ) {

		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->sanitize_columns( $data );

		$updated = false;

		if ( $this->db->update( $this->id, $data ) ) {

			$log = $this->db->get_log( $this->id );
			$this->setup_log( $log );

			$updated = true;

			$response = array(
				'success' => true,
				'code'    => 'log_updated',
				'log'    => $this,
			);

		} else {

			$response = array(
				'success' => false,
				'code'    => 'log_not_updated',
				'log'    => $this,
			);

		}

		return $response;
	}

	/**
	 * Sanitize the data for update/create
	 *
	 * @since  1.0.0
	 * @param  array $data The data to sanitize.
	 * @return array       The sanitized data, based off column defaults
	 */
	private function sanitize_columns( $data ) {

		$columns        = $this->db->get_columns();
		$default_values = $this->db->get_column_defaults();

		foreach ( $columns as $key => $type ) {

			// Only sanitize data that we were provided.
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch ( $type ) {

				case '%s':
					$data[ $key ] = sanitize_text_field( $data[ $key ] );

					break;

				case '%d':
					if ( ! is_numeric( $data[ $key ] ) || absint( $data[ $key ] !== (int) $data[ $key ] ) ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = absint( $data[ $key ] );
					}
					break;

				case '%f':
					// Convert what was given to a float.
					$value = floatval( $data[ $key ] );

					if ( ! is_float( $value ) ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = $value;
					}
					break;

				default:
					$data[ $key ] = sanitize_text_field( $data[ $key ] );
					break;

			}
		}

		return $data;
	}

}
