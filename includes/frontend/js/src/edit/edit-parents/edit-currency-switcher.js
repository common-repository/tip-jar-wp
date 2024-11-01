import React, {useState, useEffect} from 'react';

window.TipJarWPEditDefaultAmountAndCurrency = function TipJarWPEditDefaultAmountAndCurrency(props) {

	const [is_focused, set_is_focused] = useState(false);
	const[ input_amount, set_input_amount ] = useState( props.payment_box.get_visual_amount_for_input_field( true ) );

	useEffect( () => {
		if ( is_focused !== props.payment_box.state.edit_currency_is_focused ) {
			props.payment_box.setState( {
				edit_currency_is_focused: is_focused
			} );
		}
	}, [] );

	function handleBlur( event ) {
		// Pass the value to the parent component's handler.
		props.payment_box.handleAmountChange(event).then( () => {

			// Set the state of the amount in this component.
			set_input_amount( props.payment_box.get_visual_amount_for_input_field( true ) );
	
			// If we are focused (or in "Editing mode" for this element), pass the value to the block where it is saved to the form.
			if ( is_focused ) {
				tip_jar_wp_pass_value_to_block( props.main_component, props.amount_editing_key, props.payment_box.state.tip_amount, true );
			}

		} );
	}

	function handleAmountChange( event ) {

		// Pass the value to the parent component's handler.
		props.payment_box.handleAmountChange(event).then( () => {

			// Set the state of the amount in this component.
			set_input_amount( props.payment_box.get_visual_amount_for_input_field( false ) );
	
			// If we are focused (or in "Editing mode" for this element), pass the value to the block where it is saved to the form.
			if ( is_focused ) {
				tip_jar_wp_pass_value_to_block( props.main_component, props.amount_editing_key, props.payment_box.state.tip_amount, true );
			}

		} );
	}

	function render_edit_and_done_buttons() {
		return(
			<div className="tip-jar-wp-edit-button-container">
				{ (() => {
						if ( ! is_focused ) {
							return(
								<button
									type="button"
									className="button tip-jar-wp-edit-button"
									onClick={ () => { 
										console.log('sdgsdg');
										set_is_focused(true); 
									} }
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
						if ( is_focused ) {
							return (
								<button
									type="button"
									className="button tip-jar-wp-view-button"
									onClick={ () => { 
										console.log('sdgsdg');
										set_is_focused(false); 
									} }
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

	function render_amount_and_currency_fields() {

		return (
			<React.Fragment>
				<Tip_Jar_WP_Input_Field_Instruction
					main_component={ props.main_component }
					current_instruction={ props.payment_box.state.input_fields_tip_amount_current_instruction }
					instruction_codes={ props.main_component.state.unique_settings.strings.input_field_instructions.tip_amount }
					editing_key={ 'strings/input_field_instructions/tip_amount/' + props.payment_box.state.input_fields_tip_amount_current_instruction + '/instruction_message' }
					is_edit_child={ true }
					is_focused={ is_focused }
				/>
				<div className={ 'tip-jar-wp-amount-container' +  ( () => {
					if ( props.payment_box.state.currency_search_visible ) {
						return ' currency-search-visible';
					} else {
						return '';
					}
				})()}>
					<div className={ 'tip-jar-wp-tip-currency-symbol' }>{ props.payment_box.state.verified_currency_symbol }</div>
					<div className={ 'tip-jar-wp-tip-amount-input-container' }>
						<label>
							<input
								disabled={ props.payment_box.get_disabled_status( [ 'credit_card', 'payment_request', 'free_file_download' ] ) }
								type="number"
								min={ 1 }
								step={ props.payment_box.get_amount_field_step_format() }
								className={ 'tip-jar-wp-tip-amount-input' }
								placeholder={ props.main_component.state.unique_settings.strings.input_field_instructions.tip_amount.placeholder_text }
								name="tip-amount"
								onChange={ handleAmountChange }
								onFocus={ handleAmountChange }
								onBlur={ handleBlur }
								value={ input_amount }
							/>
						</label>
					</div>
					<div className={ 'tip-jar-wp-currency-switcher' }>
						{ props.payment_box.render_currency_switcher() }
					</div>
				</div>
			</React.Fragment>
		)

	}

	function get_in_focus_class() {
		if ( is_focused ) {
			return ' tip-jar-wp-edit-area-in-focus';
		} else {
			return ' tip-jar-wp-edit-area-not-in-focus';
		}
	}

	// If we are in editing mode...
	if ( props.main_component.state.editing_mode ) {

		return (
			<div className={ 'tip-jar-wp-edit-container' + get_in_focus_class() }>
				{ render_edit_and_done_buttons() }
				{ render_amount_and_currency_fields() }
			</div>
		);

		// If we are not in editing mode, show nothing here.
	} else {
		return (
			render_amount_and_currency_fields()
		);
	}
	

}
export default TipJarWPEditDefaultAmountAndCurrency;
