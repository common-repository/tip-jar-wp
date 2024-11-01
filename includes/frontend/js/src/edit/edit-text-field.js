window.TipJarWPContentEditable = class TipJarWPContentEditable extends React.Component{

	constructor( props ){
		super(props);

		this.state = {
			input_value: null,
			is_focused: false
		};

		this.textInput = React.createRef();
	}

	componentDidMount() {
		this.setState({
			input_value: this.props.html_tag_contents
		});
	}

	handle_change( event ) {

		this.setState( {
			input_value: event.target.value
		} );

		tip_jar_wp_pass_value_to_block( this.props.main_component, this.props.editing_key, event.target.value, true );
	}

	set_focus( should_be_focused, context, event ) {
		event.preventDefault();
		this.setState( {
			is_focused: should_be_focused
		});
	}

	handleBlur( event ) {
		this.setState( {
			is_focused: false
		});
	}

	render_edit_and_done_buttons() {
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
			</div>
		);
	}

	get_in_focus_class() {
		if ( this.state.is_focused ) {
			return ' tip-jar-wp-edit-area-in-focus';
		} else {
			return ' tip-jar-wp-edit-area-not-in-focus';
		}
	}

	render() {

		var HtmlTag = this.props.html_tag;

		// If we are in editing mode...
		if ( this.props.main_component.state.editing_mode ) {

			if ( this.state.is_focused ) {
				return (
					<div>
						<div className={ 'tip-jar-wp-edit-container' + this.get_in_focus_class() }>
							{ this.render_edit_and_done_buttons() }
							<textarea
								type="text"
								{ ...this.props.html_tag_attributes }
								onChange={ this.handle_change.bind( this ) }
								onBlur={ this.handleBlur.bind( this ) }
								value={ this.state.input_value }
							/>
						</div>
					</div>
				)
			} else {
				return(
					<div>
						<div className="tip-jar-wp-edit-container">
							{ this.render_edit_and_done_buttons() }
							<HtmlTag
								{ ...this.props.html_tag_attributes }
								onChange={ this.handle_change.bind( this ) }
								onBlur={ this.handleBlur.bind( this ) }
							>
								{ (() => {
									if ( this.props.html_tag_contents ) {
										return this.props.html_tag_contents;
									} else {
										return this.props.instructional_html_tag_contents;
									}
								})() }
							</HtmlTag>
						</div>
					</div>
				);
			}
			// If we are not in editing mode...
		} else {
			if ( this.props.html_tag_contents ) {
				return (
					<HtmlTag { ...this.props.html_tag_attributes }>
						{
							this.props.html_tag_contents
						}
					</HtmlTag>
				)
			} else {
				return '';
			}
		}
	}

}
export default TipJarWPContentEditable;
