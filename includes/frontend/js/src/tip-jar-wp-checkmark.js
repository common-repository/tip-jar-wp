window.Tip_Jar_WP_Checkmark = class Tip_Jar_WP_Checkmark extends React.Component{
    render(){
        return(
            <svg className="tip-jar-wp-checkmark-svg" xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 200 200">
                <g fill="none" fillRule="evenodd">
                    <circle className="tip-jar-wp-checkmark--circle" cx="100" cy="100" r="84.615" fill="#4BB543"/>
                    <polyline className="tip-jar-wp-checkmark--check" stroke="#FFF"  points="76.923 130.769 123.077 130.769 123.077 38.462" transform="rotate(42 100 84.615)"/>
                </g>
            </svg>
        )
    }
}
export default Tip_Jar_WP_Checkmark;
