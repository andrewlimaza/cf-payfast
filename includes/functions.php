<?php
/**
 * Functions for payfast for Caldera Forms
 */


/**
 * Registers the payfast for Caldera Forms Processor
 *
 * @uses "caldera_forms_get_form_processors" filter
 *
 * @since 0.1.0
 * @param array		$processors		Array of current registered processors
 *
 * @return array	Array of registered processors
 */
function cf_payfast_register($processors){
	if ( ! class_exists( 'Caldera_Forms_Processor_Get_Data' ) ) {
		return $processors;
	}


	$processors['cf_payfast'] = array(
		"name"				=>  __( 'PayFast for Caldera Forms', 'cf-payfast'),
		"description"		=>	__( 'Accept payments using PayFast and Caldera Forms', 'cf-payfast'),
		"author"			=>	'Yoohoo Plugins',
		"author_url"		=>	'https://yoohooplugins.com',
		"pre_processor"		=>	'cf_payfast_pre_process',
		"processor"			=>  'cf_payfast_process',
		"icon"				=>	CF_PAYFAST_URL . "payfast-icon.png",
		"template"			=>	CF_PAYFAST_PATH . "includes/config.php",
	);

	return $processors;

}

/**
 * Pre-Proccess payfast for Caldera Forms proccessor
 *
 * @since 0.1.0
 *
 * @param array $config Processor config
 * @param array $form Form config
 * @param string $proccesid Process ID
 *
 * @return array
 */
function cf_payfast_pre_process( $config, $form, $proccesid ) {
	global $transdata;

	/**
	 * Complete submission after coming back from payfast.
	 */
	if( !empty( $_REQUEST['cf-payfast-payment-confirmation'] ) && 'payfast' == $_REQUEST['cf-payfast-payment-confirmation'] ){
		if ( isset( $_GET[ 'processid' ] ) && isset( $transdata[ 'cf_payfast' ] ) && isset( $transdata[ 'cf_payfast' ][ $_GET[ 'processid' ] ] ) ) {

			$processor_data = $transdata[ 'cf_payfast' ][ $_GET[ 'processid' ] ][ 'process_object' ];
			if ( ! is_object( $processor_data )  ){
				return array(
					'type' => 'error',
					'note' => __( 'Error completing transaction', 'cf-payfast' )
				);
			}

			if ( ! isset( $transdata[ $proccesid ][ 'meta'] ) ) {
				$transdata[ $proccesid ][ 'meta'] = array();
			}

			// Add some errors here.

			// Get all Errors and return the errors if any.
			$errors = $processor_data->get_errors();
			if ( ! empty( $errors  ) ) {
				return $errors;
			}

			// No errors, process the form.
			return;
		}
	}


	/**
	 * New Submission
	 */
	//get data and errors from this processor
	$processor_data  = new Caldera_Forms_Processor_Get_Data( $config, $form, cf_payfast_fields() );

	//If we have errors, report them and bail
	$errors = $processor_data->get_errors();
	if ( ! empty( $errors  ) ) {

		return $errors;

	}
	//record data for this proccessor for saving
	$transdata[ $proccesid ] = $processor_data->get_values();

	$processor_data = cf_payfast_process_payment(  $processor_data, $proccesid );

	$errors = $processor_data->get_errors();

	if ( ! empty( $errors  ) ) {
		return $errors;
	}

	if( isset($transdata['cf_payfast' ][ $proccesid ][ 'url' ] ) ){
		// set transient expire to longer to allow user a longer login setup etc..
		$transdata['expire'] = 1800; // 30 min should give enough time to register if needed.
		return array(
			'type' => 'success',
			'url' => $transdata['cf_payfast' ][ $proccesid ][ 'url' ]
		);

	}

}


/**
 * Complete processing of payfast for Caldera Forms.
 *
 * @since 0.1.0
 *
 * @param array $config Processor config
 * @param array $form Form config
 * @param string $proccessid Process ID
 *
 * @return array The Transdata var for this form.
 */
function cf_payfast_process( $config, $form, $proccessid ) {

	global $transdata;

	if ( ! isset( $transdata[ $proccessid ][ 'meta' ] )) {
		$transdata[ $proccessid ][ 'meta'  ] = array();
	}

	return $transdata[ $proccessid ][ 'meta' ];

}

/**
 * Process payment and prepare the URL to redirect to payfast.
 *
 * @since 0.1.0
 *
 * @param Caldera_Forms_Processor_Get_Data $processor_data
 *
 * @return Caldera_Forms_Processor_Get_Data
 */
function cf_payfast_process_payment( $processor_data, $proccessid ) {
	global $transdata;
	if ( ! isset( $transdata['cf_payfast'] ) || ! isset( $transdata['cf_payfast'][ 'return_url' ]  ) ) {
		$processor_data->add_error( 'Could not set redirect URL.', 'cf-payfast' );
		return $processor_data;
	}

	// Get the data from the PayFast Processor
	$payment_data = $processor_data->get_values();

	// Set mode to sandbox or live.
	if ( $payment_data['sandbox'] == '1') {
		$url = "https://sandbox.payfast.co.za/eng/process/";
	} else {
		$url = "https://payfast.co.za/eng/process/";
	}

	// Try to catch sandbox details with live URL - throw an error.
	if ( $payment_data['merchant_id'] === '10006218' && strpos( $url, 'sandbox' ) == false ) {
		$processor_data->add_error( "You are using sandbox details with PayFast's live URL. Please check your merchant details.", 'cf-payfast' );
		return $processor_data;
	}

	$random_number = substr( md5( $payment_data['email_address'] ), 0, 10 );

	// Initial information to be passed through to PayFast.
	$body = array(
		"merchant_id" => $payment_data['merchant_id'],
		"merchant_key"=> $payment_data['merchant_key'],
		"return_url" => $transdata['cf_payfast']['return_url'],
		"cancel_url" => $transdata['cf_payfast']['cancel_url'],
		"name_first" => $payment_data['name_first'],
		"name_last" => $payment_data['name_last'],
		"email_address" => $payment_data['email_address'],
		"cell_number" => $payment_data['cell_number'],
		"m_payment_id" => $random_number,
		"amount" => $payment_data['initial_amount'],
		"item_name" => $payment_data['item_name'],
		"item_description" => $payment_data['item_description'],
		// "email_confirmation" => $payment_data['email_confirmation'],
		// "confirmation_address" => $payment_data['confirmation_address'],
		"payment_method" => $payment_data['payment_method'],
	);

	// Let's handle the recurring payments now.
	if ( ! empty( $payment_data['recurring'] ) ) {
		$recurring_payment_args = array(
			"subscription_type" => $payment_data['recurring'],
			"recurring_amount" => $payment_data['recurring_amount'],
			"frequency" => $payment_data['frequency'],
			"cycles" => $payment_data['billing_cycles']
		);

		// merge both arrays into one.
		$body = array_merge( $body, $recurring_payment_args );
	}

	$body = apply_filters( 'cf_payfast_checkout_parameters', $body );

	$url = add_query_arg( $body, $url );
	
	$transdata['cf_payfast' ][ $proccessid ][ 'url' ] = $url;
	$transdata['cf_payfast' ][ $proccessid ][ 'process_object' ] = $processor_data;


	return $processor_data;
}

/**
 * The fields for this processor.
 *
 * @since 0.1.0
 *
 * @return array Array of fields
 */
function cf_payfast_fields() {
	$fields = array(
		array(
			'id' => 'sandbox',
			'label' => __( 'Sandbox', 'cf-payfast' ),
			'type' => 'checkbox',
			'desc' => __( 'Use when testing. Make sure to disable before going live.', 'cf-payfast'),
			'required' => false,
		),
		array(
			'id'   => 'merchant_id',
			'label' => __( 'Merchant ID', 'cf-payfast' ),
			'desc' => __( 'Enter your payfast ID (account number).', 'cf-payfast' ),
			'type' => 'text',
			'required' => false,
			'magic' => false,
		),
		array(
			'id'   => 'merchant_key',
			'label' => __( 'Merchant Key', 'cf-payfast' ),
			'desc' => __( 'Enter your payfast application key.', 'cf-payfast' ),
			'type' => 'text',
			'required' => true,
			'magic' => false,
		),
		array(
			'id' => 'email_address',
			'label' => __( 'Customer Email Address', 'cf-payfast' ),
			'required' => true,
		),
		array(
			'id' => 'name_first',
			'label' => __( 'Customer First Name', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'name_last',
			'label' => __( 'Customer Last Name', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'cell_number',
			'label' => __( 'Customer Cell No.', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'initial_amount',
			'label' => __( 'Initial Amount', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'payment_method',
			'label' => __( 'Payment Method', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'item_name',
			'label' => __( 'Item Name', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'item_description',
			'label' => __( 'Item Description', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'recurring',
			'label' => __( 'recurring', 'cf-payfast' ),
			'type' => 'checkbox',
			'required' => false,
		),
		array(
			'id' => 'recurring_amount',
			'label' => __( 'Billing Amount', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'frequency',
			'label' => __( 'Frequency', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'billing_cycles',
			'label' => __( 'Billing Cycles', 'cf-payfast' ),
			'required' => false,
		),



	);

	return $fields;

}


/**
 * Perform redirect to PayFast
 *
 * @since 0.1.0
 *
 * @uses "caldera_forms_submit_return_redirect" filter
 *
 * @param $url
 * @param $form
 * @param $processid
 *
 * @return string URL to redirect to
 */
function cf_payfast_redirect($url, $form, $processid){
	global $transdata;

	if( ! empty( $transdata['cf_payfast' ][ $processid ][ 'url' ] ) && empty( $_GET['error'] ) && empty( $_GET['error_description'] ) ) {
		$saved_url = $transdata['cf_payfast' ][ $processid ][ 'url' ];

		return $saved_url;
	}

	return $url;
}


/**
 * Setup return URL and cancel URL for PayFast.
 *
 * @since 0.1.0
 *
 * @uses "caldera_forms_submit_start_processors" filter
 *
 * @param $form
 * @param $referrer
 * @param $process_id
 *
 * @return array|void
 */
function cf_payfast_express_set_transient($form, $referrer, $process_id ){
	global $transdata;

	if ( isset( $transdata['cf_payfast'][ 'return_url' ] ) && isset( $transdata['cf_payfast']['cancel_url'] ) ) {
		return $transdata;
	} else {
		$return_url = $referrer['scheme'] . '://' . $referrer['host'] . $referrer['path'];
		
		// Send the user back to form if they cancel.
		$transdata['cf_payfast']['cancel_url'] = $return_url;

		$queryvars = array(
			'cf-payfast-payment-confirmation' => 'payfast',
			'cf_tp' => $transdata['transient'],
			'processid' => $transdata['transient'],

		);

	if ( ! empty( $referrer['query'] ) ) {
		$queryvars = array_merge($referrer['query'], $queryvars);
	}
		$return_url = add_query_arg( $queryvars, $return_url );

		$transdata['cf_payfast'][ 'return_url' ] = urlencode( $return_url );
	}

	return $transdata;

}
