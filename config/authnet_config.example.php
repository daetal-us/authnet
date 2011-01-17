<?php
class AUTHNET_CONFIG {
	var $config = array(
		'datasource' => 'Authnet.AuthnetSource',
		'login' => '*******', // api login
		'key' => '**************', // api key
		'server' => 'test', // run on the test/development server 
		#'server' => 'live', // run on the actual, live server
		'test_request' => true, // force all transcations to be "test only"
		#'test_request' => false, // allow transcations to occur
		
		/*
		# -- Configuration options for logging the interactions 
		'logModel' => null, // default: AuthnetTransactionLog
		'logModel.useTable' => null, // default: null (authnet_transaction_logs)
		# fields: (id, status, transaction_id, response, response_reason, avs_response, input, error, url, data)
		
		# -- Testing account information, needed for unit tests
		'test_account' => '1234567890123456', // credit card number
		'test_cvv' => '123',
		'test_expire' => '0120',
		'test_name' => 'test name',
		'test_address' => '123 address street',
		'test_zip' => '40206',
		*/
	);
}
?>
