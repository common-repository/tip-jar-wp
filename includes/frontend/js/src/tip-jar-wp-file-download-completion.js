window.Tip_Jar_WP_File_Download_Completion = class Tip_Jar_WP_File_Download_Completion extends React.Component {

	constructor( props ){
		super(props);

		this.state = {
			delivery_mode: null, // check_your_email, download
			instructions_title: null,
			instructions_description: null,
		};

	}

	componentDidMount() {
		this.get_file_download();
	}

	get_file_download() {

		var is_ios_Device = !!navigator.platform.match(/iPhone|iPod|iPad/);

		if ( is_ios_Device ) {
			this.setState( {
				delivery_mode: 'download_file',
				instructions_title: 'Your device does not allow file downloads.',
				instructions_description: 'File could not be downloaded on this device. Please try again on a device that allows downloading files.',
			} );

			return false;
		}

		var this_component = this;
		var transaction_id = this.props.main_component.state.current_transaction_info ? this.props.main_component.state.current_transaction_info.transaction_id : null;
		var form_id = transaction_id ? null : this_component.props.main_component.state.unique_settings.id;
		var endpoint = null;

		// Transaction File Download Endpoint.
		if ( transaction_id ) {

			endpoint = 'tip_jar_wp_get_transaction_file_download_url';

			// Use ajax to do the stripe transaction on the server using this data.
			var postData = new FormData();
			postData.append('action', endpoint );
			postData.append('tip_jar_wp_transaction_id', transaction_id );
			postData.append('tip_jar_wp_session_id', this.props.main_component.state.session_id ? this.props.main_component.state.session_id : 0);
			postData.append('tip_jar_wp_user_id', this.props.main_component.state.user_id);
			postData.append('tip_jar_wp_file_download_nonce', this_component.props.main_component.state.frontend_nonces.file_download_nonce);

			// Free File Download endpoint.
		} else {

			endpoint = 'tip_jar_wp_get_free_file_download_url';

			// Use ajax to do the stripe transaction on the server using this data.
			var postData = new FormData();
			postData.append('action', endpoint );
			postData.append('tip_jar_wp_email', this_component.props.main_component.state.form_email_value);
			postData.append('tip_jar_wp_page_url', this_component.props.main_component.state.single_page_app_base_url);
			postData.append('tip_jar_wp_form_id', form_id );
			postData.append('tip_jar_wp_file_download_nonce', this_component.props.main_component.state.frontend_nonces.file_download_nonce);
		}

		// Do the file downnload on the server, and get the file URL in the fetched response.
		fetch( tip_jar_wp_js_vars.ajaxurl + '?' + endpoint, {
			method: "POST",
			mode: "same-origin",
			credentials: "same-origin",
			body: postData
		} ).then(
			function( response ) {
				if ( response.status !== 200 ) {

					this_component.setState( {
						delivery_mode: 'failed',
						message: response.status
					} );

					console.log('Looks like there was a problem. Status Code: ' + response.status);

					return;
				}

				// Examine the text in the response
				response.json().then(
					function( data ) {
						if ( data.success ) {

							if ( 'download_file' === data.success_code ) {

								this_component.setState( {
									delivery_mode: 'download_file',
									instructions_title: data.instructions_title,
									instructions_description: data.instructions_description,
									message: data.details
								}, () => {
									// Redirect the user to the file being downloaded.
									window.location = data.url;
								} );
							}

							if ( 'check_your_email' === data.success_code ) {
								// Redirect the user to the file being downloaded.
								this_component.setState( {
									delivery_mode: 'check_your_email',
									instructions_title: data.instructions_title,
									instructions_description: data.instructions_description,
									message: data.details,
								} );
							}

						} else {

							this_component.setState( {
								delivery_mode: 'failed',
								message: data.details
							} );
						}
					}
				).catch(
					function( err ) {

						this_component.setState( {
							delivery_mode: 'failed',
							message: err
						} );

						console.log('Fetch Error: ', err);
					}
				);
			}
		).catch(
			function( err ) {

				this_component.setState( {
					delivery_mode: 'failed',
					message: err
				} );

				console.log('Fetch Error: ', err);
			}
		);

	}

	handleFileDownloadClick( event ){

		event.preventDefault();
		this.get_file_download();

	}

	render() {

		if ( ! this.state.delivery_mode ) {
			return 'loading...';
		}

		if ( 'failed' === this.state.delivery_mode ) {
			return(
				<div className={ 'tip-jar-wp-payment-box-view tip-jar-wp-payment-confirmation-view' }>
					<div>
						No download found.
					</div>
					<button
						type="button"
						onClick={ this.props.main_component.set_all_current_visual_states.bind( null, {
							payment: {}
						}, false ) }
					>Back to payment form</button>
				</div>
			)
		}

		return (
			<div>
				<div className="tip-jar-wp-file-download-instructions-title">
					{ this.state.instructions_title }
				</div>
				<div className="tip-jar-wp-file-download-instructions-description">
					{ this.state.instructions_description }
				</div>

				{(() => {
					if ( 'check_your_email' === this.state.delivery_mode ) {
						return (
							<div>
								{ this.state.message }
							</div>
						)
					} else {
						return (
							<div className='tip-jar-wp-receipt-action-button'>
								<button
									type="button"
									onClick={ this.handleFileDownloadClick.bind( this ) }
									className={ 'tip-jar-wp-pay-button' }
								>
									{ this.props.main_component.state.unique_settings.strings.download_file_button_text }
								</button>
							</div>
						)
					}
				})()}
			</div>
		)

		return this.state.mode;
	}
}
