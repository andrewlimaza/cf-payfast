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
		"name"				=>	__( 'PayFast for Caldera Forms', 'cf-payfast'),
		"description"		=>	__( 'Accept payments using PayFast and Caldera Forms', 'cf-payfast'),
		"author"			=>	'Yoohoo Plugins',
		"author_url"		=>	'https://yoohooplugins.com',
		"pre_processor"		=>	'cf_payfast_pre_process',
		"processor"			=>  'cf_payfast_process',
		"icon"				=>	CF_PAYFAST_URL . "payfast-icon.png",
		"template"			=>	CF_PAYFAST_PATH . "includes/config.php",
		"cf_ver"            => '1.0'

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


	// Commented this out for testing purposes. The condition below 59-60 is never met, tried var_dump on line 61 but no luck.
	/**
	 * Complete submission after coming back from payfast.
	 */
	// if( !empty( $_REQUEST['cf-payfast-payment-confirmation'] ) && 'payfast' == $_REQUEST['cf-payfast-payment-confirmation'] ){
	// 	if ( isset( $_GET[ 'processid' ] ) && isset( $transdata[ 'cf_payfast' ] ) && isset( $transdata[ 'cf_payfast' ][ $_GET[ 'processid' ] ] ) ) {



	// 		/**
	// 		 * @var Caldera_Forms_Processor_Get_Data
	// 		 */
	// 		$processor_data = $transdata[ 'cf_payfast' ][ $_GET[ 'processid' ] ][ 'process_object' ];
	// 		if ( ! is_object( $processor_data )  ){
	// 			return array(
	// 				'type' => 'error',
	// 				'note' => __( 'Error completing transaction', 'cf-payfast' )
	// 			);
	// 		}

	// 		if ( ! isset( $transdata[ $proccesid ][ 'meta'] ) ) {
	// 			$transdata[ $proccesid ][ 'meta'] = array();
	// 		}

	// 		$payment_data = $processor_data->get_values();
	// 		if ( isset( $_GET['orderId'] ) && isset( $_GET['status'] ) ) {
	// 			$transdata[ $proccesid ][ 'meta' ][ 'orderId' ] = $_GET['orderId'];
	// 			$transdata[ $proccesid ][ 'meta' ][ 'status' ] = $_GET[ 'status' ];

	// 			if ( $_GET['status'] == 'Completed' ) {
	// 				if ( $_GET['signature'] == hash_hmac( 'sha1', $_GET['checkoutId'] . '&' . $_GET['amount'], $payment_data['payfast_api_secret'] ) ) {
	// 					$transdata[ $proccesid ][ 'meta' ][ 'transaction' ] = strip_tags( $_GET[ 'transaction' ] );
	// 					$transdata[ $proccesid ][ 'meta' ][ 'clearingDate' ] = strip_tags( $_GET[ 'clearingDate' ] );

	// 					return;

	// 				} else {
	// 					$processor_data->add_error( 'Invalid Signature', 'cf-payfast' );
	// 				}
	// 			} else {
	// 				$processor_data->add_error( 'Transaction Failed', 'cf-payfast' );
	// 			}
	// 		}else{
	// 			$processor_data->add_error( 'Transaction Failed', 'cf-payfast' );
	// 		}
	// 	}

	// 	if( ! empty( $_GET['error'] ) && ! empty( $_GET[ 'error_description' ] ) ){
	// 		$processor_data->add_error( urldecode( $_GET['error_description' ] ) );
	// 	}

	// 	//If we have errors, report them and bail
	// 	$errors = $processor_data->get_errors();
	// 	if ( ! empty( $errors  ) ) {

	// 		return $errors;

	// 	}

	// 	return;

	// }

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
			'type' => 'redirect',
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

	$return_url = add_query_arg(
		array(
			'cf-payfast-payment-confirmation' => 'payfast',
			'cf_tp' => $transdata['transient'], // add in the cf_tp ( Caldera Forms Transient Process - this is a shortcut to reprocess a transient form submission - Document this please! )
			'processid' => $proccessid,
		),
		$transdata['cf_payfast'][ 'return_url' ]
	);

	// Adjust this.
	// if ( empty( $payment_data['orderId'] ) ) {
	// 	$payment_data['orderId'] = $proccessid;
	// }

	// Get the data from the PayFast Processor
	$payment_data = $processor_data->get_values();

	$body = array(
		"merchant_id" => "10006218",
		"merchant_key"=> "yn6rdi19h7qnd",
		"return_url" => url_encode( $return_url ),
		"amount" => "100",
		"item_name" => 'test'
	);


	// Set this to sandbox for testing purposes.
	$url = "https://sandbox.payfast.co.za/eng/process/";
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
			'id'   => 'payfast_account_number',
			'label' => __( 'payfast ID', 'cf-payfast' ),
			'desc' => __( 'Enter your payfast ID (account number).', 'cf-payfast' ),
			'type' => 'text',
			'required' => false,
			'magic' => false,
		),
		array(
			'id'   => 'payfast_api_key',
			'label' => __( 'API Key', 'cf-payfast' ),
			'desc' => __( 'Enter your payfast application key.', 'cf-payfast' ),
			'type' => 'text',
			'required' => false,
			'magic' => false,
		),
		array(
			'id'   => 'payfast_api_secret',
			'label' => __( 'API Secret', 'cf-payfast' ),
			'desc' => __( 'Enter your payfast application secret.', 'cf-payfast' ),
			'type' => 'text',
			'required' => false,
			'magic' => false,
		),
		array(
			'id' => 'name',
			'label' => __( 'Customer name', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'price',
			'label' => __( 'Product Price', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'quantity',
			'label' => __( 'Quantity', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'product_name',
			'label' => __( 'Product Name', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'description',
			'label' => __( 'Product Description', 'cf-payfast' ),
			'required' => false,
		),
		array(
			'id' => 'orderId',
			'label' => __( 'Order ID', 'cf-payfast' ),
			'desc' => __( 'Optional. If left blank, a random number will be used.', 'cf-payfast' ),
			'required' => false,
			'magic' => false,
		)



	);

	return $fields;

}


/**
 * Perform redirect to payfast
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
 * Setup redirect to payfast
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

	if(!empty($transdata['cf_payfast']['return_url'])){

		return $transdata;

	}else{

		// setup return urls
		$return_url = $referrer['scheme'] . '://' . $referrer['host'] . $referrer['path'];
		if ( isset( $referrer[ 'query' ] ) && ! empty( $referrer[ 'query' ] ) ) {
			$return_url = add_query_arg( $referrer[ 'query'], $return_url  );
		}
		$transdata['cf_payfast'][ 'return_url' ] = $return_url;
	}

}
