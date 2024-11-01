<?php
/**
 * Note Object
 *
 * @package     Tip Jar WP
 * @subpackage  Classes/Note
 * @copyright   Copyright (c) 2018, Tip Jar WP
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tip_Jar_WP_Note Class
 *
 * @since 1.0.1.3
 */
class Tip_Jar_WP_Note {

	/**
	 * The Unique Note Number
	 *
	 * @since 1.0.1.3
	 * @var int
	 */
	public $id = 0;

	/**
	 * The note's creation date
	 *
	 * @since 1.0.1.3
	 * @var string
	 */
	public $date_created;

	/**
	 * The id of the WordPress user who left this note.
	 *
	 * @since  1.0.1.3
	 * @var int
	 */
	public $user_id;

	/**
	 * The id of the transaction to which this note belongs.
	 *
	 * @since  1.0.1.3
	 * @var int
	 */
	public $transaction_id;

	/**
	 * If this note is a reply to another note, this is the id of the note to which this one is a reply.
	 *
	 * @since  1.0.1.3
	 * @var int
	 */
	public $is_reply_to;

	/**
	 * This is the content of the note.
	 *
	 * @since  1.0.1.3
	 * @var int
	 */
	public $note_content;

	/**
	 * The Database Abstraction
	 *
	 * @since  1.0.1.3
	 * @var object
	 */
	protected $db;

	/**
	 * Get things going
	 *
	 * @since 1.0.1.3
	 * @param int    $id The value with which to get the note object.
	 * @param string $column_slug The column from which to check for the value.
	 */
	public function __construct( $id = false, $column_slug = 'id' ) {

		$this->db = tip_jar_wp()->notes_db;

		if ( false === $id || ( is_numeric( $id ) && absint( $id ) !== (int) $id ) ) {
			return false;
		}

		$note = $this->db->get_note( $id, $column_slug );

		if ( empty( $note ) || ! is_object( $note ) ) {
			return false;
		}

		$this->setup_note( $note );

	}

	/**
	 * Given the note data, let's set the variables
	 *
	 * @since  1.0.1.3
	 * @param  object $note The Note Object.
	 * @return bool                If the setup was successful or not
	 */
	private function setup_note( $note ) {

		if ( ! is_object( $note ) ) {
			return false;
		}

		foreach ( $note as $key => $value ) {

			switch ( $key ) {

				default:
					$this->$key = $value;
					break;

			}
		}

		// Note ID is the only thing that is necessary, make sure it exists.
		if ( ! empty( $this->id ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0.1.3
	 * @param mixed $key The value to retreive about this note.
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			// translators: The key that could not be retrieved about an note.
			return new WP_Error( 'tip-jar-wp-note-invalid-property', sprintf( __( 'Can\'t get property %s', 'tip-jar-wp' ), $key ) );

		}

	}

	/**
	 * Creates a note
	 *
	 * @since  1.0.1.3
	 * @param  array $data Array of attributes for a note.
	 * @return mixed False if not a valid creation, Note ID if user is found or valid creation
	 */
	public function create( $data = array() ) {

		if ( 0 !== $this->id || empty( $data ) ) {
			return false;
		}

		$defaults = array(
			'json' => '',
		);

		$args = wp_parse_args( $data, $defaults );
		$args = $this->sanitize_columns( $args );

		$created = false;

		$newly_added_id = $this->db->add( $args );

		// The DB class 'add' implies an update if the note being asked to be created already exists.
		if ( $newly_added_id ) {

			// We've successfully added/updated the note, reset the class vars with the new data.
			$note = $this->db->get_note( $newly_added_id );

			// Setup the note data with the values from DB.
			$this->setup_note( $note );

			$created = $this->id;
		}

		return $created;

	}

	/**
	 * Update a note record
	 *
	 * @since  1.0.1.3
	 * @param  array $data Array of data attributes for a note (checked via whitelist).
	 * @return bool         If the update was successful or not
	 */
	public function update( $data = array() ) {

		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->sanitize_columns( $data );

		$updated = false;

		if ( $this->db->update( $this->id, $data ) ) {

			$note = $this->db->get_note( $this->id );
			$this->setup_note( $note );

			$updated = true;

			$response = array(
				'success' => true,
				'code'    => 'note_updated',
				'note'    => $this,
			);

		} else {

			$response = array(
				'success' => false,
				'code'    => 'note_not_updated',
				'note'    => $this,
			);

		}

		return $response;
	}

	/**
	 * Sanitize the data for update/create
	 *
	 * @since  1.0.1.3
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
					$data[ $key ] = wp_kses_post( $data[ $key ] );

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
