window.TipJarWPEditRecurringOptions = class TipJarWPEditRecurringOptions extends React.Component{

	constructor( props ){
		super(props);

		this.state = {
			currency_input_value: '',
			amount_input_value: ''
		};

		this.textInput = React.createRef();
	}

	componentDidMount() {

	}

	set_focus( should_be_focused, context, event ) {
		event.preventDefault();
		this.setState( {
			is_focused: should_be_focused
		});
	}

	toggle_recurring_options_enabled( event ) {
		event.preventDefault();
		if ( this.props.main_component.state.unique_settings.recurring_options_enabled ) {
			tip_jar_wp_pass_value_to_block( this.props.main_component, 'recurring_options_enabled', false, true );
		} else {
			tip_jar_wp_pass_value_to_block( this.props.main_component, 'recurring_options_enabled', true, true );
		}
	}

	render_edit_and_hide_buttons() {
		return(
			<div className="tip-jar-wp-edit-button-container">
				{ (() => {
						if ( ! this.state.is_focused ) {
							return(
								<button
									type="button"
									className="button tip-jar-wp-edit-button"
									onClick={ this.set_focus.bind( this, true, 'edit' ) }
								>
								{
									tip_jar_wp_editing_strings.edit
								}
								</button>
							)
						}
					})()
				}
				{ (() => {
						if ( this.state.is_focused ) {
							return (
								<button
									type="button"
									className="button tip-jar-wp-view-button"
									onClick={ this.set_focus.bind( this, false, 'view' ) }
								>
								{
									tip_jar_wp_editing_strings.view
								}
								</button>
							)
						}
					})()
				}
				{ (() => {
						if ( ! this.props.main_component.state.unique_settings.recurring_options_enabled ) {
							return (
								<button
									type="button"
									className="button tip-jar-wp-edit-button"
									onClick={ this.toggle_recurring_options_enabled.bind( this ) }
								>
								{
									tip_jar_wp_editing_strings.enable_recurring_options
								}
								</button>
							)
						} else {
							return (
								<button
									type="button"
									className="button tip-jar-wp-edit-button"
									onClick={ this.toggle_recurring_options_enabled.bind( this ) }
								>
								{
									tip_jar_wp_editing_strings.disable_recurring_options
								}
								</button>
							)
						}
					})()
				}
			</div>
		);
	}

	render_recurring_options_and_instructions() {

		return (
			<Tip_Jar_WP_Radio_Field
				main_component={ this.props.main_component }
				state_validation_variable_name={ 'recurring_validated' }
				state_value_variable_name={ 'recurring_value' }
				set_validation_and_value_of_field={ this.props.payment_box.set_validation_and_value_of_field.bind( this.props.payment_box ) }
				form_validation_attempted={ this.props.payment_box.state.form_validation_attempted }
				is_validated={ this.props.payment_box.state.recurring_validated }
				validate_form={ this.props.payment_box.validate_form.bind( this.props.payment_box ) }
				instruction_codes={ this.props.main_component.state.unique_settings.strings.input_field_instructions.recurring }

				type="radio"
				radio_buttons={ this.props.main_component.state.unique_settings.recurring_options }
				class_name={ 'tip-jar-wp-recurring' }
				placeholder={ this.props.main_component.state.unique_settings.strings.input_field_instructions.recurring.placeholder_text }
				name="recurring"
				editing_key={ 'strings/input_field_instructions/recurring/[current_key_here]/instruction_message' }
				is_edit_child={ true }
				is_focused={ this.state.is_focused }
			/>
		)

	}

	get_in_focus_class() {
		if ( this.state.is_focused ) {
			return ' tip-jar-wp-edit-area-in-focus';
		} else {
			return ' tip-jar-wp-edit-area-not-in-focus';
		}
	}

	render() {

		// If we are in editing mode...
		if ( this.props.main_component.state.editing_mode ) {

			return (
				<div className={ 'tip-jar-wp-edit-container' + this.get_in_focus_class() }>
					{ this.render_edit_and_hide_buttons() }
					{ this.render_recurring_options_and_instructions() }
				</div>
			);

			// If we are not in editing mode...
		} else {

			if ( this.props.main_component.state.unique_settings.recurring_options_enabled ) {
				return (
					this.render_recurring_options_and_instructions()
				);
			} else {
				return '';
			}
		}
	}

}
export default TipJarWPEditRecurringOptions;
