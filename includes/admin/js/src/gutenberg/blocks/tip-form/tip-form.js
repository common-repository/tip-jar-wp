/*
* Tip Jar WP
* https://www.tipjarwp.com
*
* Licensed under the GPL license.
*
* Author: Tip Jar WP
* Version: 1.0
* Date: January 11, 2018
*/

// This component acts as an in-between, from our actual custom react component(s), to Gutenberg blocks.
// It recieves a JS object, which it passes to the block's attributes.
// From there, the attributes are saved to the block.
class AttributeReciever extends React.Component {

	constructor( props ){
		super(props);

		this.keypress_delay = null;
		this.onChangeHandler = this.onChangeHandler.bind( this );
	}

	onChangeHandler( new_state, use_typing_delay = false ) {

		return new Promise( (resolve, reject) => {

			// Temporarily store the value in the state of this component while we wait for the typing to stop.
			this.setState( new_state, () => {

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

						// Update the attributes of the the Gutenberg Block.
						this.props.changeAttributes( new_state ).then( () => {
							resolve();
							return;
						} );

					}, 1000);
				} else {
					// Update the attributes of the the Gutenberg Block.
					this.props.changeAttributes( new_state ).then( () => {
						resolve();
						return;
					} );
				}

			});
		});

	};

	render() {

		var form_number = 1;

		return (
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
					unique_settings={ this.props.attributes }
					dynamic_settings={ tip_jar_wp_gutenberg_vars.tip_jar_wp_dynamic_settings }
				/>
			</span>
		);

	};
}

export default AttributeReciever;
