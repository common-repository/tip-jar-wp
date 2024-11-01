window.Tip_Jar_WP_Spinner = class Tip_Jar_WP_Spinner extends React.Component{

		get_color_mode_class() {

			if ( this.props.color_mode ) {
				return ( ' ' + this.props.color_mode );
			} else {
				return '';
			}
		}

	  render(){
        return(
					<div className={ "tip-jar-wp-spinner-container" }>
						<div className={ "tip-jar-wp-spinner" + this.get_color_mode_class() }>
              <div className="tip-jar-wp-double-bounce1"></div>
              <div className="tip-jar-wp-double-bounce2"></div>
            </div>
					</div>
        )
    }
}
export default Tip_Jar_WP_Spinner;
