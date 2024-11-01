var tip_jar_wp_vars = tip_jar_wp_js_vars.tip_form_vars;

window.Tip_Jar_WP_Manage_Payments_Nav = class Tip_Jar_WP_Manage_Payments_Nav extends React.Component {

	constructor( props ){
		super(props);

		this.state = {};

	}

	set_view_to_transactions() {
		this.props.main_component.set_all_current_visual_states( {
			manage_payments: {
				transactions: {}
			}
		} )
	}

	set_view_to_arrangements() {
		this.props.main_component.set_all_current_visual_states( {
			manage_payments: {
				arrangements: {}
			}
		} )
	}

	get_current_button_class( button_in_question ) {
		if ( this.props.current_visual_state == button_in_question ) {
			return ' tip-jar-wp-manage-nav-current-btn';
		} else {
			return '';
		}
	}

	render() {

		if ( this.props.main_component.state.user_logged_in ) {
			return(
				<div className="tip-jar-wp-manage-payments-nav-container-full">
					<div className="tip-jar-wp-manage-payments-nav-container-center">
						<div className="tip-jar-wp-manage-payments-nav">
							<div className={ "tip-jar-wp-arrangements-btn" + this.get_current_button_class( 'arrangements' ) }>
								<button type="button" className="tip-jar-wp-text-button" onClick={ this.set_view_to_arrangements.bind( this ) }>{ this.props.main_component.state.unique_settings.strings.arrangements_title }</button>
							</div>
							<div className={ "tip-jar-wp-transactions-btn"  + this.get_current_button_class( 'transactions' ) }>
								<button type="button" className="tip-jar-wp-text-button" onClick={ this.set_view_to_transactions.bind( this ) }>{ this.props.main_component.state.unique_settings.strings.transactions_title }</button>
							</div>
						</div>
					</div>
				</div>
			);
		} else {
			return( '' );
		}
	}
}
export default Tip_Jar_WP_Manage_Payments_Nav;
