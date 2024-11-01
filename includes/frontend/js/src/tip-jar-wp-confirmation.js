var tip_jar_wp_vars = tip_jar_wp_js_vars.tip_form_vars;

window.Tip_Jar_WP_Payment_Confirmation = class Tip_Jar_WP_Payment_Confirmation extends React.Component{

	constructor( props ){
		super(props);

		this.state = {
			note_with_tip_value: null,
			note_with_tip_validated: false,

			form_validation_attempted: false,
			time_since_last_keypress: 0,
			after_payment_actions_completed: false,
			print_html: null,
			sending_email_receipt: false,
			email_receipt_success: null
		};

		this.note_with_tip_keypress_delay;
		this.render_refunded_output = this.render_refunded_output.bind( this );
		this.email_transaction_receipt = this.email_transaction_receipt.bind( this );
	}

	componentDidMount() {
		// Set up our print HTML upon mount
		if ( this.props.main_component.state.dom_node ) {
			this.setState( {
				print_html: this.props.main_component.state.dom_node.outerHTML
			} );
		}

		// If we should fire the actions that take place after a payment upon component mounting...
		if ( this.props.do_after_payment_actions ) {
			this.do_after_payment_actions();
		}
	}

	componentDidUpdate() {

		// Update our print HTML upon mount
		if ( this.props.main_component.state.dom_node ) {
			if ( this.state.print_html != this.props.main_component.state.dom_node.outerHTML ) {
				this.setState( {
					print_html: this.props.main_component.state.dom_node.outerHTML
				} );
			}
		}

	}

	do_after_payment_actions() {
		this.email_transaction_receipt( true, false );
	}

	get_transaction_visual_amount() {

		var cents = this.props.main_component.state.current_transaction_info.transaction_charged_amount;
		var currency = this.props.main_component.state.current_transaction_info.transaction_charged_currency;
		var is_zero_decimal_currency = this.props.main_component.state.current_transaction_info.transaction_currency_is_zero_decimal;
		var string_after = ' (' + currency.toUpperCase() + ')';

		return tip_jar_wp_format_money( cents, currency, is_zero_decimal_currency, string_after );

	}

	get_arrangement_visual_amount() {

		var cents = this.props.main_component.state.current_transaction_info.arrangement_info.amount;
		var currency = this.props.main_component.state.current_transaction_info.arrangement_info.currency;
		var is_zero_decimal_currency = this.props.main_component.state.current_transaction_info.arrangement_info.is_zero_decimal_currency;
		var string_after = this.props.main_component.state.current_transaction_info.arrangement_info.string_after + ' (' + currency.toUpperCase() + ')';

		return tip_jar_wp_format_money( cents, currency, is_zero_decimal_currency, string_after );

	}

	maybe_render_the_period_this_transaction_covers() {

		var start_date = this.props.main_component.state.current_transaction_info.transaction_period_start_date;
		var end_date = this.props.main_component.state.current_transaction_info.transaction_period_end_date;
		var period_string;

		if ( ! start_date || ! end_date ) {
			return '';
		}

		if ( '0000-00-00 00:00:00' == start_date || '0000-00-00 00:00:00' == end_date ) {
			return '';
		}

		period_string = tip_jar_wp_format_date( start_date ) + ' - ' + tip_jar_wp_format_date( end_date );

		return(
			<div>
				<span className="tip-jar-wp-receipt-line-item-title">{ this.props.main_component.state.unique_settings.strings.transaction_period + ': ' }</span>
				<span className="tip-jar-wp-receipt-line-item-value">{ period_string }</span>
			</div>
		);

	}

	validate_form( modify_state ) {

		var all_fields_validate = true;

		// Note with tip field
		if ( ! this.state.note_with_tip_validated ) {
			all_fields_validate = false;
		}

		return all_fields_validate;

	}

	email_transaction_receipt( notify_admin_too = false, send_regardless_of_initial_emails_sent = false ) {

		this.setState( {
			sending_email_receipt: true,
			email_receipt_success: null
		} );

		// Do any after-payment actions that need to take place via ajax
		var postData = new FormData();
		postData.append('action', 'tip_jar_wp_email_transaction_receipt' );
		postData.append('tip_jar_wp_transaction_id', this.props.main_component.state.current_transaction_info.transaction_id);
		postData.append('tip_jar_wp_session_id', this.props.main_component.state.session_id);
		postData.append('tip_jar_wp_user_id', this.props.main_component.state.user_id);
		postData.append('tip_jar_wp_notify_admin_too', notify_admin_too);
		postData.append('tip_jar_wp_send_regardless_of_initial_emails_sent', send_regardless_of_initial_emails_sent);
		postData.append('tip_jar_wp_email_transaction_receipt_nonce', this.props.main_component.state.frontend_nonces.tip_jar_wp_email_transaction_receipt_nonce);

		var this_component = this;

		// Here we will handle anything that needs to be done after the payment was completed.
		fetch( tip_jar_wp_js_vars.ajaxurl + '?tip_jar_wp_email_transaction_receipt', {
			method: "POST",
			mode: "same-origin",
			credentials: "same-origin",
			body: postData
		} ).then(
			function( response ) {
				if ( response.status !== 200 ) {

					this_component.setState( {
						sending_email_receipt: false,
						email_receipt_success: false
					} );

					console.log('Looks like there was a problem. Status Code: ' + response.status);
					return;
				}

				// Examine the text in the response
				response.json().then(
					function( data ) {
						if ( data.success ) {

							this_component.setState( {
								sending_email_receipt: false,
								email_receipt_success: true
							} );

						} else {

							console.log( data );

							this_component.setState( {
								sending_email_receipt: false,
								email_receipt_success: false
							} );

						}
					}
				).catch( () => {
					this_component.setState( {
						sending_email_receipt: false,
						email_receipt_success: false
					} );

					console.log( response );
				} );
			}
		).catch(
			function( err ) {

				this_component.setState( {
					sending_email_receipt: false,
					email_receipt_success: false
				} );

				console.log('Fetch Error :-S', err);
			}
		);
	}

	set_validation_and_value_of_field( state_validation_variable, is_validated, state_value_variable = null, state_value = null ) {

		if ( 'note_with_tip_value' != state_value_variable ) {

			if ( null == state_value_variable ) {
				this.setState( {
					[state_validation_variable]: is_validated,
				} );
			} else {
				this.setState( {
					[state_validation_variable]: is_validated,
					[state_value_variable]: state_value,
				} );
			}

		} else {

			// If we are saving the note with tip
			var old_note_with_tip = this.state.note_with_tip;
			var this_component = this;

			// We won't set the validation to true until the ajax response comes back
			this.setState( {
				note_with_tip_validated: 'typing',
				note_with_tip_value: state_value,
			} );

			// If nothing has changed since the state was last set
			if ( state_value == old_note_with_tip ) {

				// Do nothing
				return false;

			} else {

				// Set up a delay which waits to save the tip until .5 seconds after they stop typing.
				if( this.note_with_tip_keypress_delay ) {
					// Clear the keypress delay if the user just typed
					clearTimeout( this.note_with_tip_keypress_delay );
					this.note_with_tip_keypress_delay = null;
				}

				// (Re)-Set up the save_note_with_tip to fire in 500ms
				this.note_with_tip_keypress_delay = setTimeout( function() {
					clearTimeout( this.note_with_tip_keypress_delay );
					this_component.save_note_with_tip( state_value );
				}, 500);

			}
		}
	}

	save_note_with_tip( note_with_tip ) {

		this.setState( {
			note_with_tip_validated: 'saving',
		} );

		// We'll auto save the entered tip note into the database's transaction field via ajax every time the person stops typing for 1 second.
		var postData = new FormData();
		postData.append('action', 'tip_jar_wp_save_note_with_tip');
		postData.append('tip_jar_wp_transaction_id', this.props.main_component.state.current_transaction_info.transaction_id);
		postData.append('tip_jar_wp_note_with_tip', this.state.note_with_tip_value);
		postData.append('tip_jar_wp_session_id', this.props.main_component.state.session_id);
		postData.append('tip_jar_wp_user_id', this.props.main_component.state.user_id);
		postData.append('tip_jar_wp_note_with_tip_nonce', this.props.main_component.state.frontend_nonces.note_with_tip_nonce);

		var this_component = this;

		fetch( tip_jar_wp_js_vars.ajaxurl + '?tip_jar_wp_save_note_with_tip', {
			method: "POST",
			mode: "same-origin",
			credentials: "same-origin",
			body: postData
		} ).then(
			function( response ) {
				if ( response.status !== 200 ) {
					console.log('Looks like there was a problem. Status Code: ' +
					response.status);
					return;
				}

				// Examine the text in the response
				response.json().then(
					function( data ) {
						if ( data.success ) {

							// The note was successfully saved. Adjust the validation to true
							this_component.setState( {
								note_with_tip_validated: true,
							} );

						} else {
							console.log( data );

							// The note was not successfully saved. Adjust the validation to false
							this_component.setState( {
								note_with_tip_validated: false,
							} );
						}
					}
				);
			}
		).catch(
			function( err ) {
				console.log('Fetch Error :-S', err);
			}
		);
	}

	render_email_button() {

		var email_message = '';

		// If the receipt was just successfully sent, or just failed to send
		if ( this.state.email_receipt_success ) {
			email_message = <div className="tip-jar-wp-email-receipt-message">{ this.props.main_component.state.unique_settings.strings.email_receipt_success }</div>
		}

		if ( null !== this.state.email_receipt_success && ! this.state.email_receipt_success ) {
			email_message = <div className="tip-jar-wp-email-receipt-message">{ this.props.main_component.state.unique_settings.strings.email_receipt_failed }</div>
		}

		if ( this.state.sending_email_receipt ) {
			return (
				<div className="tip-jar-wp-email-receipt">
					{ this.props.main_component.state.unique_settings.strings.email_receipt_sending }
					<button type="button" className={ 'tip-jar-wp-pay-button' }>{ this.props.main_component.state.unique_settings.strings.email_receipt_sending }</button>
				</div>
			);
		}

		if ( ! this.state.sending_email_receipt ) {
			return (
				<div className="tip-jar-wp-email-receipt">
					{ email_message }
					<button type="button" className={ 'tip-jar-wp-pay-button' } onClick={ this.email_transaction_receipt.bind( this, false, true ) }>{ this.props.main_component.state.unique_settings.strings.email_receipt }</button>
				</div>
			);
		}

	}

	render_print_button() {

		if ( this.state.print_html ) {

			if( typeof window.print == 'function' ) {
				return (
					<div className="tip-jar-wp-print-receipt">
					<button type="button" className={ 'tip-jar-wp-pay-button' } onClick={ tip_jar_wp_print_div.bind( null, this.state.print_html, this.props.main_component.state.unique_settings.strings.receipt_title, 'tip_jar_wp_default_skin-css' ) }>{ this.props.main_component.state.unique_settings.strings.print_receipt }</button>
					</div>
				)
			}

		}
	}

	render_manage_payments_button() {

		if ( ! this.props.show_manage_payments ) {
			return( '' );
		}

		return(
			<button type="button" className={ 'tip-jar-wp-manage-payments-button tip-jar-wp-input-instruction tip-jar-wp-text-button' } onClick={ this.props.main_component.set_all_current_visual_states.bind( null, {
				manage_payments: {}
			}, false ) }>{ this.props.main_component.state.unique_settings.strings.manage_payments_button_text }</button>
		);

	}

	render_refunded_output() {

		// If this is a refund transaction
		if ( 'refund' == this.props.main_component.state.current_transaction_info.transaction_type ) {
			return( 'This is a refund for transaction' + ' ' + this.props.main_component.state.current_transaction_info.refund_id );
		}

		// If this is an initial transaction that has been refunded
		if ( this.props.main_component.state.current_transaction_info.refund_id ) {
			if (
				'initial' == this.props.main_component.state.current_transaction_info.transaction_type ||
				'renewal' == this.props.main_component.state.current_transaction_info.transaction_type
			) {
				return( 'This transaction has been refunded. See transaction ' + this.props.main_component.state.current_transaction_info.refund_id );
			}
		}

		return( '' );

	}

	render_things_before_receipt() {

		// Don't show extra things on refund receipts, like note with tip
		if ( 'refund' == this.props.main_component.state.current_transaction_info.transaction_type ) {
			return '';
		}

		return (
			<React.Fragment>
				<div className="tip-jar-wp-confirmation-message">
				{ this.props.main_component.state.unique_settings.strings.thank_you_message }
				</div>
				<div className="tip-jar-wp-confirmation-note">
				{
					<Tip_Jar_WP_TextArea_Field
						main_component={ this.props.main_component }
						state_validation_variable_name={ 'note_with_tip_validated' }
						state_value_variable_name={ 'note_with_tip_value' }
						set_validation_and_value_of_field={ this.set_validation_and_value_of_field.bind( this ) }
						form_validation_attempted={ this.state.form_validation_attempted }
						is_validated={ this.state.note_with_tip_validated }
						validate_form={ this.validate_form.bind( this ) }
						instruction_codes={ this.props.main_component.state.unique_settings.strings.input_field_instructions.note_with_tip }
						editing_key={ 'strings/input_field_instructions/note_with_tip/[current_key_here]/instruction_message' }
						value={ this.props.main_component.state.current_transaction_info ? this.props.main_component.state.current_transaction_info.transaction_note_with_tip : '' }

						type="text"
						class_name={ 'tip-jar-wp-note-with-tip' }
						placeholder={ this.props.main_component.state.unique_settings.strings.input_field_instructions.note_with_tip.placeholder_text }
						name="tip-amount"

					/>
				}
				</div>
			</React.Fragment>
		);


	}

	maybe_render_plan_details() {

		if ( 'off' !== this.props.main_component.state.current_transaction_info.arrangement_info.recurring_status ) {
			return(
				<React.Fragment>
					<div>
						<span className="tip-jar-wp-receipt-line-item-title">{ this.props.main_component.state.unique_settings.strings.arrangement_id_title + ': ' }</span>
						<span className="tip-jar-wp-receipt-line-item-value">{ this.props.main_component.state.current_transaction_info.arrangement_info.id }</span>
					</div>
					<div>
						<span className="tip-jar-wp-receipt-line-item-title">{ this.props.main_component.state.unique_settings.strings.arrangement_amount_title + ': ' }</span>
						<span className="tip-jar-wp-receipt-line-item-value">{ this.get_arrangement_visual_amount() }</span>
					</div>
					{ this.maybe_render_the_period_this_transaction_covers() }
				</React.Fragment>
			)
		}

	}

	render() {

		if ( ! this.props.main_component.state.current_transaction_info ) {
			return ( <Tip_Jar_WP_Spinner /> );
		}

		return (
			<div className="tip-jar-wp-payment-confirmation">
				{ this.render_things_before_receipt() }
				<div className="tip-jar-wp-receipt">
					<div className="tip-jar-wp-receipt-title">
					{ this.props.main_component.state.unique_settings.strings.receipt_title }
					</div>
					<div className="tip-jar-wp-receipt-field-space-below">
						{ this.props.main_component.state.current_transaction_info.email }
					</div>

					<div className="tip-jar-wp-receipt-field-space-below">
						{ this.render_refunded_output() }
					</div>

					<div className="tip-jar-wp-receipt-payee">
						<span className="tip-jar-wp-receipt-line-item-title tip-jar-wp-receipt-payee-title">{ ( 'refund' == this.props.main_component.state.current_transaction_info.transaction_type ? this.props.main_component.state.unique_settings.strings.refund_payer : this.props.main_component.state.unique_settings.strings.receipt_payee ) + ': ' }</span>
						<span className="tip-jar-wp-receipt-line-item-value tip-jar-wp-receipt-payee-value">{ this.props.main_component.state.current_transaction_info.payee_name }</span>
					</div>
					<div className="tip-jar-wp-receipt-transaction-id">
						<span className="tip-jar-wp-receipt-line-item-title tip-jar-wp-receipt-transaction-id-title">{ this.props.main_component.state.unique_settings.strings.receipt_transaction_id + ': ' }</span>
						<span className="tip-jar-wp-receipt-line-item-value tip-jar-wp-receipt-transaction-id-value">{ this.props.main_component.state.current_transaction_info.transaction_id }</span>
					</div>
					<div className="tip-jar-wp-receipt-transaction-date">
						<span className="tip-jar-wp-receipt-line-item-title tip-jar-wp-receipt-date-title">{ this.props.main_component.state.unique_settings.strings.receipt_date + ': ' }</span>
						<span className="tip-jar-wp-receipt-line-item-value tip-jar-wp-receipt-date-value">{ tip_jar_wp_format_date_and_time( this.props.main_component.state.current_transaction_info.transaction_date_created ) }</span>
					</div>
					<div className="tip-jar-wp-receipt-amount">
						<span className="tip-jar-wp-receipt-line-item-title tip-jar-wp-receipt-transaction-amount-title">{ this.props.main_component.state.unique_settings.strings.receipt_transaction_amount + ': ' }</span>
						<span className="tip-jar-wp-receipt-line-item-value tip-jar-wp-receipt-transaction-amount-value">{ this.get_transaction_visual_amount() }</span>
					</div>
					<div className="tip-jar-wp-receipt-statement-descriptor">
						<span className="tip-jar-wp-receipt-line-item-title tip-jar-wp-receipt-transaction-amount-title">{ this.props.main_component.state.unique_settings.strings.receipt_statement_descriptor + ': ' }</span>
						<span className="tip-jar-wp-receipt-line-item-value tip-jar-wp-receipt-transaction-amount-value">{ this.props.main_component.state.current_transaction_info.statement_descriptor }</span>
					</div>
					{ this.maybe_render_plan_details() }

					<div className='tip-jar-wp-receipt-action-button'>
						<Tip_Jar_WP_File_Download_Button
							main_component={ this.props.main_component }
							card_form={ null }
							email_value={ this.props.main_component.state.current_transaction_info.email }
							email_validated={ true }
							privacy_policy_validated={ true }
							mode={ 'receipt' }
						 />
					</div>

				</div>
				{ this.render_email_button() }
				{ this.render_print_button() }
				{ this.render_manage_payments_button() }
			</div>
		)
	}

}
export default Tip_Jar_WP_Payment_Confirmation;

// This function takes html, puts it on a single page, and then sets that page to print.
function tip_jar_wp_print_div( html_to_print, page_title_to_use, css_stylesheet_id ) {

	// Copy the <head> tag
	var head_tag = document.querySelector( 'head' );

	var mywindow = window.open( '', page_title_to_use, 'height=6000,width=8000' );
	mywindow.document.write( head_tag.outerHTML );
	mywindow.document.write( '<body class="tip-jar-wp-print-page">' );
	mywindow.document.write( html_to_print );
	mywindow.document.write( '</body></html>' );

	// Wait for 1 second before attempting to print it so it can write everything and load the CSS
	setTimeout( function() {

		mywindow.focus()
		mywindow.print();

	}, 2000 );

	return true;
}
