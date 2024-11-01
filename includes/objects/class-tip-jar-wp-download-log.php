<?php
/**
 * Download Log Object
 *
 * @package     Tip Jar WP
 * @subpackage  Classes/Transaction
 * @copyright   Copyright (c) 2018, Tip Jar WP
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tip_Jar_WP_Download_Log Class
 *
 * @since 1.0.0
 */
class Tip_Jar_WP_Download_Log {

	/**
	 * The download_log Number (used for invoice number)
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $id = 0;

	/**
	 * The user ID associated with the download_log
	 *
	 * @since  1.0.0
	 * @var int
	 */
	public $user_id;

	/**
	 * The download_log's creation date
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $date;

	/**
	 * The form_id related to this download_log.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $form_id;

	/**
	 * The transaction_id related to this download_log (if any).
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $transaction_id;

	/**
	 * The attachment_id which was downloaded.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $attachment_id;

	/**
	 * The page url where the download originated.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $page_url;

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
	 * @param int    $id The value with which to get the download_log object.
	 * @param string $column_slug The column from which to check for the value.
	 */
	public function __construct( $id = false, $column_slug = 'id' ) {

		$this->db = tip_jar_wp()->download_logs_db;

		if ( false === $id || ( is_numeric( $id ) && absint( $id ) !== (int) $id ) ) {
			return false;
		}

		$download_log = $this->db->get_download_log( $id, $column_slug );

		if ( empty( $download_log ) || ! is_object( $download_log ) ) {
			return false;
		}

		$this->setup_download_log( $download_log );

	}

	/**
	 * Given the download_log data, let's set the variables
	 *
	 * @since  1.0.0
	 * @param  object $download_log The Transaction Object.
	 * @return bool                If the setup was successful or not
	 */
	private function setup_download_log( $download_log ) {

		if ( ! is_object( $download_log ) ) {
			return false;
		}

		foreach ( $download_log as $key => $value ) {

			switch ( $key ) {

				default:
					$this->$key = $value;
					break;

			}
		}

		// Transaction ID is the only thing that is necessary, make sure it exists.
		if ( ! empty( $this->id ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0.0
	 * @param mixed $key The value to retreive about this download_log.
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			// translators: The key that could not be retrieved about an download_log.
			return new WP_Error( 'tip-jar-wp-download_log-invalid-property', sprintf( __( 'Can\'t get property %s', 'tip-jar-wp' ), $key ) );

		}

	}

	/**
	 * Creates a download_log
	 *
	 * @since  1.0.0
	 * @param  array $data Array of attributes for a download_log.
	 * @return mixed False if not a valid creation, Transaction ID if user is found or valid creation
	 */
	public function create( $data = array() ) {

		if ( 0 !== $this->id || empty( $data ) ) {
			return false;
		}

		$defaults = array(
			'id'             => 0,
			'user_id'        => 0,
			'form_id'        => 0,
			'transaction_id' => 0,
			'attachment_id'  => 0,
			'page_url'       => '',
		);

		$args = wp_parse_args( $data, $defaults );
		$args = $this->sanitize_columns( $args );

		$created = false;

		$newly_added_id = $this->db->add( $args );

		// The DB class 'add' implies an update if the arrangement being asked to be created already exists.
		if ( $newly_added_id ) {

			// We've successfully added/updated the arrangement, reset the class vars with the new data.
			$download_log = $this->db->get_download_log( $newly_added_id );

			// Setup the arrangement data with the values from DB.
			$this->setup_download_log( $download_log );

			$created = $this->id;
		}

		return $created;

	}

	/**
	 * Update a download_log record
	 *
	 * @since  1.0.0
	 * @param  array $data Array of data attributes for a download_log (checked via whitelist).
	 * @return bool         If the update was successful or not
	 */
	public function update( $data = array() ) {

		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->sanitize_columns( $data );

		$updated = false;

		if ( $this->db->update( $this->id, $data ) ) {

			$download_log = $this->db->get_download_log( $this->id );
			$this->setup_download_log( $download_log );

			$updated = true;

			$response = array(
				'success'      => true,
				'code'         => 'download_log_updated',
				'download_log' => $download_log,
			);

		} else {

			$response = array(
				'success'         => false,
				'code'            => 'download_log_not_updated',
				'download_log_id' => $this,
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
