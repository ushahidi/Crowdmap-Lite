<?php

// Keeps track of the number of times the API was queried
$GLOBALS['Crowdmap_API_Calls'] = 0;

/* Crowdmap PHP API Library */

/*
// EXAMPLE: GET all the posts in map with id 1 and return only
//          the usernames of the poster along with the post message
$request = array(
	'method' => 'GET',
	'path' => array(1,'posts'),
	'parameters' => array('fields'=>'posts.message,users.username'),
	);
var_dump($crowdmap->maps($request));
*/

// Check for functionality that the Crowdmap API relies on
if (!function_exists('curl_init')) {
  throw new Exception('Crowdmap requires the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Crowdmap requires the JSON PHP extension.');
}

// Require supporting / helper classes
require_once 'Crowdmap_Web.php';

class Crowdmap {

	function __construct()
	{
		//echo 'Crowdmap Constructed<br/>';
	}

	// Retrieves an object variable
	function __get($var_name)
	{
		if (isset($this->{$var_name}))
			return $this->{$var_name};

		return NULL;

	}

	// Sets an object variable
	function __set($var_name,$value)
	{
		$this->{$var_name} = $value;
	}

	// Magic method, should not be called directly.
	//   ie: $crowdmap->maps(ARGS) will call http://api.crdmp3.com/v1/maps/...
	public function __call($resource, $args=FALSE)
	{
		global $Me, $config;

		// Collapse args since it comes through as a nested array
		$method = 'GET';
		$resource = trim($resource, '/') . '/';
		$path = '';
		$parameters = '';
		$data = array();

		if (isset($args[0]))
			$args = $args[0];

		if (isset($args['method']))
			$method = $args['method'];

		if (isset($args['path']))
			$path = ltrim((implode('/', $args['path']) . '/'),'/');

		if(isset($Me) && $Me->Authenticated())
			$args['parameters']['session'] = $Me->SessionID();

		if(!isset($args['parameters']['session'])) {
			$date = time();
			$args['parameters']['apikey'] = 'A' . $config->api['publicKey'] . hash_hmac('sha1', "{$method}\n{$date}\n/{$resource}{$path}\n", $config->api['privateKey']);
		}

		if (isset($args['parameters']))
		{
			// First strip any whitespace that may have been passed between commas
			//$args['parameters'] = str_replace(' ','',$args['parameters']);
			$args['parameters'] = preg_replace('/\s+/', '', $args['parameters']);
			$parameters = '?' . http_build_query($args['parameters']);
		}

		$url  = rtrim($config->api['endpoint'], '/') . '/' . $resource . $path . $parameters;

		$request = Crowdmap_Web::request($method,$url,$data);
		$response = json_decode($request['response']);
		$code = $request['code'];

		if($code == 404) {
			header("Status: 404 Not Found");
		} elseif ($code == 403) {
			// The account is banned.
			header("Status: 403 Forbidden");
			exit;
		} elseif ($code == 401) {
			// The user's session token has expired. Redirect them to /login.
			setCookie('session', '', time() - 3600, '/', ".{$_SERVER['HTTP_HOST']}");
			header('Location: ' . $config->base_url . '/login?r=' . mt_rand() . '&reason=sessionexpired');
			exit;
		} elseif ($code != 200) {
			ini_set('memory_limit', '256M');

			ob_start();
			var_dump($request);
			var_dump($response);
			//var_dump(debug_backtrace());
			$debug = ob_get_clean();

			@mail('evansims@gmail.com', 'API Communication Error', "URI: {$url}\n\n{$debug}\n\n", "From: noreply@crowdmap.com\r\n");
			die("<p><strong>API communication error.</strong><br />Administrators have bee notified of this error. Please try again shortly.</p>");
		}

		return $response;
	}

	public static function apikey($method,$resource){
		$date = time();
		$args['parameters']['apikey'] = 'A' . $config->api['publicKey'] . hash_hmac('sha1', "{$method}\n{$date}\n/{$resource}\n", $config->api['privateKey']);
	}
}
