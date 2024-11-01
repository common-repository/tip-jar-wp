window.TipJarWPEditButton = class TipJarWPEditButton extends React.Component{

	constructor( props ){
		super(props);

		this.state = {
		};

	}

	maybe_render_editing_lightbox() {

		var EditingComponent = eval( this.props.component );

		return(
			<Tip_Jar_WP_Modal
				main_component={ this.props.main_component }
				slug={ this.props.editing_key }
				modal_contents={
					<EditingComponent
						main_component={ this.props.main_component }
						editing_key={ this.props.editing_key }
					/>
				}
			/>
		);
	}

	maybe_render_edit_button() {
		if ( this.props.main_component.state.editing_mode ) {
			return(
				<React.Fragment>
					<div className="tip-jar-wp-edit-button-container">
						<button
							type="button"
							className="button tip-jar-wp-edit-button"
							onClick={ this.props.main_component.handle_visual_state_change_click_event.bind( null, false, {
								[this.props.editing_key]: {}
							} ) }
						>
						{
							tip_jar_wp_editing_strings.edit
						}
						</button>
					</div>
					{ this.maybe_render_editing_lightbox() }
				</React.Fragment>
			)
		} else {
			return '';
		}
	}

	render() {
		return (
			this.maybe_render_edit_button()
		)
	}

}
export default TipJarWPEditButton;
