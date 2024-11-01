window.Tip_Jar_WP_Terms_Field = class Tip_Jar_WP_Terms_Field extends React.Component {

	constructor( props ) {
		super(props);

		this.state= {
			terms_checked: null,
			terms_are_visible: false,
			is_edit_focused: false
		};

		this.get_input_field_class = this.get_input_field_class.bind( this );
		this.get_input_instruction_class = this.get_input_instruction_class.bind( this );
		this.get_input_instruction_message = this.get_input_instruction_message.bind( this );
		this.get_terms_visibility = this.get_terms_visibility.bind( this );
	};

	componentDidMount() {

		// If this checkbox was previously set as validated in the containing form, set the default to be checked
		if ( this.props.is_validated && this.props.form_validation_attempted ) {
			this.setState( {
				terms_checked: true
			} );
		}
	}

	get_edit_in_focus_class() {
		if ( this.state.is_edit_focused ) {
			return ' tip-jar-wp-edit-area-in-focus';
		} else {
			return ' tip-jar-wp-edit-area-not-in-focus';
		}
	}

	dangerously_set_terms_body() {
		// The terms are not user input, and thus they do not pose a security risk. They do contain HTML from WordPress though, which is why we do this
		return { __html: this.props.terms_body };
	}

	get_current_instruction_key() {

		// Handle the instruction differently when the form containing this field has been submitted
		if ( this.props.form_validation_attempted ) {

			if ( this.props.is_validated ) {
				return 'checked';
			} else {
				return 'unchecked';
			}

		} else {

			// If the form containing this field has not yet been submitted
			if ( null == this.state.terms_checked ) {
				return 'initial';
			}
			if ( this.state.terms_checked ) {
				return 'checked';
			}
			if ( ! this.state.terms_checked || ! this.props.is_validated) {
				return 'unchecked';
			}

		}
	}

	get_input_instruction_class() {

		// Get the current instruction for this field
		var current_instruction = this.get_current_instruction_key();

		if ( this.props.instruction_codes[current_instruction] ) {
			if ( 'error' == this.props.instruction_codes[current_instruction].instruction_type ) {
				return ' tip-jar-wp-instruction-error';
			}
		}

		return '';

	};

	get_input_field_class() {

		// Get the current instruction for this field
		var current_instruction = this.get_current_instruction_key();

		if ( this.props.instruction_codes[current_instruction] ) {
			if ( 'success' == this.props.instruction_codes[current_instruction].instruction_type ) {
				return ' tip-jar-wp-input-success';
			}
			if ( 'error' == this.props.instruction_codes[current_instruction].instruction_type ) {
				return ' tip-jar-wp-input-error';
			}
			if ( 'initial' == this.props.instruction_codes[current_instruction].instruction_type ) {
				return '';
			}
		}

		return '';

	};

	get_input_instruction_message() {

		// Forcing this to a single message for now.
		return this.props.instruction_codes.initial.instruction_message;

		// Get the current instruction for this field
		var current_instruction = this.get_current_instruction_key();

		if ( this.props.instruction_codes[current_instruction] ) {
			return this.props.instruction_codes[current_instruction].instruction_message;
		}
	};

	get_terms_visibility() {

		// If editing mode is in focus for the terms, always show the terms text body
		if ( this.state.is_edit_focused ) {
			return '';
		}

		if( this.state.terms_are_visible ) {
			return '';
		} else {
			return 'hidden';
		}
	}

	toggle_full_terms() {

		if ( ! this.state.terms_are_visible ) {
			this.setState( {
				terms_are_visible: true
			} );
		} else {
			this.setState( {
				terms_are_visible: false
			} );
		}
	}

	handle_terms_change( event ) {

		var terms_checked;

		// This will toggle the privacy policy state
		if ( this.state.terms_checked ) {
			terms_checked = false;
		} else {
			terms_checked = true;
		}

		// Pass the validation status back to the parent.
		this.props.set_validation_and_value_of_field(
			this.props.state_validation_variable_name,
			terms_checked,
		);

		if ( this.props.form_validation_attempted ) {
			var this_component = this;
			// Wait for the state to be set in the parent
			setTimeout( function() {
				this_component.props.validate_form( true );
			}, 10 );
		}

		this.setState( {
			terms_checked: terms_checked
		} );

	};

	render_terms_agree_field(){

		var inputProps = {};

		// Set the initial checked state of this checkbox
		if ( null == this.state.terms_checked ) {
			// If this checkbox was previously set as validated in the containing form, set the default to be checked
			if ( this.props.is_validated && this.props.form_validation_attempted ) {
				inputProps['defaultChecked'] = 'checked';
			}
		}

		inputProps['onChange'] = this.handle_terms_change.bind( this );

		if ( this.props.class_name ) {
			inputProps['className'] = this.props.class_name + this.get_input_field_class();
		} else {
			inputProps['className'] = this.get_input_field_class();
		}

		if ( this.props.name ) {
			inputProps['name'] = this.props.name;
		}

		if ( this.props.placeholder ) {
			inputProps['placeholder'] = this.props.placeholder;
		}

		if ( this.props.defaultValue ) {
			inputProps['defaultValue'] = this.props.defaultValue;
		}

		if ( this.props.disabled ) {
			inputProps['disabled'] = this.props.disabled;
		}

		return(
			<div>
				<label>
					<input type="checkbox" { ...inputProps } />
					<div className="tip-jar-wp-terms-edit-container">
						{ (() => {
							if ( this.props.main_component.state.editing_mode && this.state.is_edit_focused ) {
								return tip_jar_wp_editing_strings.agreement_text;
							}
						})() }
						<TipJarWPContentEditableAsChild
							main_component={ this.props.main_component }
							html_tag="span"
							html_tag_attributes={ {
								className: 'tip-jar-wp-input-instruction' + this.get_input_instruction_class()
							} }
							html_tag_contents={ this.get_input_instruction_message() }
							editing_key={ 'strings/input_field_instructions/privacy_policy/initial/instruction_message' }
							is_focused={ this.state.is_edit_focused }
						/>
					</div>
				</label>
				<span> </span>
				<div className="tip-jar-wp-terms-edit-container">
					{ (() => {
						if ( this.props.main_component.state.editing_mode && this.state.is_edit_focused ) {
							return tip_jar_wp_editing_strings.view_terms_button_text;
						}
					})() }
					<TipJarWPContentEditableAsChild
						main_component={ this.props.main_component }
						html_tag="a"
						html_tag_attributes={ {
							className: 'tip-jar-wp-view-terms-button',
							onClick: this.toggle_full_terms.bind( this ),
						} }
						html_tag_contents={ this.props.terms_show_text }
						editing_key={ 'strings/input_field_instructions/privacy_policy/terms_show_text' }
						is_focused={ this.state.is_edit_focused }
					/>
				</div>
			</div>
		)
	}

	render_terms_title_and_description() {
		return(
			<div hidden={ this.get_terms_visibility() } className={ 'tip-jar-wp-expandable-terms' }>
				{ (() => {
					if ( this.props.main_component.state.editing_mode && this.state.is_edit_focused ) {
						return tip_jar_wp_editing_strings.terms_and_conditions_title;
					}
				})() }
				<TipJarWPContentEditableAsChild
					main_component={ this.props.main_component }
					html_tag="div"
					html_tag_attributes={ {
						className: 'tip-jar-wp-terms-title',
					} }
					html_tag_contents={ this.props.terms_title }
					editing_key={ 'strings/input_field_instructions/privacy_policy/terms_title' }
					is_focused={ this.state.is_edit_focused }
				/>
				{ (() => {
					if ( this.props.main_component.state.editing_mode && this.state.is_edit_focused ) {
						return tip_jar_wp_editing_strings.terms_and_conditions_body;
					}
				})() }
				<TipJarWPContentEditableAsChild
					main_component={ this.props.main_component }
					html_tag="div"
					html_tag_attributes={ {
						className: 'tip-jar-wp-terms-body',
						dangerouslySetInnerHTML: this.dangerously_set_terms_body(),
					} }
					html_tag_contents={ this.props.terms_body }
					editing_key={ 'strings/input_field_instructions/privacy_policy/terms_body' }
					is_focused={ this.state.is_edit_focused }
				/>
			</div>
		)
	}

	render_edit_and_hide_buttons() {
		return(
			<div className="tip-jar-wp-edit-button-container">
				{ (() => {
						if ( ! this.state.is_edit_focused ) {
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
						if ( this.state.is_edit_focused ) {
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
			</div>
		);
	}

	set_focus( should_be_focused, context, event ) {
		event.preventDefault();
		this.setState( {
			is_edit_focused: should_be_focused
		});
	}

	render() {

		// If we are in editing mode...
		if ( this.props.main_component.state.editing_mode ) {

			return (
				<div className={ 'tip-jar-wp-edit-container' + this.get_edit_in_focus_class() }>
					{ this.render_edit_and_hide_buttons() }
					{ this.render_terms_title_and_description() }
					{ this.render_terms_agree_field() }
				</div>
			);

			// If we are not in editing mode...
		} else {

			if ( this.props.main_component.state.unique_settings.strings.input_field_instructions.privacy_policy.terms_body ) {
				return (
					<React.Fragment>
						{ this.render_terms_title_and_description() }
						{ this.render_terms_agree_field() }
					</React.Fragment>
				)
			} else {
				return '';
			}
		}
	}

};
export default Tip_Jar_WP_Terms_Field;
