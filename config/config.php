<?php
	// Config stuff. Dude.
	class config {

		public $site_title = 'Crowdmap Lite';

		public $base_url = 'http://crowdmaplite.osx';

		// Crowdmap API
		public $api = array(
			'endpoint'   => 'https://api.crowdmap.new/v1',
			'privateKey' => 'HMgcYgacPerPZmgo', // Throw Away Keys for testing
			'publicKey'  => 'fezpasywFZPRKatp'
			); // No trailing slash, include v1

		function __construct(){
			$this->base_url = $this->current_url();
			return true;
		}

		// Pass an array to replace keys in the standard config
		function api($api){
			if(isset($api['endpoint']))   $this->api['endpoint']   = $api['endpoint'];
			if(isset($api['privateKey'])) $this->api['privateKey'] = $api['privateKey'];
			if(isset($api['publicKey']))  $this->api['publicKey']  = $api['publicKey'];
			return true;
		}

		function current_url(){
			return sprintf(
			"%s://%s%s",
			isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
				$_SERVER['HTTP_HOST'],
				$_SERVER['REQUEST_URI']
			);
		}
	}

	$config = new config;
