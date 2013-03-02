<?php

namespace Fila0\Utils;

class Api {
	private $app;
	private $method;
	private $apikey;
	private $apisecret;
	private $format;
	private $debug;
	private $import;
	private $currency;
	private $user;
	private $project;
	private $results;
	private $error;

	function __construct ($app, $slug, $params) {
		$this->app = $app;
		$this->method = $slug;
		$this->apikey = $params['apikey'];
		$this->apisecret = $params['apisecret'];
		$this->format = $params['format'];
		if (isset($params['debug']) && 1 == $params['debug']) $this->debug = true;
		else $this->debug = false;
		$this->import = $params['import'];
		$this->currency = $params['currency'];
		$this->user = 0;
		$this->project = 0;
		$this->results = array(); //array("status" => "ok", "datas" => "");
		$this->error = array();  //array("status" => "error", "code" => "");
		return;
	}

	private function  isValidated () {
		$user = $this->app['db']->fetchAll("SELECT * FROM users WHERE api_key = '".$this->apikey."' AND api_secret = '".$this->apisecret."' LIMIT 0,1");
		if (isset($user[0]) && $user[0]['id'] > 0) {
			$this->user = $user[0]['id'];
			return true;
		}
		else return false;
	}
	
	public function execute () {
		if ($this->isValidated()) {
			if ($this->method == 'isServerUp') {
				$this->results = array ("status" => "OK", "datas" => "Server is Up");
				return true;
			}
			else if ($this->method == 'insertDonation') {
				$project = $this->app['db']->fetchAll("SELECT project_id FROM `projects_users` WHERE `id` = ".$this->user." AND `status` = 'current' LIMIT 0,1");
				if (isset($project[0]['project_id']) && $project[0]['project_id'] > 0) {
					$this->project = $project[0]['project_id'];
					//Validamos los datos básicos
					if ($this->import > 0 && $this->currency != '') {
						$sql = "INSERT INTO `filacero`.`donations` (
						`id`,
						`import` ,
						`currency` ,
						`user_id` ,
						`project_id` ,
						`date_stored`
						)
						VALUES (
						NULL , '".$this->import."', '".$this->currency."', '".$this->user."', '".$this->project."',
						CURRENT_TIMESTAMP
						)";
						if (!$this->debug) $project = $this->app['db']->executeUpdate($sql);		
						$this->results = array ("status" => "OK", "datas" => "Donation accepted");
						return true;
					}
					else {
					$this->error = array ("status" => "error", "code" => "003");
					return false;
				}
					
				}
				else {
					$this->error = array ("status" => "error", "code" => "002");
					return false;
				}

			}
		}
		else {
			$this->error = array ("status" => "error", "code" => "001");
			return false;
			
		}
	}		

	public function showResults() {
		if ($this->format == 'xml') {
			$this->results['format'] = 'xml';
			return $this->results;	
		}
		else {
			$datas = array('format' => 'json', "json" => json_encode($this->results));
			return $datas;	
		}
	}

	public function showError() {
		if ($this->format == 'xml') {
			$this->error['format'] = 'xml';
			return $this->error;	
		}
		else {
			$datas = array('format' => 'json', "json" => json_encode($this->error));
			return $datas;	
		}	}


} #class Api

?>
