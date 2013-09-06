<?php

	/* Supports the Crowdmap Class to help make requests */

	class Crowdmap_Web {

		// Method - GET, POST, PUT, DELETE
		// URL - Complete URL
		// Data - Data being posted or put as an assoc array
		public static function request($method,$url,$data=array())
		{
			// We work with JSON
			$headers = array(
				'Accept: application/json',
				'Content-Type: application/json',
			);
			$data = json_encode($data);

			$handle = curl_init();
			curl_setopt($handle, CURLOPT_URL, $url);
			curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

			if(isset($_SERVER['HTTP_USER_AGENT'])) {
				curl_setopt($handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
				//file_put_contents("./debugging.txt", $_SERVER['HTTP_USER_AGENT'] . "\n\n", FILE_APPEND);
			} else {
				curl_setopt($handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/536.5 (KHTML, like Gecko) CrowdmapFrontend Chrome/19.0.1084.9 Safari/536.5');
				//file_put_contents("./debugging.txt", 'NO USER AGENT' . "\n\n", FILE_APPEND);
			}

			switch($method)
			{
				case 'GET':
					break;

				case 'POST':
					curl_setopt($handle, CURLOPT_POST, true);
					curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
					break;

				case 'PUT':
					curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
					curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
					break;

				case 'DELETE':
					curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
					break;

				default:
					die('Invalid method');
			}

			$response = curl_exec($handle);
			$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

			$GLOBALS['Crowdmap_API_Calls']++;

			return array(
						'response' => $response,
						'code'     => $code,
						'url'      => $url,
					);
		}

	}
