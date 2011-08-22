<?php

class AuthnetTransaction extends AuthnetAppModel {

	public $useDbConfig = 'authnet';

	public $useTable = false;
	public $primaryKey = 'transaction_id';

	public $displayField = 'transaction_id';
	
	public $validate = array(
		'amount' => array(
			'numeric' => array(
				'rule' => 'numeric',
				'message' => 'Invalid amount.',
				'required' => false,
				'allowEmpty' => true
				)
			),
		'card_number' => array(
			/* 
			'cc' => array(
				'rule' => array('cc', 'fast'),
				'message' => 'Invalid credit card number.',
				'required' => false,
				'allowEmpty' => true
				)
			*/
			), 
		'expiration' => array(
			'mmyyyy' => array(
				'rule' => array('mmyyyy', 'expiration'),
				'message' => 'Invalid expiration date.',
				'required' => false,
				'allowEmpty' => true
				),
			'notExpired' => array(
				'rule' => array('notExpired', 'expiration'),
				'message' => 'Credit card is expired according to date provided.',
				'required' => false,
				'allowEmpty' => true
				)
			)

		);

	
	public function beforeSave() {
		if (!isset($this->data[$this->alias])) {
			$this->data = array($this->alias => $this->data);
		}
		if (isset($this->data[$this->alias]['expiration'])) {
			$this->data[$this->alias]['expiration'] = preg_replace('/[^0-9]/', '', $this->data[$this->alias]['expiration']);
		}
		return true;
	}

	public function mmyyyy($data) {
		$value = preg_replace('/[^0-9]/', '', current($data));
		if (strlen($value) == 4 || strlen($value) == 6) {
			return true;
		} elseif ((strlen($value) == 3 || strlen($value) == 5) && substr($value, 0, 1)!==0) {
			return true;
		}
		return false;
	}

	public function notExpired($data) {
		$value = preg_replace('/[^0-9]/', '', current($data));
		if (strlen($value) > 6) {
			return false;
		} elseif (strlen($value) > 4) {
			$year = str_pad(substr($value, -4), 4, "0", STR_PAD_LEFT);
			$month = str_pad(substr($value, 0, -4), 2, "0", STR_PAD_LEFT);
		} else {
			$year = str_pad(substr(date('Y'), 0, 2).substr($value, -2), 2, "0", STR_PAD_LEFT);
			$month = str_pad(substr($value, 0, -2), 2, "0", STR_PAD_LEFT);
		}
		$epoch = strtotime("{$year}-{$month}-01");
		return ($epoch > time());
	}

	public function exists() {
		if (!empty($this->data)) {
			if (!empty($this->data[$this->alias]['transaction_id'])) {
				$this->__exists = true;
				$this->id = $this->data[$this->alias]['transaction_id'];
				return $this->__exists;
			}
		}
		return false;
	}
	
	/**
	* Overwrite of the save() function
	* we prepare for the repsonse array, and parse the status to see if it's an error or not
	* @param mixed $data
	* @param mixed $validate true
	*/
	public function save($data = array(), $validate = true) {
		$this->response = array();
		$response = parent::save($data, $validate);
		if (!empty($this->response) && isset($this->response['status']) && $this->response['status']=="good") {
			return $this->response;
		}
		if (isset($this->response['error']) && !empty($this->response['error'])) {
			$this->validationErrors[] = $this->response['error'];
			return false;
		}
		$this->validationErrors[] = "unknown error";
		return false;
	}
	
	/**
	* Overwrite of the delete() function
	* we prepare for the repsonse array, and parse the status to see if it's an error or not
	* @param mixed $data
	* @param mixed $validate true
	*/
	public function delete($id = null) {
		$this->response = array();
		$this->data[$this->alias]['transaction_id'] = $id;
		$response = parent::delete($id);
		if (!empty($this->response) && isset($this->response['status']) && $this->response['status']=="good") {
			return true;
		}
		if (isset($this->response['error']) && !empty($this->response['error'])) {
			$this->validationErrors[] = $this->response['error'];
			return false;
		}
		$this->validationErrors[] = "unknown error";
		return false;
	}

	/**
	*
	* listSources is included in the base DataSources class 1.3 at the moment, and so the scaffold
	* resets schema to null even if the child datasource class doesn't have the method implemented.
	* Subsequently it resets _schema and attempts to run the models schema method - thus, this method.
	*/
	public function schema() {
		$this->_schema = array(
			'server' => array('type' => 'string', 'length' => '16', 'null' => false, 'default' => NULL),
			'amount' => array('type' => 'float', 'null' => false, 'default' => 0),
			'card_number' => array('type' => 'string', 'length' => '16', 'null' => false, 'default' => NULL),
			'expiration' => array('type' => 'string', 'length' => 6, 'null' => false, 'default' => NULL),
			'ccv' => array('type' => 'string', 'length' => 4, 'null' => true, 'default' => NULL),

			'recurring' => array('type' => 'boolean', 'null' => false, 'default' => 0),

			'transaction_id' => array('type' => 'string', 'length' => 255, 'null' => true, 'default' => NULL),

			'authorization_code' => array('type' => 'string', 'null' => true, 'default' => NULL),

			'invoice_num' => array('type' => 'string', 'length' => 20, 'null' => true, 'default' => NULL),
			'description' => array('type' => 'string', 'length' => 255, 'null' => true, 'default' => NULL),
			'line_items' => array('type' => 'text', 'null' => true, 'default' => NULL),

			'billing_first_name' => array('type' => 'string', 'length' => 50, 'null' => true, 'default' => NULL),
			'billing_last_name' => array('type' => 'string', 'length' => 50, 'null' => true, 'default' => NULL),
			'billing_company' => array('type' => 'string', 'length' => 50, 'null' => true, 'default' => NULL),
			'billing_street' => array('type' => 'string', 'length' => 60, 'null' => true, 'default' => NULL),
			'billing_city' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'billing_state' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'billing_zip' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'billing_country' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'billing_phone' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'billing_fax' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'billing_email' => array('type' => 'string', 'null' => true, 'default' => NULL),

			'customer_id' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'customer_ip' => array('type' => 'string', 'null' => true, 'default' => NULL),

			'shipping_first_name' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'shipping_last_name' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'shipping_company' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'shipping_street' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'shipping_city' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'shipping_state' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'shipping_zip' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'shipping_country' => array('type' => 'string', 'null' => true, 'default' => NULL),

			'taxes' => array('type' => 'text', 'null' => true, 'default' => NULL),
			'freight' => array('type' => 'text', 'null' => true, 'default' => NULL),
			'duty' => array('type' => 'text', 'null' => true, 'default' => NULL),

			'purchase_order_id' => array('type' => 'string', 'length' => 25, 'null' => true, 'default' => NULL),

			'authentication_indicator' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'cardholder_authentication' => array('type' => '', 'null' => true, 'default' => NULL),

			'other' => array('type' => 'text', 'null' => true, 'default' => NULL),

			'test_mode' => array('type' => 'boolean', 'null' => true, 'default' => NULL)
			);

		return $this->_schema;
	}
}

?>
