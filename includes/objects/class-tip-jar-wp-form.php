<?php
/**
 * Form Object
 *
 * @package     Tip Jar WP
 * @subpackage  Classes/Form
 * @copyright   Copyright (c) 2018, Tip Jar WP
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tip_Jar_WP_Form Class
 *
 * @since 1.0.0
 */
class Tip_Jar_WP_Form {

	/**
	 * The Unique Form Number
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $id = 0;

	/**
	 * The form's creation date
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $date_created;

	/**
	 * The unique json dicatating what is part of this form.
	 *
	 * @since  1.0.0
	 * @var int
	 */
	public $json;

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
	 * @param int    $id The value with which to get the form object.
	 * @param string $column_slug The column from which to check for the value.
	 */
	public function __construct( $id = false, $column_slug = 'id' ) {

		$this->db = tip_jar_wp()->forms_db;

		if ( false === $id || ( is_numeric( $id ) && absint( $id ) !== (int) $id ) ) {
			return false;
		}

		$form = $this->db->get_form( $id, $column_slug );

		if ( empty( $form ) || ! is_object( $form ) ) {
			return false;
		}

		$this->setup_form( $form );

	}

	/**
	 * Given the form data, let's set the variables
	 *
	 * @since  1.0.0
	 * @param  object $form The Form Object.
	 * @return bool                If the setup was successful or not
	 */
	private function setup_form( $form ) {

		if ( ! is_object( $form ) ) {
			return false;
		}

		foreach ( $form as $key => $value ) {

			switch ( $key ) {

				default:
					$this->$key = $value;
					break;

			}
		}

		// Form ID is the only thing that is necessary, make sure it exists.
		if ( ! empty( $this->id ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0.0
	 * @param mixed $key The value to retreive about this form.
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			// translators: The key that could not be retrieved about an form.
			return new WP_Error( 'tip-jar-wp-form-invalid-property', sprintf( __( 'Can\'t get property %s', 'tip-jar-wp' ), $key ) );

		}

	}

	/**
	 * Creates a form
	 *
	 * @since  1.0.0
	 * @param  array $data Array of attributes for a form.
	 * @return mixed False if not a valid creation, Form ID if user is found or valid creation
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

		// The DB class 'add' implies an update if the form being asked to be created already exists.
		if ( $newly_added_id ) {

			// We've successfully added/updated the form, reset the class vars with the new data.
			$form = $this->db->get_form( $newly_added_id );

			// Setup the form data with the values from DB.
			$this->setup_form( $form );

			$created = $this->id;

			// Now that the form has been created, it has an ID. Apply that ID to the json.
			$json_with_id_applied       = json_decode( $args['json'], true );
			$json_with_id_applied['id'] = $newly_added_id;
			$json_with_id_applied       = wp_json_encode( $json_with_id_applied );
			$args['json']               = $json_with_id_applied;
			$this->update( $args );
		}

		return $created;

	}

	/**
	 * Update a form record
	 *
	 * @since  1.0.0
	 * @param  array $data Array of data attributes for a form (checked via whitelist).
	 * @return bool         If the update was successful or not
	 */
	public function update( $data = array() ) {

		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->sanitize_columns( $data );

		$updated = false;

		if ( $this->db->update( $this->id, $data ) ) {

			$form = $this->db->get_form( $this->id );
			$this->setup_form( $form );

			$updated = true;

			$response = array(
				'success' => true,
				'code'    => 'form_updated',
				'form'    => $this,
			);

		} else {

			$response = array(
				'success' => false,
				'code'    => 'form_not_updated',
				'form'    => $this,
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
