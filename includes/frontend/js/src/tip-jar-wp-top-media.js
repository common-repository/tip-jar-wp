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

window.Tip_Jar_WP_Top_Media = class Tip_Jar_WP_Top_Media extends React.Component {

	constructor( props ){
		super(props);

		this.state = {
			top_media_type: 'featured_image', // none, featured_image, featured_embed
			is_focused: false,
			featured_embed: '',
			fetched_oembed_html: '',
			fetching_oembed: true,
		}
	}

	componentDidMount() {

		if ( this.props.main_component.state.unique_settings.top_media_type ) {
			this.setState( {
				top_media_type: this.props.main_component.state.unique_settings.top_media_type
			} );
		}

		if ( this.props.main_component.state.unique_settings.featured_embed ) {
			this.setState( {
				featured_embed: this.props.main_component.state.unique_settings.featured_embed,
				fetched_oembed_html: this.props.main_component.state.unique_settings.fetched_oembed_html,
			}, () => {
				if ( ! this.state.fetched_oembed_html ) {
					this.get_oembed();
				} else {
					this.setState( {
						fetching_oembed: false
					} );
				}
			} );
		}
	}

	get_oembed() {

		this.setState( {
			fetching_oembed: true,
		} );

		return new Promise( (resolve, reject) => {

			var postData = new FormData();
			postData.append('action', 'tip_jar_wp_get_oembed' );
			postData.append('tip_jar_wp_oembed_string_source', decodeURI( this.state.featured_embed ) );
			postData.append('tip_jar_wp_get_oembed_nonce', this.props.main_component.state.frontend_nonces.get_oembed_nonce);

			// Get the arrangements defined by the paramaters in the state
			fetch( tip_jar_wp_js_vars.ajaxurl + '?tip_jar_wp_get_oembed', {
				method: "POST",
				mode: "same-origin",
				credentials: "same-origin",
				body: postData
			} ).then(
				( response ) => {
					if ( response.status !== 200 ) {

						this.setState( {
							fetched_oembed_html: null,
							fetching_oembed: false,
						} );

						console.log('Looks like there was a problem. Status Code: ' + response.status);

						reject();
						return;
					}

					// Examine the text in the response
					response.json().then(
						( data ) => {
							if ( data.success ) {

								this.setState( {
									fetched_oembed_html: data.oembed_html,
									fetching_oembed: false,
								}, () => {
									resolve();
									return;
								} );

							} else {

								// Remove the user ID from the main state and set the state to be login
								this.setState( {
									fetched_oembed_html:'',
									fetching_oembed: false,
								}, () => {
									resolve();
									return;
								} );

							}
						}
					).catch(
						( err ) => {
							console.log('Fetch Error: ', err);
							this.setState( {
								fetched_oembed_html:'',
								fetching_oembed: false,
							}, () => {
								reject();
								return;
							} );
						}
					);
				}
			).catch(
				function( err ) {
					console.log('Fetch Error :-S', err);
					tthis.setState( {
						fetched_oembed_html:'',
						fetching_oembed: false,
					}, () => {
						reject();
						return;
					} );
				}
			);

		});

	}

	set_focus( should_be_focused, context, event ) {
		event.preventDefault();
		this.setState( {
			is_focused: should_be_focused
		}, () => {

			if ( ! should_be_focused ) {
				this.get_oembed().then( () => {
					this.props.main_component.setState( {
						top_media_editor_focused: should_be_focused
					} );
				} );
			} else {
				this.props.main_component.setState( {
					top_media_editor_focused: should_be_focused
				} );
			}

		});
	}

	handle_top_media_type_change( event ) {
		this.setState( {
			top_media_type: event.target.value
		}, () => {
			tip_jar_wp_pass_value_to_block( this.props.main_component, 'top_media_type', this.state.top_media_type, false );
		} );
	}

	handle_featured_embed_change( event ) {
		this.setState( {
			featured_embed: encodeURI( event.target.value )
		}, () => {
			tip_jar_wp_pass_value_to_block( this.props.main_component, 'featured_embed', this.state.featured_embed, false );
		} );
	}

	render_edit_and_done_buttons() {
		if ( ! this.props.main_component.state.editing_mode ) {
			return false;
		}

		return(
			<div className="tip-jar-wp-edit-button-container tip-jar-wp-edit-button-container-absolute">
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
			</div>
		);
	}

	render_none() {
		if ( this.props.main_component.state.editing_mode ) {
			return(
				<div className="tip-jar-wp-logo">
					<div className="tip-jar-wp-header-logo-container">
						<div className="tip-jar-wp-header-logo-inner-bevel"></div>
						<div className="tip-jar-wp-header-logo-img"></div>
						{ this.render_edit_and_done_buttons() }
					</div>
				</div>
			);
		} else {
			return '';
		}
	}

	render_featured_embed() {

		if ( this.state.fetching_oembed ) {
			return(
				<React.Fragment>
					<Tip_Jar_WP_Spinner />
				</React.Fragment>
			);
		}

		if ( ! this.state.featured_embed ) {
			return this.render_edit_and_done_buttons();
		}

		// Close the embed if the modal is closed.
		if ( ! this.props.main_component.state.editing_mode ) {
			if ( 'form' !== this.props.main_component.state.unique_settings.mode && 'in_modal' === this.props.main_component.state.unique_settings.open_style ) {
				if ( 0 === Object.entries(this.props.main_component.state.modal_visual_state).length ) {
					return '';
				}
			}
		}

		var embed_attributes;
		var width;
		var height;
		var src;
		var use_padding_ratio = true;

		// If we don't need to go to the server for this oembed, don't!
		embed_attributes = decodeURI( this.state.featured_embed ).split(" ");

		// If we do need to go to the server to get the oembed, do!
		if ( ! embed_attributes[0].includes("iframe") ) {
			embed_attributes = this.state.fetched_oembed_html.split(" ");
		}

		if ( ! embed_attributes[0].includes("iframe") ) {
			return this.render_edit_and_done_buttons();
		}

		if ( embed_attributes[0].includes("iframe") ) {
			embed_attributes.forEach((attribute) => {

				// If this is the width attribute, get its value.
				if ( attribute.includes("width=") ) {
					width = attribute.split( '"' );
					if ( attribute.includes("%") ) {
						use_padding_ratio = false;
						width = width[1] ? parseInt( width[1], 10 ) + '%' : false;
					} else {
						width = width[1] ? parseInt( width[1], 10 ) : false;
					}
				}

				// If this is the height attribute, get its value.
				if ( attribute.includes("height=") ) {
					height = attribute.split( '"' );
					if ( attribute.includes("%") ) {
						use_padding_ratio = false;
						height = height[1] ? parseInt( height[1], 10 ) + '%' : false;
					} else {
						height = height[1] ? parseInt( height[1], 10 ) : false;
					}
				}

				// If this is the src attribute, get its value.
				if ( attribute.includes("src=") ) {
					src = attribute.split( '"' );
					src = src[1] ? src[1] : false;
				}

			});
		}

		if ( ! src ) {
			return this.render_edit_and_done_buttons();
		}

		if ( use_padding_ratio ) {
			// Get the width-to-height ratio of the embedded content.
			var width_height_ratio = ( width && height ? height/width : .56 )  * 100; // Default to 16x9 (9/16 = .56. We then remove the decimal and set padding-top to 56%);
			var padding_top = width_height_ratio.toString() + '%';
			width = 'inherit';
			height = 'inherit';
		} else {
			var padding_top = '0';
			width = width;
			height = height;
		}

		return(
			<React.Fragment>
				<div
					className={ 'tip-jar-wp-featured-media-container' }
				>
					<div
						style={ {
							position: 'relative',
							width: '100%',
							height: height,
							margin: '0px auto',
						} }
					>
						{ this.render_edit_and_done_buttons() }
						<div
							className={ 'tip-jar-wp-featured-media' }
							style={ {
								padding: padding_top + ' 0 0 0',
								width: '100%',
								height: '100%',
							} }
						>
							<iframe
								src={ src + '?test&rel=0' }
								allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
								allowFullScreen
							/>
						</div>
					</div>
				</div>
			</React.Fragment>
		);

	}

	render_featured_image() {

		if ( this.props.main_component.state.unique_settings.featured_image_url ) {
			return (
				<React.Fragment>
					<div className="tip-jar-wp-logo">
						<div className="tip-jar-wp-header-logo-container">
							<div className="tip-jar-wp-header-logo-inner-bevel"></div>
							<div className="tip-jar-wp-header-logo-img" style={ {
								backgroundImage: 'url(' + this.props.main_component.state.unique_settings.featured_image_url + ')',
							} }></div>
							{ this.render_edit_and_done_buttons() }
						</div>
					</div>
				</React.Fragment>
			)
		} else {
			if ( this.props.main_component.state.editing_mode ) {
				return(
					<div className="tip-jar-wp-logo">
						<div className="tip-jar-wp-header-logo-container">
							<div className="tip-jar-wp-header-logo-inner-bevel"></div>
							<div className="tip-jar-wp-header-logo-img" style={ {
								backgroundImage: 'url(' + this.props.main_component.state.unique_settings.featured_image_url + ')',
							} }></div>
							{ this.render_edit_and_done_buttons() }
						</div>
					</div>
				)
			}
		}
	}

	maybe_render_featured_embed_input() {
		if ( 'featured_embed' !== this.state.top_media_type ) {
			return '';
		}
		return(
			<React.Fragment>
				<div>
					<div className="tip-jar-wp-edit-container-admin-only-setting-description">
						Copy and paste the embed code below:
					</div>
					<textarea value={ decodeURI( this.state.featured_embed ) } onChange={ this.handle_featured_embed_change.bind( this ) } />
				</div>
			</React.Fragment>
		);
	}

	maybe_render_featured_image_selector() {
		if ( 'featured_image' !== this.state.top_media_type ) {
			return '';
		}
		return(
			<div className="tip-jar-wp-logo">
				<div className="tip-jar-wp-header-logo-container">
					<div className="tip-jar-wp-header-logo-inner-bevel"></div>
					<div className="tip-jar-wp-header-logo-img" style={ {
						backgroundImage: 'url(' + this.props.main_component.state.unique_settings.featured_image_url + ')',
					} }></div>
				</div>
				<TipJarWPEditFileButton
					main_component={ this.props.main_component }
					editing_key='featured_image_url'
					editing_string={ tip_jar_wp_editing_strings.choose_image }
				/>
			</div>
		);
	}

	render_editing_focused_mode() {
		return(
			<div className="tip-jar-wp-edit-container-admin-only tip-jar-wp-top-media-type-editor">
				<div className="tip-jar-wp-edit-container-admin-only-header">
					<span className="tip-jar-wp-edit-container-admin-only-title">{ tip_jar_wp_editing_strings.media_above_payment_form }</span>
				</div>
				<div className="tip-jar-wp-edit-container-admin-only-body">
					<div className="tip-jar-wp-edit-container-admin-only-setting">
						<div className="tip-jar-wp-edit-container-admin-only-setting-description">
							{ tip_jar_wp_editing_strings.description_top_media_type }
						</div>
						<div className="tip-jar-wp-edit-container-admin-only-setting-value">
							<select onChange={ this.handle_top_media_type_change.bind( this ) } value={ this.state.top_media_type }>
								<option value="featured_image">A logo/image</option>
								<option value="featured_embed">An embed (YouTube, Soundcloud, etc)</option>
								<option value="none">Nothing</option>
							</select>
							{ this.maybe_render_featured_image_selector() }
							{ this.maybe_render_featured_embed_input() }
						</div>
					</div>
					<div className="tip-jar-wp-edit-container-admin-only-setting">
						<button type="button" className="button" onClick={ this.set_focus.bind( this, false, 'view' ) }>{ tip_jar_wp_editing_strings.view }</button>
					</div>
				</div>
			</div>
		);
	}

	render() {

		if ( this.state.is_focused ) {
			return(
				<React.Fragment>
					{ this.render_editing_focused_mode() }
				</React.Fragment>
			)
		} else {
			if ( 'none' === this.state.top_media_type ) {
				return this.render_none();
			}

			if ( 'featured_image' === this.state.top_media_type ) {
				return(
					<React.Fragment>
						{ this.render_featured_image() }
					</React.Fragment>
				)
			}

			if ( 'featured_embed' === this.state.top_media_type ) {
				return(
					<React.Fragment>
						{ this.render_featured_embed() }
					</React.Fragment>
				)
			}
		}
	}
}
