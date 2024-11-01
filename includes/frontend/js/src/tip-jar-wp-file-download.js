window.Tip_Jar_WP_File_Download_Button = class Tip_Jar_WP_File_Download_Button extends React.Component {

	constructor( props ){
		super(props);

		this.state = {};

	}

	handleSubmit( event ){

		var modify_state;

		event.preventDefault();

		// Should we modify the state of the Card Form?
		if ( this.props.card_form ) {
			modify_state = true;
		} else {
			modify_state = false;
		}

		// Validate any fields that are required (email, terms, etc)
		var allow_form_to_be_submitted = this.validate_file_download_form( modify_state );

		// Prevent the form submission if a field didn't validate
		if ( ! allow_form_to_be_submitted ) {

			this.setState( {
				current_payment_state: 'payment_attempt_failed',
			}, () => {
				return false;
			} );

		} else {

			// Set the visual state to be "free_file_download_completion"
			this.props.main_component.set_all_current_visual_states(
				{
					payment: {
						free_file_download_completion: {}
					}
				},
				false
			);

		}

	}

	validate_file_download_form( modify_state = true ) {

		var all_fields_validate = true;

		// Email field
		if ( ! this.props.email_validated ) {
			all_fields_validate = false;
		}

		// Privacy Policy
		if ( this.props.main_component.state.unique_settings.strings.input_field_instructions.privacy_policy.terms_body ) {
			if ( ! this.props.privacy_policy_validated ) {
				all_fields_validate = false;
			}
		}

		if ( modify_state ) {

			this.props.card_form.setState( {
				form_validation_attempted: true
			} );

			if ( ! all_fields_validate ) {

				this.props.card_form.setState( {
					form_has_any_error: true,
					current_payment_state: 'payment_attempt_failed',
				});
			} else {
				this.props.card_form.setState( {
					form_has_any_error: false,
					current_payment_state: 'initial',
				});
			}
		}

		return all_fields_validate;

	}

	render() {

		if (
			! this.props.main_component.state.unique_settings.file_download_attachment_data ||
			! this.props.main_component.state.unique_settings.file_download_attachment_data.file_download_mode_enabled
		) {
			return '';
		} else {
			return (
					<button
						type="button"
						onClick={ this.handleSubmit.bind( this ) }
						className={ 'tip-jar-wp-pay-button' }
					>
						{ this.props.main_component.state.unique_settings.strings.download_file_button_text }
					</button>
			)
		}
	}
}
