<?php

class AuthnetAppModel extends AppModel {
	
	public $log = array(); // false to disable
	
	public $logModel = 'AuthnetTransactionLog'; // false to disable
	
	public $config = array(
		'AuthnetPluginVersion' => '1.0', 
		'logModel' => 'Authnet.AuthnetTransactionLog', 
		'logModel.useTable' => null,
		);
	
	/**
	* Updates config from: app/config/authnet_config.php
	* Sets up $this->logModel
	* @param mixed $id
	* @param string $table
	* @param mixed $ds
	*/
	public function __construct($id = false, $table = null, $ds = null) {
		// Default minimum config
		$config = set::merge(array('login' => null, 'key' => null), $this->config);
		// Try an import the plugins/authnet/config/authnet_config.php file and merge
		// any default and datasource specific config with the defaults above
		if (App::import(array('type' => 'File', 'name' => 'AUTHNET.AUTHNET_CONFIG', 'file' => APP.'config'.DS.'authnet_config.php'))) {
			$AUTHNET_CONFIG = new AUTHNET_CONFIG();
			if (isset($AUTHNET_CONFIG->config)) {
				$config = set::merge($config, $AUTHNET_CONFIG->config);
			}
		} elseif (App::import(array('type' => 'File', 'name' => 'AUTHNET.AUTHNET_CONFIG', 'file' => 'config'.DS.'authnet_config.php'))) {
			if (isset($AUTHNET_CONFIG->config)) {
				$config = set::merge($config, $AUTHNET_CONFIG->config);
			}
		}
		// Add any config from Configure class that you might have added at any
		// point before the model is instantiated.
		if (($configureConfig = Configure::read('AUTHNET.config')) != false) {
			$config = set::merge($config, $configureConfig);
		}
		// double-check we have required keys
		if (empty($config['login']) || empty($config['login'])) {
			trigger_error(__d('authnet', "Invalid AUTHNET Configuration, missing 'login' field.", true), E_USER_WARNING);
			die();
		} elseif (empty($config['key']) || empty($config['key'])) {
			trigger_error(__d('authnet', "Invalid AUTHNET Configuration, missing 'key' field.", true), E_USER_WARNING);
			die();
		}
		$this->config = $config;
		
		// initialize extras: transaction log model
		if (!empty($this->config['logModel'])) {
			if (App::import('model', $this->config['logModel'])) {
				$this->logModel = ClassRegistry::init(array_pop(explode('.', $this->config['logModel'])));
				if (isset($this->config['logModel.useTable']) && $this->config['logModel.useTable']!==null) {
					$this->logModel->useTable = $this->config['logModel.useTable'];
				}
			}
		}
		
		
		ConnectionManager::create($this->useDbConfig, $config);
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		parent::__construct($id, $table, $ds);
		
	}
	
	/**
    * Simple function to return the $config array
    * @param array $config if set, merge with existing array
    * @return array $config
    */
	public function config($config = array()) {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		if (!empty($config) && is_array($config)) {
			$db->config = set::merge($db->config, $config);
		}
		return $db->config;
	}
}

?>
