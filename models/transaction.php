<?php

class Transaction extends AuthnetAppModel {
	
		public $useTable = false;
		
		public $useDbConfig = 'authnet';
		
		public $validate = array(
			'amount' => array(
				'numeric' => array(
					'rule' => 'numeric',
					'message' => 'Invalid amount.',
					'required' => true
				)
			)
		);
	
}

?>
