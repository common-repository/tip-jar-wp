<?php
/**
 * Tip Jar WP
 *
 * @package     Tip Jar WP
 * @subpackage  Classes/Tip Jar WP
 * @copyright   Copyright (c) 2018, Tip Jar WP
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create the Tip Jar WP Manager User role and capabilities.
 *
 * @since  1.0.1.3
 * @return void
 */
function tip_jar_wp_user_role_and_capability_setup() {

	// Check if the role exists pror to creating it.
	$tip_jar_wp_manager_role = get_role( 'tipjarwp_manager' );

	// If the user role does not yet exists, add it.
	if ( ! $tip_jar_wp_manager_role ) {
		// Create the new user role with no capailities by default.
		$tip_jar_wp_manager_role = add_role( 'tipjarwp_manager', __( 'Tip Jar WP Manager', 'tip-jar-wp' ), array() );
		// Add a new user capability to the user role.
		$tip_jar_wp_manager_role->add_cap( 'do_tipjarwp_manager_things' );
		$tip_jar_wp_manager_role->add_cap( 'read' );
		$tip_jar_wp_manager_role->add_cap( 'upload_files' );
		$tip_jar_wp_manager_role->add_cap( 'delete_attachments' );
	}

	// If the user capability has not yet been applied to administrators, apply it.
	$admin_role = get_role( 'administrator' );
	if ( ! $admin_role->has_cap( 'do_tipjarwp_manager_things' ) ) {
		$admin_role->add_cap( 'do_tipjarwp_manager_things' );
	}

}
add_action( 'admin_init', 'tip_jar_wp_user_role_and_capability_setup' );

/**
 * Get the SVG icon to use for Tip Jar WP
 *
 * @param  string $fill_color The color to use as the fill for the svg.
 * @since  1.0.0.
 * @return string
 */
function tip_jar_wp_get_svg_icon( $fill_color = '#000000' ) {
	return '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000" xml:space="preserve" fill="' . $fill_color . '">
	<path d="M797.669,371.752l-19.702-55.833c-1.521-4.297-2.181-8.854-1.938-13.406l0.018-0.334l-0.125-44.222h12.02
	c6.924,0,12.537-5.613,12.537-12.537c0-6.924-5.613-12.538-12.537-12.538h-12.09l-0.086-30.089h12.176
	c6.924,0,12.537-5.613,12.537-12.537s-5.613-12.537-12.537-12.537h-12.245l-0.099-34.784c-0.064-14.734-12.012-26.653-26.746-26.681
	H251.158c-14.733,0.03-26.68,11.948-26.746,26.681l-0.099,34.784h-12.253c-6.924,0-12.538,5.613-12.538,12.537
	s5.613,12.537,12.538,12.537l0,0h12.174l-0.085,30.089h-12.089c-6.924,0-12.538,5.613-12.538,12.538
	c0,6.924,5.613,12.537,12.538,12.537l0,0h12.019l-0.124,44.222l0.017,0.334c0.242,4.553-0.417,9.11-1.939,13.408l-19.702,55.832
	c-23.033,65.168-34.787,133.787-34.756,202.907v171.176c0.085,76.13,61.78,137.825,137.91,137.91h389.031
	c76.13-0.085,137.824-61.78,137.91-137.91V574.659C832.456,505.539,820.702,436.921,797.669,371.752z M249.478,143.005
	c0.007-0.92,0.751-1.665,1.672-1.671h497.692c0.921,0.006,1.666,0.751,1.672,1.671l0.099,34.714H249.379L249.478,143.005z
	M249.311,202.793h501.38l0.085,30.089H249.224L249.311,202.793z M807.351,745.842c-0.069,62.288-50.547,112.766-112.835,112.835
	H305.485c-62.289-0.069-112.766-50.547-112.836-112.835V574.666c-0.029-66.277,11.241-132.073,33.326-194.562l19.702-55.823
	c2.577-7.275,3.716-14.982,3.355-22.693l0.122-43.629h501.692l0.124,43.629c-0.362,7.71,0.776,15.417,3.354,22.693l19.702,55.823
	c22.084,62.489,33.354,128.285,33.325,194.562V745.842z"/>
	<path d="M500,390.853L500,390.853c-79.742-0.001-144.387,64.643-144.388,144.384c0,0.002,0,0.003,0,0.004l0,0
	c0,79.742,64.643,144.387,144.384,144.388c0.002,0,0.003,0,0.004,0l0,0c79.743,0.001,144.388-64.642,144.388-144.385
	c0-0.001,0-0.002,0-0.003l0,0c0.001-79.742-64.642-144.387-144.385-144.388C500.003,390.853,500.001,390.853,500,390.853z
	M518.89,618.042c-5.441,2.061-6.411,4.122-6.37,7.663c0.018,0.275,0.042,0.555,0.071,0.835c0.081,0.744,0.146,1.737,0.168,2.811
	c0.261,4.611-0.229,7.92-1.462,9.724c-3.593,5.267-7.729,6.466-10.568,6.547h-0.194c-4.691,0-10.85-4.636-12.088-9.148
	c-0.59-2.147-1.07-4.024-1.493-5.684c-2.565-10.029-2.718-10.622-13.707-16.676l-0.265-0.167
	c-12.098-8.125-19.371-19.925-21.617-35.071c-0.185-1.218-0.291-2.446-0.319-3.678c-0.722-11.382,6.261-14.181,10.497-14.84
	c11.959-1.823,13.875,9.811,14.516,13.642c2.46,14.76,12.35,22.93,26.451,21.912c12.242-1.132,21.583-11.443,21.501-23.738
	c-0.408-12.92-9.737-21.837-24.349-23.149c-23.975-2.152-41.046-15.179-46.838-35.737c-1.253-4.433-1.898-9.015-1.921-13.622
	c-0.771-20.113,11.306-38.5,30.071-45.782c5.39-2.111,6.353-4.199,6.302-7.784c-0.019-0.276-0.042-0.561-0.071-0.852
	c-0.079-0.76-0.129-1.672-0.154-2.595c-0.08-1.409-0.09-3.297-0.025-4.603c-0.064-3.077,0.818-6.101,2.531-8.659
	c2.297-3.51,7.476-4.513,9.891-4.539c2.807,0.095,5.439,1.381,7.24,3.535c2.578,2.721,4.103,6.271,4.302,10.014
	c0.057,0.507,0.062,1.02,0.017,1.528c-0.092,0.983-0.126,1.972-0.104,2.96c0.426,6.077,3.644,9.545,12.74,13.573
	c13.743,6.083,22.35,18.723,24.894,36.57c0.183,1.247,0.286,2.503,0.312,3.763c0.747,11.339-6.256,14.119-10.496,14.753
	c-11.822,1.759-13.894-9.863-14.568-13.685c-2.435-13.759-10.514-21.541-22.748-21.909c-13.453-0.431-24.263,9.07-25.195,22.046
	c-0.05,0.71-0.055,1.408-0.047,2.104c0.689,11.812,9.842,21.383,21.611,22.601c1.422,0.183,2.854,0.309,4.286,0.438
	c2.701,0.19,5.39,0.541,8.051,1.048c21.062,4.197,36.924,21.644,39.101,43.01c0.134,1.348,0.208,2.725,0.228,4.21
	C550.058,591.817,537.914,610.583,518.89,618.042L518.89,618.042z"/>
	</svg>';
}

/**
 * Convert a value in cents, or the lowest possible unit in the currency, back to the normal amount and put the currency symbol at the start.
 *
 * @since 1.0.0.
 * @param int    $cents The number of cents being displayed.
 * @param string $currency The 3-letter currency in which they are being displayed.
 * @return bool
 */
function tip_jar_wp_get_visible_amount( $cents, $currency ) {

	$cents = absint( $cents );

	// If this is a decimal currency (not a zero decimal currency) https://stripe.com/docs/currencies#zero-decimal.
	if ( ! tip_jar_wp_is_a_zero_decimal_currency( $currency ) ) {
		$amount = ( $cents / 100 );
	} else {
		$amount = $cents;
	}

	return html_entity_decode( tip_jar_wp_currency_symbol( $currency ) ) . $amount;
}

/**
 * Set the default value if the first value is empty
 *
 * @since    1.0.0
 * @param    array  $saved_settings The array of saved settings.
 * @param    string $key The setting we went to extract.
 * @param    string $default_value The default value to use if none exists.
 * @return   array
 */
function tip_jar_wp_get_saved_setting( $saved_settings, $key, $default_value = null ) {

	if ( isset( $saved_settings[ $key ] ) ) {

		// If the saved value is empty.
		if ( empty( $saved_settings[ $key ] ) ) {

			// If a default value was passed-in.
			if ( $default_value ) {
				return $default_value;
			} else {

				// If a default value was not passed in.
				return $saved_settings[ $key ];
			}
		} else {

			// If there is a saved value.
			return $saved_settings[ $key ];

		}
	} else {

		return $default_value;

	}
}

/**
 * Search for the currencies which match a given search term.
 *
 * @since 1.0
 * @param string $search_term The term being used to search for a currency.
 * @return array $currencies A list of the available currencies
 */
function tip_jar_wp_currency_search_results( $search_term ) {

	// Get all available values.
	$all_available_currencies = tip_jar_wp_get_currencies();

	$matching_currencies = array();

	// Search the array.
	foreach ( $all_available_currencies as $currency_key => $currency_value ) {
		if ( stripos( $currency_key, $search_term ) !== false || stripos( $currency_value, $search_term ) !== false ) {
			$matching_currencies[ $currency_key ] = $currency_value;
		}
	}

	return $matching_currencies;
}

/**
 * Get Currencies. This checks at Stripe for available currencies to this account and formats them into a nice readable array.
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */
function tip_jar_wp_get_currencies() {

	$all_currencies_that_exist_in_the_world = array(
		'AFN' => 'Afghan Afghani Afganistan',
		'ALL' => 'Albanian Lek Albania',
		'DZD' => 'Algerian Dinar Algeria',
		'AOA' => 'Angolan Kwanza Angola',
		'ARS' => 'Argentine Peso Argentina',
		'AMD' => 'Armenian Dram Armenia',
		'AWG' => 'Aruban Florin Aruba',
		'AUD' => 'Australian Dollar Australia',
		'AZN' => 'Azerbaijani Manat',
		'BSD' => 'Bahamian Dollar Bahai',
		'BDT' => 'Bangladeshi Taka Bangladesh',
		'BBD' => 'Barbadian Dollar Barbados',
		'BZD' => 'Belize Dollar Belize',
		'BMD' => 'Bermudian Dollar Bermuda',
		'BOB' => 'Bolivian Boliviano Bolibia',
		'BAM' => 'Bosnia & Herzegovina Convertible Mark',
		'BWP' => 'Botswana Pula',
		'BRL' => 'Brazilian Real Brazil',
		'GBP' => 'British Pound Great Britain England',
		'BND' => 'Brunei Dollar',
		'BGN' => 'Bulgarian Lev Bulgaria',
		'BIF' => 'Burundian Franc Burundi',
		'KHR' => 'Cambodian Riel Cambodia',
		'CAD' => 'Canadian Dollar Canada CDN',
		'CVE' => 'Cape Verdean Escudo Cape Verdea',
		'KYD' => 'Cayman Islands Dollar',
		'XAF' => 'Central African Cfa Franc Central Africa',
		'XPF' => 'Cfp Franc French overseas collectivities',
		'CLP' => 'Chilean Peso Chili',
		'CNY' => 'Chinese Renminbi Yuan China',
		'COP' => 'Colombian Peso Columbia',
		'KMF' => 'Comorian Franc Comoros',
		'CDF' => 'Congolese Franc',
		'CRC' => 'Costa Rican Colón',
		'HRK' => 'Croatian Kuna',
		'CZK' => 'Czech Koruna czechoslovakia',
		'DKK' => 'Danish Krone Denmark',
		'DJF' => 'Djiboutian Franc',
		'DOP' => 'Dominican Peso',
		'XCD' => 'East Caribbean Dollar',
		'EGP' => 'Egyptian Pound Egypt',
		'ETB' => 'Ethiopian Birr',
		'EUR' => 'Euro European Union Austria Belgium Cyprus Estonia Finland France Germany Greece Ireland Italy Latvia Lithuania Luxembourg Malta the Netherlands Holland Portugal Slovakia Slovenia Spain',
		'FKP' => 'Falkland Islands Pound',
		'FJD' => 'Fijian Dollar',
		'GMD' => 'Gambian Dalasi',
		'GEL' => 'Georgian Lari',
		'GIP' => 'Gibraltar Pound',
		'GTQ' => 'Guatemalan Quetzal',
		'GNF' => 'Guinean Franc',
		'GYD' => 'Guyanese Dollar Guyana',
		'HTG' => 'Haitian Gourde',
		'HNL' => 'Honduran Lempira Honduras',
		'HKD' => 'Hong Kong Dollar',
		'HUF' => 'Hungarian Forint Hungary',
		'ISK' => 'Icelandic Króna',
		'INR' => 'Indian Rupee',
		'IDR' => 'Indonesian Rupiah',
		'ILS' => 'Israeli New Sheqel',
		'JMD' => 'Jamaican Dollar',
		'JPY' => 'Japanese Yen',
		'KZT' => 'Kazakhstani Tenge',
		'KES' => 'Kenyan Shilling',
		'KGS' => 'Kyrgyzstani Som',
		'KRW' => 'South Korean won',
		'LAK' => 'Lao Kip',
		'LBP' => 'Lebanese Pound Lebanon',
		'LSL' => 'Lesotho Loti',
		'LRD' => 'Liberian Dollar',
		'MOP' => 'Macanese Pataca Macau',
		'MKD' => 'Macedonian Denar',
		'MGA' => 'Malagasy Ariary Madagascar',
		'MWK' => 'Malawian Kwacha',
		'MYR' => 'Malaysian Ringgit',
		'MVR' => 'Maldivian Rufiyaa Maldives',
		'MRO' => 'Mauritanian Ouguiya Mauritania',
		'MUR' => 'Mauritian Rupee Mauritius',
		'MXN' => 'Mexican Peso Mexico',
		'MDL' => 'Moldovan Leu',
		'MNT' => 'Mongolian Tögrög',
		'MAD' => 'Moroccan Dirham Morocco',
		'MZN' => 'Mozambican Metical',
		'MMK' => 'Myanmar Kyat',
		'NAD' => 'Namibian Dollar',
		'NPR' => 'Nepalese Rupee',
		'ANG' => 'Netherlands Antillean Gulden',
		'TWD' => 'New Taiwan Dollar',
		'NZD' => 'New Zealand Dollar',
		'NIO' => 'Nicaraguan Córdoba',
		'NGN' => 'Nigerian Naira',
		'NOK' => 'Norwegian Krone Norway',
		'PKR' => 'Pakistani Rupee',
		'PAB' => 'Panamanian Balboa',
		'PGK' => 'Papua New Guinean Kina',
		'PYG' => 'Paraguayan Guaraní',
		'PEN' => 'Peruvian Nuevo Sol',
		'PHP' => 'Philippine Peso Philippines',
		'PLN' => 'Polish Złoty Poland',
		'QAR' => 'Qatari Riyal',
		'RON' => 'Romanian Leu',
		'RUB' => 'Russian Ruble',
		'RWF' => 'Rwandan Franc',
		'STD' => 'São Tomé and Príncipe Dobra',
		'SHP' => 'Saint Helenian Pound Saint Helena',
		'SVC' => 'Salvadoran Colón El Salvador',
		'WST' => 'Samoan Tala',
		'SAR' => 'Saudi Riyal Saudi Arabia',
		'RSD' => 'Serbian Dinar',
		'SCR' => 'Seychellois Rupee',
		'SLL' => 'Sierra Leonean Leone',
		'SGD' => 'Singapore Dollar',
		'SBD' => 'Solomon Islands Dollar',
		'SOS' => 'Somali Shilling',
		'ZAR' => 'South African Rand',
		'KRW' => 'South Korean Won',
		'LKR' => 'Sri Lankan Rupee',
		'SRD' => 'Surinamese Dollar Suriname',
		'SZL' => 'Swazi Lilangeni Swaziland Eswatini',
		'SEK' => 'Swedish Krona Sweden',
		'CHF' => 'Swiss Franc Switzerland',
		'TJS' => 'Tajikistani Somoni',
		'TZS' => 'Tanzanian Shilling',
		'THB' => 'Thai Baht Thailand',
		'TOP' => 'Tongan Paʻanga Tongo',
		'TTD' => 'Trinidad and Tobago Dollar',
		'TRY' => 'Turkish Lira Turkey',
		'UGX' => 'Ugandan Shilling',
		'UAH' => 'Ukrainian Hryvnia',
		'AED' => 'United Arab Emirates Dirham',
		'USD' => 'United States Dollar America American',
		'UYU' => 'Uruguayan Peso',
		'UZS' => 'Uzbekistani Som',
		'VUV' => 'Vanuatu Vatu',
		'VND' => 'Vietnamese Đồng',
		'XOF' => 'West African Cfa Franc West Africa',
		'YER' => 'Yemeni Rial',
		'ZMW' => 'Zambian Kwacha',
	);

	$stripe_currencies = tip_jar_wp_stripe_get_available_currencies();

	$formatted_currency_array = array();

	// Here we will rebuild the array of currencies so that it is an associative array, since stripe only gives us currency codes but not names.
	foreach ( $stripe_currencies as $stripe_currency_code ) {
		if ( isset( $all_currencies_that_exist_in_the_world[ strtoupper( $stripe_currency_code ) ] ) ) {
			$formatted_currency_array[ strtoupper( $stripe_currency_code ) ] = $all_currencies_that_exist_in_the_world[ strtoupper( $stripe_currency_code ) ];
		} else {
			$formatted_currency_array[ strtoupper( $stripe_currency_code ) ] = $stripe_currency_code;
		}
	}

	return $formatted_currency_array;
}

/**
 * Return whether a given currency ode is a zero decimal currency
 * This list came from https://stripe.com/docs/currencies#zero-decimal
 *
 * @since 1.0
 * @param string $currency_code The 3 letter currency code in question.
 * @return bool true if zero decimal currency, false if not.
 */
function tip_jar_wp_is_a_zero_decimal_currency( $currency_code ) {

	$all_zero_decimal_currencies = tip_jar_wp_get_zero_decimal_currencies();

	// If the given curency code is in the list of all zero decimal currencies.
	if ( array_key_exists( strtoupper( $currency_code ), $all_zero_decimal_currencies ) ) {

		// Return true, indicating that it is a zero decimal currency.
		return true;

		// If not a zero decimal currency.
	} else {

		// Return false, indicating it is not a zero decimal currency.
		return false;
	}
}

/**
 * Get the currencies that are Zero Decimal Currencies.
 * This list came from https://stripe.com/docs/currencies#zero-decimal
 *
 * @since 1.0
 * @return array $currencies A list of zero decimal currencies
 */
function tip_jar_wp_get_zero_decimal_currencies() {

	$zero_decimal_currencies = array(
		'BIF' => 'Burundian Franc Burundi',
		'CLP' => 'Chilean Peso',
		'DJF' => 'Djiboutian Franc',
		'GNF' => 'Guinean Franc',
		'JPY' => 'Japanese Yen',
		'KMF' => 'Comorian franc',
		'KRW' => 'South Korean won',
		'MGA' => 'Malagasy Ariary',
		'PYG' => 'Paraguayan Guaraní',
		'RWF' => 'Rwandan Franc',
		'UGX' => 'Ugandan Shilling',
		'VUV' => 'Vanuatu Vatu',
		'VND' => 'Vietnamese Đồng',
		'XOF' => 'West African Cfa Franc',
		'XPF' => 'French overseas collectivities franc',
	);

	return $zero_decimal_currencies;

}

/**
 * Given a currency determine the symbol to use. If no currency given, site default is used.
 * If no symbol is determine, the currency string is returned.
 *
 * @since  1.0
 * @param  string $currency The currency string.
 * @return string           The symbol to use for the currency
 */
function tip_jar_wp_currency_symbol( $currency = '' ) {

	$currency_symbols = array(
		'AED' => '&#1583;.&#1573;', // ?
		'AFN' => '&#65;&#102;',
		'ALL' => '&#76;&#101;&#107;',
		'AMD' => '',
		'ANG' => '&#402;',
		'AOA' => '&#75;&#122;', // ?
		'ARS' => '&#36;',
		'AUD' => '&#36;',
		'AWG' => '&#402;',
		'AZN' => '&#1084;&#1072;&#1085;',
		'BAM' => '&#75;&#77;',
		'BBD' => '&#36;',
		'BDT' => '&#2547;', // ?
		'BGN' => '&#1083;&#1074;',
		'BHD' => '.&#1583;.&#1576;', // ?
		'BIF' => '&#70;&#66;&#117;', // ?
		'BMD' => '&#36;',
		'BND' => '&#36;',
		'BOB' => '&#36;&#98;',
		'BRL' => '&#82;&#36;',
		'BSD' => '&#36;',
		'BTN' => '&#78;&#117;&#46;', // ?
		'BWP' => '&#80;',
		'BYR' => '&#112;&#46;',
		'BZD' => '&#66;&#90;&#36;',
		'CAD' => '&#36;',
		'CDF' => '&#70;&#67;',
		'CHF' => '&#67;&#72;&#70;',
		'CLF' => '', // ?
		'CLP' => '&#36;',
		'CNY' => '&#165;',
		'COP' => '&#36;',
		'CRC' => '&#8353;',
		'CUP' => '&#8396;',
		'CVE' => '&#36;', // ?
		'CZK' => '&#75;&#269;',
		'DJF' => '&#70;&#100;&#106;', // ?
		'DKK' => '&#107;&#114;',
		'DOP' => '&#82;&#68;&#36;',
		'DZD' => '&#1583;&#1580;', // ?
		'EGP' => '&#163;',
		'ETB' => '&#66;&#114;',
		'EUR' => '&#8364;',
		'FJD' => '&#36;',
		'FKP' => '&#163;',
		'GBP' => '&#163;',
		'GEL' => '&#4314;', // ?
		'GHS' => '&#162;',
		'GIP' => '&#163;',
		'GMD' => '&#68;', // ?
		'GNF' => '&#70;&#71;', // ?
		'GTQ' => '&#81;',
		'GYD' => '&#36;',
		'HKD' => '&#36;',
		'HNL' => '&#76;',
		'HRK' => '&#107;&#110;',
		'HTG' => '&#71;', // ?
		'HUF' => '&#70;&#116;',
		'IDR' => '&#82;&#112;',
		'ILS' => '&#8362;',
		'INR' => '&#8377;',
		'IQD' => '&#1593;.&#1583;', // ?
		'IRR' => '&#65020;',
		'ISK' => '&#107;&#114;',
		'JEP' => '&#163;',
		'JMD' => '&#74;&#36;',
		'JOD' => '&#74;&#68;', // ?
		'JPY' => '&#165;',
		'KES' => '&#75;&#83;&#104;', // ?
		'KGS' => '&#1083;&#1074;',
		'KHR' => '&#6107;',
		'KMF' => '&#67;&#70;', // ?
		'KPW' => '&#8361;',
		'KRW' => '&#8361;',
		'KWD' => '&#1583;.&#1603;', // ?
		'KYD' => '&#36;',
		'KZT' => '&#1083;&#1074;',
		'LAK' => '&#8365;',
		'LBP' => '&#163;',
		'LKR' => '&#8360;',
		'LRD' => '&#36;',
		'LSL' => '&#76;', // ?
		'LTL' => '&#76;&#116;',
		'LVL' => '&#76;&#115;',
		'LYD' => '&#1604;.&#1583;', // ?
		'MAD' => '&#1583;.&#1605;.', // ?
		'MDL' => '&#76;',
		'MGA' => '&#65;&#114;', // ?
		'MKD' => '&#1076;&#1077;&#1085;',
		'MMK' => '&#75;',
		'MNT' => '&#8366;',
		'MOP' => '&#77;&#79;&#80;&#36;', // ?
		'MRO' => '&#85;&#77;', // ?
		'MUR' => '&#8360;', // ?
		'MVR' => '.&#1923;', // ?
		'MWK' => '&#77;&#75;',
		'MXN' => '&#36;',
		'MYR' => '&#82;&#77;',
		'MZN' => '&#77;&#84;',
		'NAD' => '&#36;',
		'NGN' => '&#8358;',
		'NIO' => '&#67;&#36;',
		'NOK' => '&#107;&#114;',
		'NPR' => '&#8360;',
		'NZD' => '&#36;',
		'OMR' => '&#65020;',
		'PAB' => '&#66;&#47;&#46;',
		'PEN' => '&#83;&#47;&#46;',
		'PGK' => '&#75;', // ?
		'PHP' => '&#8369;',
		'PKR' => '&#8360;',
		'PLN' => '&#122;&#322;',
		'PYG' => '&#71;&#115;',
		'QAR' => '&#65020;',
		'RON' => '&#108;&#101;&#105;',
		'RSD' => '&#1044;&#1080;&#1085;&#46;',
		'RUB' => '&#1088;&#1091;&#1073;',
		'RWF' => '&#1585;.&#1587;',
		'SAR' => '&#65020;',
		'SBD' => '&#36;',
		'SCR' => '&#8360;',
		'SDG' => '&#163;', // ?
		'SEK' => '&#107;&#114;',
		'SGD' => '&#36;',
		'SHP' => '&#163;',
		'SLL' => '&#76;&#101;', // ?
		'SOS' => '&#83;',
		'SRD' => '&#36;',
		'STD' => '&#68;&#98;', // ?
		'SVC' => '&#36;',
		'SYP' => '&#163;',
		'SZL' => '&#76;', // ?
		'THB' => '&#3647;',
		'TJS' => '&#84;&#74;&#83;', // ? TJS (guess).
		'TMT' => '&#109;',
		'TND' => '&#1583;.&#1578;',
		'TOP' => '&#84;&#36;',
		'TRY' => '&#8356;', // New Turkey Lira (old symbol used).
		'TTD' => '&#36;',
		'TWD' => '&#78;&#84;&#36;',
		'TZS' => '',
		'UAH' => '&#8372;',
		'UGX' => '&#85;&#83;&#104;',
		'USD' => '&#36;',
		'UYU' => '&#36;&#85;',
		'UZS' => '&#1083;&#1074;',
		'VEF' => '&#66;&#115;',
		'VND' => '&#8363;',
		'VUV' => '&#86;&#84;',
		'WST' => '&#87;&#83;&#36;',
		'XAF' => '&#70;&#67;&#70;&#65;',
		'XCD' => '&#36;',
		'XDR' => '',
		'XOF' => '',
		'XPF' => '&#70;',
		'YER' => '&#65020;',
		'ZAR' => '&#82;',
		'ZMK' => '&#90;&#75;', // ?
		'ZWL' => '&#90;&#36;',
	);

	$uppercase_currency = strtoupper( $currency );

	if ( ! isset( $currency_symbols[ $uppercase_currency ] ) ) {
		return $uppercase_currency;
	}

	return $currency_symbols[ strtoupper( $currency ) ];

}

/**
 * Get the statement descriptor we want to use.
 *
 * @since  1.0
 * @return string
 */
function tip_jar_wp_statement_descriptor() {

	// Get the saved options for Tip Jar WP.
	$settings = get_option( 'tip_jar_wp_settings' );

	// Check if a custom statement descriptor has been entered.
	$statement_descriptor = tip_jar_wp_get_saved_setting( $settings, 'statement_descriptor', get_bloginfo( 'name' ) );

	// If there is no statement descriptor, use the site's URL.
	if ( empty( $statement_descriptor ) ) {
		$statement_descriptor = get_bloginfo( 'url' );
	}

	return sanitize_title( substr( $statement_descriptor, 0, 22 ) );
}

// Workaround for users on nginx, where getallheaders isn't a PHP function.
if ( ! function_exists( 'getallheaders' ) ) {
	/**
	 * Workaround for users on nginx, where getallheaders isn't a PHP function.
	 *
	 * @since  1.0
	 * @return array The headers array.
	 */
	function getallheaders() {
		$headers = [];
		foreach ( $_SERVER as $name => $value ) {
			if ( substr( $name, 0, 5 ) === 'HTTP_' ) {
				$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
			}
		}
		return $headers;
	}
}

/**
 * Similar to wp_parse_args() but extended to work with multidimensional arrays .
 *
 * @since  1.0.1.3
 * @param  array $args The arguments being checked.
 * @param  array $defaults The arguments to use as defaults.
 * @return array The headers array.
 */
function tip_jar_wp_wp_parse_args( $args, $defaults ) {
	$new_args = (array) $defaults;

	foreach ( $args as $key => $value ) {
		if ( is_array( $value ) && isset( $new_args[ $key ] ) ) {
			$new_args[ $key ] = tip_jar_wp_wp_parse_args( $value, $new_args[ $key ] );
		} else {
			$new_args[ $key ] = $value;
		}
	}

		return $new_args;
}

/**
 * Tell Tip Jar WPs settings that emails are not successfully being received.
 *
 * @since  1.0
 * @return void
 */
function tip_jar_wp_unconfirm_wp_mail_health_check() {

	// Get the saved options for Tip Jar WP.
	$settings = get_option( 'tip_jar_wp_settings' );

	// Set the wp_mail check to be true, confirmed.
	$settings['wp_mail_confirmed'] = false;

	// Save the options.
	update_option( 'tip_jar_wp_settings', $settings );

}

/**
 * Check if the current website is a localhost
 *
 * @since  1.0
 * @return bool
 */
function tip_jar_wp_is_site_localhost() {

	$site_url = get_bloginfo( 'url' );

	$localhost_possibilities = array(
		'.local',
		'.dev',
		'.test',
	);

	// Loop through each possible localhost URL.
	foreach ( $localhost_possibilities as $localhost_possibility ) {
		// Check if the site ends with one of the $localhost_possibility strings.
		if ( tip_jar_wp_ends_with( $site_url, $localhost_possibility ) ) {
			// This is a localhost.
			return true;
		}
	}

	// If this is not a localhost.
	return false;

}

/**
 * Simple helper function to check what a string ends with
 *
 * @since  1.0
 * @param  string $haystack The full string we are wondering about.
 * @param  string $needle We are wondering if the string end with this.
 * @return bool
 */
function tip_jar_wp_ends_with( $haystack, $needle ) {
	$length = strlen( $needle );
	if ( 0 === $length ) {
		return true;
	}

	return ( substr( $haystack, -$length ) === $needle );
}

/**
 * Check if the current website is reachable over ssl
 *
 * @since  1.0
 * @return bool
 */
function tip_jar_wp_is_site_reachable_over_ssl() {

	// If this site is already running on SSL, we don't need to do this check, we know it is already.
	if ( is_ssl() ) {
		return true;
	}

	// It's possible that they have an SSL, but are just logged in over port 80. Try pinging their site over https.
	$response = wp_remote_post( str_replace( 'http://', 'https://', get_bloginfo( 'url' ) ) );

	// If we were not able to ping the site over https, no certificate exists.
	if ( is_wp_error( $response ) ) {

		// Set the default to false for the certificate's existence.
		$certificate_exists = false;

		// Loop through each error.
		foreach ( $response->errors as $wp_error_code => $wp_error_message ) {

			if ( 'http_request_failed' === $wp_error_code ) {
				// If this is a local domain, allow self-signed certs.
				if ( tip_jar_wp_is_site_localhost() ) {
					if ( false !== strpos( $wp_error_message[0], 'self signed certificate' ) ) {
						// Allow self-signed certificates if "local" is in the domain.
						$certificate_exists = true;
					}
				}
			}
		}
		// The site was pingable over https, so a good certificate is in place. It just needs to be used.
	} else {
		$certificate_exists = true;
	}

	return $certificate_exists;
}

/**
 * Show the "Get Started!" and "Settings" links under the plugin on the "Dashboard" > "Plugins" page.
 *
 * @since  1.0
 * @param array  $links The list of links to show under the plugin.
 * @param string $file The of the plugin file.
 * @return bool
 */
function tip_jar_wp_links_in_plugin_list_table( $links, $file ) {

	if ( strpos( $file, 'tip-jar-wp.php' ) !== false ) {
		$new_links = array(
			'setup'    => '<a href="' . admin_url( 'admin.php?page=tip-jar-wp&mpwpadmin1=welcome&mpwpadmin_lightbox=do_wizard_health_check' ) . '">' . __( 'Get Started!', 'tip-jar-wp' ) . '</a>',
			'settings' => '<a href="' . admin_url( 'admin.php?page=tip-jar-wp&mpwpadmin1=welcome' ) . '">' . __( 'Settings', 'tip-jar-wp' ) . '</a>',
		);

		$links = array_merge( $links, $new_links );
	}

	return $links;
}
add_filter( 'plugin_row_meta', 'tip_jar_wp_links_in_plugin_list_table', 10, 2 );
add_filter( 'plugin_action_links', 'tip_jar_wp_links_in_plugin_list_table', 10, 2 );
