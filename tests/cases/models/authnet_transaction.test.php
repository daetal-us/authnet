<?php

App::import('Model', 'Authnet.AuthnetTransaction');

Class AuthnetTransactionTestCase extends CakeTestCase {

	public $Transaction;
	public $fixtures = array();	
	public $deleteIds = array();	

	function startTest() {
		$this->AuthnetTransaction =& ClassRegistry::init('AuthnetTransaction');
	}
	function endTest() {
		if (!empty($this->deleteIds)) {
			foreach ( $this->deleteIds as $i => $id ) { 
				$this->AuthnetTransaction->delete($id);
				unset($this->deleteIds[$i]);
			}
		}
		unset($this->AuthnetTransaction);
		ClassRegistry::flush();
	}
	function testBasics() {
		$this->assertTrue(is_object($this->AuthnetTransaction));
	}
	function testConfig() {
		$config = $this->AuthnetTransaction->config();
		$this->assertTrue($config['test_account'], 	"Missing TESTING config: 'test_account' => 'credit-card-number'");
		$this->assertTrue($config['test_cvv'], 		"Missing TESTING config: 'test_cvv' => 'credit-card-cvv'");
		$this->assertTrue($config['test_expire'], 	"Missing TESTING config: 'test_expire' => 'credit-card-expiration-date'");
		$this->assertTrue($config['test_name'], 	"Missing TESTING config: 'test_name' => 'credit-card-name'");
		$this->assertTrue($config['test_address'], 	"Missing TESTING config: 'test_address' => 'billing-address'");
		$this->assertTrue($config['test_zip'], 		"Missing TESTING config: 'test_zip' => 'zip-code'");
		if (!isset($config['test_account']) || empty($config['test_account'])) {
			exit;
		}
		$this->assertFalse($config['test_request'], "Config: 'test_request' is set to TRUE, it should be FALSE to execute charges");
		$this->assertEqual($config['server'], "live", "Config: 'server' is not set to 'live', but it needs to be to run the full test suite");
	}
	function testCharge() {
		$this->AuthnetTransaction->log = array();
		$data = array_merge($this->__data(), array('amount' => '1.'.rand(10,99)));
		$response = $this->AuthnetTransaction->save($data);
		$this->assertTrue($response);
		$this->assertTrue($this->AuthnetTransaction->id);
		$this->assertTrue($this->AuthnetTransaction->log);
		$this->assertTrue($this->AuthnetTransaction->log[0]['status']=='good');
		$this->assertTrue($this->AuthnetTransaction->log[0]['transaction_id']);
		$this->assertTrue($this->AuthnetTransaction->response['status']=='good');
		$this->assertTrue($this->AuthnetTransaction->response['transaction_id']);
		$this->assertTrue($this->AuthnetTransaction->response['transaction_id']==$this->AuthnetTransaction->id);
		$this->deleteIds[] = $this->AuthnetTransaction->id;
	}
	function testFailAccountNumber() {
		$this->AuthnetTransaction->log = array();
		$data = array_merge($this->__data(), array('amount' => '1.'.rand(10,99), 'card_number' => '4111111111111111'));
		$response = $this->AuthnetTransaction->save($data);
		$this->assertFalse($response);
		$this->assertEqual($this->AuthnetTransaction->log[0]['status'],'declined');
		$this->assertEqual($this->AuthnetTransaction->response['status'],'declined');
		$this->assertTrue(!empty($this->AuthnetTransaction->log[0]['transaction_id']));
		$this->assertTrue(!empty($this->AuthnetTransaction->response['transaction_id']));
		$this->assertTrue(!empty($this->AuthnetTransaction->id));
		$this->assertTrue($this->AuthnetTransaction->log[0]['response_reason']=='This transaction has been declined.');
	}
	function testFailZip() {
		$this->AuthnetTransaction->log = array();
		$data = array_merge($this->__data(), array('amount' => '1.'.rand(10,99), 'billing_zip' => '77099'));
		$response = $this->AuthnetTransaction->save($data);
		$this->assertTrue($response);
		$this->assertTrue($this->AuthnetTransaction->log[0]['status']=='good');
		$this->assertTrue($this->AuthnetTransaction->response['status']=='good');
		$this->assertTrue($this->AuthnetTransaction->log[0]['transaction_id']);
		$this->assertTrue($this->AuthnetTransaction->response['transaction_id']);
		$this->assertTrue($this->AuthnetTransaction->id);
		$this->assertTrue($this->AuthnetTransaction->log[0]['response_reason']=='This transaction has been approved.');
		// the transcation is GOOD, but the Address Verification Service failed
		$this->assertTrue($this->AuthnetTransaction->log[0]['avs_response']=='No Match on Address (Street) or ZIP');
		$this->assertTrue($this->AuthnetTransaction->response['avs_response']=='No Match on Address (Street) or ZIP');
		$this->deleteIds[] = $this->AuthnetTransaction->id;
	}
	function testDelete() {
		$this->AuthnetTransaction->log = array();
		$data = array_merge($this->__data(), array('amount' => '1.'.rand(10,99)));
		$response = $this->AuthnetTransaction->save($data);
		$id = $this->AuthnetTransaction->id;
		$this->AuthnetTransaction->log = array();
		$response = $this->AuthnetTransaction->delete($id);
		$this->assertTrue($response);
		$this->assertTrue($this->AuthnetTransaction->response['status']=='good');
	}
	/**
	* A simple function to help return testing data
	*/
	function __data() {
		$config = $this->AuthnetTransaction->config();
		$nameParts = explode(' ', $config['test_name']);
		return array(
			'card_number' => $config['test_account'],
			'expiration' => $config['test_expire'],
			'ccv' => $config['test_ccv'],
			'billing_first_name' => array_shift($nameParts),
			'billing_last_name' => implode(' ', $nameParts),
			'billing_street' => $config['test_address'],
			'billing_zip' => $config['test_zip'],
			'server' => 'live',
			'test_request' => false,
			);
	}
}

?>
