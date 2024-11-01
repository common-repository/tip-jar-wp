/*
 * Tip Jar WP
 * https://www.tipjarwp.com
 *
 * Licensed under the GPL license.
 *
 * Author: Tip Jar WP
 * Version: 1.0
 * Date: April 18, 2018
 */

window.Tip_Jar_WP_Form = class Tip_Jar_WP_Form extends React.Component {

	constructor( props ){
		super(props);

		this.state = {
			editing_mode: false,
			show_edit_display_mode: true,
			editing_lightbox_active: false,
			all_initial_visual_states: {
				payment: {}
			},
			all_current_visual_states: this.props.all_current_visual_states, // This is an object containing the entire visual state for the Single Page App
			modal_visual_state: this.props.dynamic_settings.modal_visual_state,
			single_page_app_base_url: this.props.dynamic_settings.wordpress_permalink_only,
			dom_node: null,

			user_id: null,
			frontend_nonces: this.props.frontend_nonces
		};

		this.get_current_view_class = this.get_current_view_class.bind( this );
		this.set_all_current_visual_states = this.set_all_current_visual_states.bind( this );
		this.handle_visual_state_change_click_event = this.handle_visual_state_change_click_event.bind( this );

	}

	componentDidMount() {

		// Set the initial view state based on the initialization props
		this.setState( {
			editing_mode: this.props.editing_mode ? true : false,
			unique_settings: this.props.unique_settings,
			dynamic_settings: this.props.dynamic_settings,
			frontend_nonces: this.props.frontend_nonces,
			all_initial_visual_states: this.props.all_current_visual_states,
			all_current_visual_states: this.props.all_current_visual_states,
			modal_visual_state: this.props.dynamic_settings.modal_visual_state,
			initial_modal_visual_state: this.props.dynamic_settings.modal_visual_state,
			show_edit_display_mode: this.props.show_edit_display_mode,
		}, function() {

			// If the URL in the browser's address bar doesn't match what the initial state is for some reason, adjust the browser's history so it's right
			//this.set_all_current_visual_states( this.state.all_initial_visual_states );

		} );

		// Prevent scroll jumps upon back button clicks
		if ('scrollRestoration' in history) {
			history.scrollRestoration = 'manual';
		}

		// Create an event listener to respond to back button clicks
		window.addEventListener('popstate', (e) => {
			this.on_web_history_change( e, this );
		});

	}

	componentDidUpdate() {
		this.maybe_refresh_parent_dom_node();

		if (
			this.props.unique_settings !== this.state.unique_settings ||
			this.props.dynamic_settings !== this.state.dynamic_settings ||
			( (false === this.props.editing_mode || true === this.props.editing_mode ) && this.props.editing_mode !== this.state.editing_mode )
		) {
			this.setState( {
				editing_mode: this.props.editing_mode ? true : false,
				unique_settings: this.props.unique_settings,
				dynamic_settings: this.props.dynamic_settings,
				all_current_visual_states: this.props.all_current_visual_states,
				modal_visual_state: this.props.dynamic_settings.modal_visual_state,
			}, () => {
				this.set_all_current_visual_states( this.state.all_current_visual_states, this.state.modal_visual_state );
			} );
		}
	}

	maybe_refresh_parent_dom_node() {

		/*
		if ( this.state.dom_node !== ReactDOM.findDOMNode(this).parentNode ) {
			this.setState( {
				dom_node: ReactDOM.findDOMNode(this).parentNode
			} );
		}
		*/

	}

	on_web_history_change( e, this_component ) {

		var history_state = e.state;

		// If there's no state in the back button, we're in the initial state.
		if (history_state == null) {
			this_component.setState( {
				'all_current_visual_states': this_component.state.all_initial_visual_states,
				'modal_visual_state': this_component.state.initial_modal_visual_state
			} );
		}
		// If there is a state in the history, set the current state to that
		else {
			this_component.setState( {
				'all_current_visual_states': history_state.tip_jar_wp_visual_state,
				'modal_visual_state': history_state.tjwpmodal_visual_state
			} );
		}

	}

	get_current_view_class( view_in_question ) {

		var currently_in_view_class_name = 'tip-jar-wp-current-view';
		var hidden_class_name = 'tip-jar-wp-hidden-view';
		var current_visual_state = Object.keys(this.state.all_current_visual_states)[0]; // This grabs the name of the the first key, which is the visual state of this component

		// If the current visual state matches the view we are getting the class for
		if( current_visual_state == view_in_question ) {

			return ' ' + currently_in_view_class_name;

		} else {

			return ' ' + hidden_class_name;

		}

	}

	get_featured_image_class() {

		if (
			! this.state.top_media_editor_focused &&
			(
				// If there is a featured image url, AND there is NOT a top media type (like it was before top media types became a thing)
				( this.state.unique_settings.featured_image_url && ! this.state.unique_settings.top_media_type ) || // This is a legacy check for before featured media was added.
				'none' !== this.state.unique_settings.top_media_type
			)
		) {

			return ' tip-jar-wp-has-featured-media';

		} else {
			return '';
		}

	}

	build_new_url_path( obj, new_url_path, depth ) {
		depth = depth + 1;
		var prefix = 1 == depth ? 'tjwp' : '&tjwp';
		for (var component_visual_state in obj) {
				new_url_path = this.build_new_url_path(obj[component_visual_state], new_url_path + prefix + depth + '=' + component_visual_state, depth );
		}
		return new_url_path;
	}

	handle_visual_state_change_click_event( new_state, new_modal_state, event ) {
		this.set_all_current_visual_states( new_state, new_modal_state );
	}

	set_all_current_visual_states( new_state = false, new_modal_state = false ) {

		var in_initial_state = false;

		return new Promise( (resolve, reject) => {

			// If the URL is an empty object, set it to the initial state
			if ( Object.entries(new_state).length === 0 && new_state.constructor === Object ) {
				new_state = this.state.all_initial_visual_states;
				in_initial_state = true;
			}

			// If no new state was passed, we're probably just updating the modal state.
			if ( ! new_state ) {
				new_state = this.state.all_current_visual_states;
			}

			// If no modal state was passed, we probably are just updating the main state.
			if ( ! new_modal_state ) {
				new_modal_state = this.state.modal_visual_state;
			}

			this.setState( {
				all_current_visual_states: new_state,
				modal_visual_state: new_modal_state
			}, () => {

				// New URL, make sure it handles URLs with and without a trailing slash
				if ( this.state.dynamic_settings.wordpress_permalink_only.includes("?") ) {
					var new_url_prefix = this.state.dynamic_settings.wordpress_permalink_only + '&';
				} else {
					var new_url_prefix = this.state.dynamic_settings.wordpress_permalink_only + '?';
				}

				var new_url = new_url_prefix + this.build_new_url_path( this.state.all_current_visual_states, '', 0 );

				// If there is a modal open, add it to the end of the URL
				if ( Object.keys(this.state.modal_visual_state)[0] ) {
					new_url = new_url + '&tjwpmodal=' + Object.keys(this.state.modal_visual_state)[0];
				}

				// Take a snapshot of the current visual state and add it to the web history
				if ( in_initial_state ) {
					history.pushState({
						tip_jar_wp_visual_state: this.state.all_current_visual_states,
						tjwpmodal_visual_state: this.state.modal_visual_state
					}, new_state, this.state.dynamic_settings.wordpress_permalink_only);
				} else {
					history.pushState({
						tip_jar_wp_visual_state: this.state.all_current_visual_states,
						tjwpmodal_visual_state: this.state.modal_visual_state
					}, new_state, new_url);
				}

				resolve( new_state );

			} );

		} );

	}

	render_top_media() {
		return(
			<Tip_Jar_WP_Top_Media
				main_component={ this }
			/>
		)
	}

	render() {

		// Checks for HTTPS from the browser
		if ( window.location.protocol != "https:" ) {
			return(
				<div className={ 'tip-jar-wp-container' }>
					You must have an SSL certificate in order to accept payments on your website. Contact your webhost to have them install an SSL certificate on your website.
				</div>
			);
		}
		else if ( ! this.state.all_current_visual_states || ! this.state.unique_settings ) {
			return( <Tip_Jar_WP_Spinner /> );
		}
		else if (
			! this.state.dynamic_settings.stripe_api_key ||
			! this.state.unique_settings.currency_code
		) {

			return (
				<div className={ 'tip-jar-wp-container' }>
					<div className={ 'tip-jar-wp-fancy-container tip-jar-wp-payment-view'}>
						<div className="tip-jar-wp-component-box">

							<header className="tip-jar-wp-header" role="banner">
								<h1 className="tip-jar-wp-header-title">Complete Set Up</h1>
								<h2 className={ 'tip-jar-wp-header-subtitle' }>You are almost ready to start accepting payments right here, using Tip Jar WP and Stripe. Click the link below to complete the set-up.</h2>
							</header>
							<div className="tip-jar-wp-payment-form-container">
								<div className={ 'tip-jar-wp-payment-box-view' }>
									<a href={ this.state.dynamic_settings.setup_link.replace(/&amp;/g, '&') }>Complete Tip Jar WP Setup</a>
								</div>
							</div>

						</div>
					</div>
				</div>
			);

		} else {

			var tip_jar_wp_open_link = false;
			var modal_array = false;
			var tip_jar_wp_form = false;

			if ( 'in_modal' === this.state.unique_settings.open_style ) {
				modal_array = {
					tjwp: {}
				}
			} else {
				modal_array = false;
			}

			if ( 'text_link' === this.state.unique_settings.mode ) {
				tip_jar_wp_open_link = (
					<a className={ 'tip-jar-wp-a-tag' } onClick={ this.handle_visual_state_change_click_event.bind( null, {
						payment: {
							form: {}
						}
					}, modal_array ) }>{ this.state.unique_settings.strings.link_text }</a>
				);
			}

			if ( 'button' === this.state.unique_settings.mode ) {
				tip_jar_wp_open_link = (
					<button type="button" className={ 'button tip-jar-wp-button' } onClick={ this.handle_visual_state_change_click_event.bind( null, {
						payment: {
							form: {}
						}
					}, modal_array ) }>{ this.state.unique_settings.strings.link_text }</button>
				);
			}

			tip_jar_wp_form = (
				<div className={ 'tip-jar-wp-container tip-jar-wp-current-view-is-' + Object.keys(this.state.all_current_visual_states)[0] }>

					{ this.render_top_media() }

					<div className={ 'tip-jar-wp-fancy-container tip-jar-wp-payment-view' + this.get_current_view_class( 'payment' ) + this.get_featured_image_class() }>

						<Tip_Jar_WP_Payment_Box
							main_component={ this }
							show_close_button={
								! this.state.editing_mode &&
								(
									'button' === Object.keys(this.state.all_initial_visual_states)[0] ||
									'text_link' === Object.keys(this.state.all_initial_visual_states)[0] ||
									'in_modal' === this.state.unique_settings.open_style
								)
								? true : false
							}
						/>

					</div>

					<div className={ 'tip-jar-wp-fancy-container tip-jar-wp-manage-payments-view' + this.get_current_view_class( 'manage_payments' ) + this.get_featured_image_class() }>

						<Tip_Jar_WP_Manage_Payments
							main_component={ this }
							show_close_button={
								! this.state.editing_mode &&
								(
									'button' === Object.keys(this.state.all_initial_visual_states)[0] ||
									'text_link' === Object.keys(this.state.all_initial_visual_states)[0] ||
									'in_modal' === this.state.unique_settings.open_style
								)
							}
						/>

					</div>

				</div>
			)

			if ( this.state.editing_mode ) {

				// Add the "Display Mode" editor above the tip form
				if ( this.state.show_edit_display_mode ) {
					tip_jar_wp_form = (
						<React.Fragment>
							<TipJarWPEditOpenStyle main_component={ this }/>
							{ tip_jar_wp_form }
						</React.Fragment>
					)
				}

				return tip_jar_wp_form;

			}

			if ( ! this.state.unique_settings.mode || 'form' === this.state.unique_settings.mode ) {
				return tip_jar_wp_form;
			}

			if ( 'in_modal' === this.state.unique_settings.open_style ) {
				return (
					<React.Fragment>
						{
							// The modal used to open inside this component.
							// But now this component sits silently in the footer until triggered by an outside event.
							//tip_jar_wp_open_link
						}
						<Tip_Jar_WP_Modal
							main_component={ this }
							slug={ this.props.form_number }
							modal_contents={ tip_jar_wp_form }
						/>
					</React.Fragment>
				)
			}

			if ( 'in_place' === this.state.unique_settings.open_style ) {

				if (
					'button' === Object.keys(this.state.all_current_visual_states)[0] ||
					'text_link' === Object.keys(this.state.all_current_visual_states)[0]
		 		) {
					return tip_jar_wp_open_link;
				}

				return tip_jar_wp_form;

			}

			// Handle the form showing by default
			return tip_jar_wp_form;
		}
	}
}

window.tip_jar_wp_refresh_a_tipping_element = function tip_jar_wp_refresh_a_tipping_element( tip_jar_wp_element ){

	// Get the form number for this form
	var form_number = tip_jar_wp_element.getAttribute( 'tip-jar-wp-form-number' );

	// Get the unique settings to this shortcode
	var unique_shortcode_settings = JSON.parse( document.getElementById( 'tip-jar-wp-element-unique-vars-json-' + form_number ).textContent );
	var dynamic_shortcode_settings = JSON.parse( document.getElementById( 'tip-jar-wp-element-dynamic-vars-json-' + form_number ).textContent );

	var top_level_visual_state = 'payment';

	var all_default_visual_states = {}

	//If a default visual state is in the URL, it wins
	if ( 'inherit' !== dynamic_shortcode_settings.all_default_visual_states ) {

		// Use the default visual state from the URL
		all_default_visual_states = dynamic_shortcode_settings.all_default_visual_states;

	} else {

		// If the shortcode has a mode attached, use it
		if ( 'form' == unique_shortcode_settings.mode ) {
			all_default_visual_states = {
				payment: {},
			};
		} else {
			all_default_visual_states = {
				[unique_shortcode_settings.mode]: {},
			};
		}

	}

	ReactDOM.render( <Tip_Jar_WP_Form
		key={ 'tip_jar_wp_button_element_' + form_number }
		id={ 'tip_jar_wp_button_element_' + form_number }
		form_number={ form_number }
		all_current_visual_states={ all_default_visual_states }
		frontend_nonces={ tip_jar_wp_js_vars.frontend_nonces }
		unique_settings={ unique_shortcode_settings }
		dynamic_settings={ dynamic_shortcode_settings }
	/>, tip_jar_wp_element );

}

window.tip_jar_wp_refresh_all_tipping_elements = function tip_jar_wp_refresh_all_tipping_elements(){

	var tip_jar_wp_element_exists = document.querySelector( '.tip-jar-wp-element' );

	if ( tip_jar_wp_element_exists ) {

		var tip_jar_wp_elements = document.querySelectorAll( '.tip-jar-wp-element' );

		var counter = 0;

		// Loop through each Tip Jar WP element on the page
		tip_jar_wp_elements.forEach(function( tip_jar_wp_element ) {

			// Get the form number for this form
			var form_number = tip_jar_wp_element.getAttribute( 'tip-jar-wp-form-number' );

			tip_jar_wp_refresh_a_tipping_element( tip_jar_wp_element );
		});
	}
}
tip_jar_wp_refresh_all_tipping_elements();

window.tip_jar_wp_set_modal_to_open = function tip_jar_wp_set_modal_to_open( form_number ) {

	var tip_jar_wp_element_exists = document.querySelector( '.tip-jar-wp-element' );

	// Set all tip jar forms on the page to have their modals closed, then we'll open this one.
	if ( tip_jar_wp_element_exists ) {

		var tip_jar_wp_elements = document.querySelectorAll( '.tip-jar-wp-element' );

		var counter = 0;

		// Loop through each Tip Jar WP element on the page
		tip_jar_wp_elements.forEach(function( tip_jar_wp_element ) {

			// Get the form number for this form
			var form_number = tip_jar_wp_element.getAttribute( 'tip-jar-wp-form-number' );

			// Get the unique settings to this shortcode
			var dynamic_shortcode_settings_container = document.getElementById( 'tip-jar-wp-element-dynamic-vars-json-' + form_number );
			var dynamic_shortcode_settings = JSON.parse( dynamic_shortcode_settings_container.textContent );

			// Set the status of the modal to be closed
			dynamic_shortcode_settings.modal_visual_state = {};

			// Rewrite the JSON to the DOM
			dynamic_shortcode_settings_container.textContent = JSON.stringify( dynamic_shortcode_settings );
		} );

		// Refresh all React payment form components on the page
		tip_jar_wp_refresh_all_tipping_elements();

	}

	var element_holding_settings = document.getElementById( 'tip-jar-wp-element-dynamic-vars-json-' + form_number );

	// Get the unique settings to this shortcode
	var dynamic_shortcode_settings = JSON.parse( element_holding_settings.innerHTML );

	// Set the state of the form to be "payment/form"
	dynamic_shortcode_settings.all_default_visual_states = {
		payment: {
			form: {}
		}
	};

	// Set the status of the modal to be open
	dynamic_shortcode_settings.modal_visual_state = {
		[form_number]: {}
	};

	// Rewrite the JSON to the DOM
	element_holding_settings.textContent = JSON.stringify( dynamic_shortcode_settings );

	var tip_jar_wp_element = document.getElementById( 'tip-jar-wp-element-' + form_number );

	// Refresh all React payment form components on the page
	tip_jar_wp_refresh_a_tipping_element( tip_jar_wp_element );
}
