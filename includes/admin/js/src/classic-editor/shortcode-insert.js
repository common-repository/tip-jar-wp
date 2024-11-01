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

window.Tip_Jar_WP_Classic_Editor_Shortcode_Inserter = class Tip_Jar_WP_Classic_Editor_Shortcode_Inserter extends React.Component {

	constructor( props ){
		super(props);

		this.state = {
			default_form_id: null,
			form_id: null,
			form_json: null,
			form_dynamic_settings: tip_jar_wp_classic_editor_vars.tip_jar_wp_dynamic_settings,
			default_modal_visual_state: {
				'tjwp_classic_editor': {}
			},
			insert_to_tinymce: true,
			remount: false,
		};

	}

	componentDidMount() {
		this.setState( {
			default_form_id: this.props.default_form_id,
			form_id: this.props.default_form_id,
			insert_to_tinymce: this.props.insert_to_tinymce,
		}, () => {
			// If there's no form ID upon mount, this is a new Tip Jar. Set the json to be the default values.
			if ( ! this.state.form_id ) {
				this.setState( {
					form_json: tip_jar_wp_classic_editor_vars.tip_jar_wp_block_default_json
				} );
			}
		} );
	}

	componentDidUpdate() {

		// If the default form id in the props is different than the default form id in the state, fetch that new form ID from the server.
		if ( this.state.default_form_id !== this.props.default_form_id ) {

			this.setState( {
				remount: true,
				default_form_id: this.props.default_form_id,
				form_id: this.props.default_form_id,
				insert_to_tinymce: this.props.insert_to_tinymce,
			}, () => {

				if ( this.state.form_id ) {
					this.get_form_json_from_server();
				} else {

					// If there's no form ID, this is a new form creation.
					this.setState( {
						form_json: tip_jar_wp_classic_editor_vars.tip_jar_wp_block_default_json
					}, () => {
						this.setState( {
							remount: false
						} );
					} );
				}

			} );

		}

		// If the default modal setting for this changed, it needs to be reset and updated.
		if ( this.props.default_modal_visual_state !== this.state.default_modal_visual_state ) {
			this.setState( {
				default_modal_visual_state: this.props.default_modal_visual_state
			}, () => {
				this.set_all_current_visual_states( false, this.state.default_modal_visual_state );
			} );
		}

	}

	// This is a simplified version of the set_all_current_visual_states method from the main Tip Form  component. This just doesn't do URL updating.
	set_all_current_visual_states( new_state = false, new_modal_state = false ) {

		var in_initial_state = false;

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
		} );
	}

	// This will send a fetch call to the server to get the json for a form from the forms table.
	get_form_json_from_server() {

		return new Promise( (resolve, reject) => {

			var postData = new FormData();
			postData.append('action', 'tip_jar_wp_get_form' );
			postData.append('form_id', this.state.form_id );
			postData.append('tip_jar_wp_get_form_nonce', tip_jar_wp_classic_editor_vars.get_form_nonce);

			fetch( tip_jar_wp_classic_editor_vars.get_form_endpoint, {
				method: "POST",
				mode: "same-origin",
				credentials: "same-origin",
				body: postData
			} ).then(
				( response ) => {
					if ( response.status !== 200 ) {
						console.log('Looks like there was a problem. Status Code: ' + response.status);
						reject('unable_to_get_form_data');
						return;
					}

					// Examine the text in the response
					response.json().then(
						( data ) => {
							if ( data.success ) {

								this.setState( {
									form_json: JSON.parse( data.json ),
									remount: true
								}, () => {
									this.setState( {
										remount: false
									}, () => {
										resolve( data.form_id );
										return data.form_id;
									} );
								} );

							} else {
								console.log('Looks like there was a problem: ' + data);
								reject('unable_to_get_form_data');
								return;
							}
						}
					).catch(
						( err ) => {
							console.log('Fetch Error: ', err);
							reject('unable_to_get_form_data');
							return;
						}
					);
				}
			).catch(
				( err ) => {
					console.log('Fetch Error: ', err);
					reject('unable_to_get_form_data');
					return;
				}
			);

		} );

	}

	// This will send a fetch call to the server to generate or update a row in the forms table.
	create_or_update_form() {

		return new Promise( (resolve, reject) => {

			var postData = new FormData();
			postData.append('action', 'tip_jar_wp_create_or_update_form' );
			postData.append('form_id', this.state.form_id );
			postData.append('state', JSON.stringify( this.state.form_json ) );
			postData.append('tip_jar_wp_create_or_update_form_nonce', tip_jar_wp_classic_editor_vars.create_or_update_form_nonce);

			fetch( tip_jar_wp_classic_editor_vars.create_or_update_form_endpoint, {
				method: "POST",
				mode: "same-origin",
				credentials: "same-origin",
				body: postData
			} ).then(
				( response ) => {
					if ( response.status !== 200 ) {
						console.log('Looks like there was a problem. Status Code: ' + response.status);
						reject('unable_to_create_form');
						return;
					}

					// Examine the text in the response
					response.json().then(
						( data ) => {
							if ( data.success ) {

								this.setState( {
									form_id: data.form_id
								}, () => {
									resolve( data.form_id );
									return data.form_id;
								} );

							} else {
								console.log('Looks like there was a problem: ' + data);
								reject('unable_to_create_form');
								return;
							}
						}
					).catch(
						( err ) => {
							console.log('Fetch Error: ', err);
							reject('unable_to_create_form');
							return;
						}
					);
				}
			).catch(
				( err ) => {
					console.log('Fetch Error: ', err);
					reject('unable_to_create_form');
					return;
				}
			);

		} );

	}

	onChangeHandler( new_form_json, use_typing_delay = false ) {

		return new Promise( (resolve, reject) => {
			// Temporarily store the value in the state of this component while we wait for the typing to stop.
			this.setState( {
				form_json: new_form_json
			}, () => {

				if ( use_typing_delay ) {
					// Set up a delay which waits to save the tip until .5 seconds after they stop typing.
					if( this.keypress_delay ) {
						// Clear the keypress delay if the user just typed
						clearTimeout( this.keypress_delay );
						this.keypress_delay = null;
					}

					// (Re)-Set up the save_note_with_tip to fire in 500ms
					this.keypress_delay = setTimeout( () => {
						clearTimeout( this.keypress_delay );

						// Update the form data in the tip_jar_wp_forms custom table.
						this.create_or_update_form().then( () => {
							resolve();
							return;
						});

					}, 1000);
				} else {
					// Update the form data in the tip_jar_wp_forms custom table.
					this.create_or_update_form().then( () => {
						resolve();
						return;
					});
				}

			});
		});
	};

	sendShortcodeToEditor() {

		this.create_or_update_form().then( () => {

			// If we are only editing a shortcode, don't insert another shortcode into TinyMce.
			if ( ! this.state.insert_to_tinymce ) {
				return false;
			} else {
				window.send_to_editor( '[tipjarwp id="' + this.state.form_id + '"]' );
			}

		} );

		// Close the lightbox.
		this.close_lightbox();

	}

	close_lightbox() {
		// Close the lightbox.
		this.set_all_current_visual_states( {}, {} );
	}

	render() {

		if ( this.state.remount || ! this.state.form_dynamic_settings || ! this.state.form_json ) {
			return '';
		} else {
			var form_number = 1;
			return (
				<Tip_Jar_WP_Modal
					main_component={ this }
					slug={ 'tjwp_classic_editor' }
					modal_contents={
						<div className="tip-jar-wp-classic-editor-shortcode-container">
							<div className="tip-jar-wp-shortcode-insert-area">
								<div className="tip-jar-wp-edit-container-admin-only">
									<div className="tip-jar-wp-edit-container-admin-only-header">
										<span className="tip-jar-wp-edit-container-admin-only-title">{ tip_jar_wp_editing_strings.insert_shortcode_area_title }</span>
									</div>
									<div className="tip-jar-wp-edit-container-admin-only-body">
										<button className="button" onClick={ this.sendShortcodeToEditor.bind( this ) }>{
											(() => {
												if ( this.state.insert_to_tinymce ) {
													return ( tip_jar_wp_editing_strings.insert_shortcode );
												} else {
													return ( tip_jar_wp_editing_strings.update_shortcode );
												}
											})()}</button>
										<button className="button tip-jar-wp-cancel-shortcode" onClick={ this.close_lightbox.bind( this ) }>{ tip_jar_wp_editing_strings.cancel_shortcode }</button>
									</div>
								</div>
							</div>
							<span className="tip-jar-wp-element">
								<Tip_Jar_WP_Form
									key={ 'tip_jar_wp_button_element_' + form_number }
									id={ 'tip_jar_wp_button_element_' + form_number }
									form_number={ form_number }
									editing_mode={ true }
									show_edit_display_mode={ true }
									editing_parent_component={ this }
									all_current_visual_states={
										{
											payment: {}
										}
									}
									frontend_nonces={ tip_jar_wp_js_vars.frontend_nonces }
									unique_settings={ this.state.form_json }
									dynamic_settings={ this.state.form_dynamic_settings }
								/>
							</span>
						</div>
					}
				/>
			)
		}
	}
}

window.tip_jar_wp_refresh_classic_editor_shortcode_inserter = function tip_jar_wp_refresh_classic_editor_shortcode_inserter( form_id, default_modal_visual_state = {}, insert_to_tinymce = true ) {
	var lightbox_container = document.getElementById( 'tip_jar_wp_classic_editor_lightbox' );

	ReactDOM.render( <Tip_Jar_WP_Classic_Editor_Shortcode_Inserter
		default_form_id={ form_id }
		default_modal_visual_state={ default_modal_visual_state }
		insert_to_tinymce={ insert_to_tinymce }
	/>, lightbox_container );

	return false;

}
tip_jar_wp_refresh_classic_editor_shortcode_inserter();


window.tip_jar_wp_set_shortcode_insert_modal_to_open = function tip_jar_wp_set_shortcode_insert_modal_to_open( form_number = null, insert_to_tinymce = true ) {

	//Open the Shortcode inserter lightbox
	tip_jar_wp_refresh_classic_editor_shortcode_inserter(
		form_number,
		{
		'tjwp_classic_editor': {}
		},
		insert_to_tinymce
	);
}

// Move the media buttons into the right position.
// We have to do this with javascript because the WordPress media_buttons hook is unable to scan the content of the wp_editor it is for.
// This allows us to scan the contents for the tipjarwp shortcode, and output edit buttons dynamically, if the shortcode actually exists.
window.tip_jar_wp_move_shortcode_edit_media_buttons = function tip_jar_wp_move_shortcode_edit_media_buttons() {

	var tip_jar_wp_media_buttons_exist = document.querySelector( '.tip-jar-wp-media-buttons' );

	// Set all tip jar forms on the page to have their modals closed, then we'll open this one.
	if ( tip_jar_wp_media_buttons_exist ) {

		var tip_jar_wp_media_buttons_containers = document.querySelectorAll( '.tip-jar-wp-media-buttons' );

		// Loop through each Tip Jar WP Shortcode Media Buttons Container...
		tip_jar_wp_media_buttons_containers.forEach(function( tip_jar_wp_media_buttons_container ) {

			// Grab the contents of this container, and move them to the previous sibling's
			tip_jar_wp_media_buttons_container.previousElementSibling.innerHTML = tip_jar_wp_media_buttons_container.previousElementSibling.innerHTML + tip_jar_wp_media_buttons_container.innerHTML;

			// Remove the originals, which were in the wrong spot.
			tip_jar_wp_media_buttons_container.remove();
		} );

	}
}
//tip_jar_wp_move_shortcode_edit_media_buttons();
