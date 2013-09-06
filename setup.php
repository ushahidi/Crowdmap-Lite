<?php

if(!isset($_SERVER["DB1_HOST"])){
	$db_host = 'localhost';
	$db_port = 3306;
	$db_name = 'crowdmap_lite';
	$db_user = 'root';
	$db_pass = 'root';
}else{
	$db_host = $_SERVER["DB1_HOST"];
	$db_port = $_SERVER["DB1_PORT"];
	$db_name = $_SERVER["DB1_NAME"];
	$db_user = $_SERVER["DB1_USER"];
	$db_pass = $_SERVER["DB1_PASS"];
}

try {
	$db = new PDO('mysql:host='.$db_host.';port='.$db_port.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass);
} catch(PDOException $ex) {
	var_dump($ex->getMessage());
	die();
}

$stmt = $db->query("SHOW TABLES LIKE 'settings';");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(count($results) == 0) {
	$db->query("CREATE TABLE `settings` (
					`key` varchar(50) NOT NULL DEFAULT '',
					`value` varchar(255) DEFAULT NULL,
					PRIMARY KEY (`key`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
}

$api = array();
foreach($db->query("SELECT * FROM settings") as $row) {
	// Replaces our API settings
	if($row['key'] == 'endpoint'
		|| $row['key'] == 'privateKey'
		|| $row['key'] == 'publicKey'){
		$api[$row['key']] = $row['value'];
	}

	if($row['key'] == 'site_title') {
		$config->site_title = $row['value'];
	}

}

if(count($api) >= 2) {
	// Success! We set new key values
	$config->api($row);
}else{

	// See if we filled out the signup form
	if(isset($_POST['privateKey']) AND isset($_POST['publicKey'])) {
		if(strlen($_POST['privateKey']) == 16 OR strlen($_POST['publicKey']) == 16) {
			// Valid looking keys (didn't check if they were only alpha numeric yet though)
			$stmt = $db->prepare('INSERT INTO `settings` (`key`, `value`) VALUES ("privateKey", :newvalue)');
			$stmt->execute(array(':newvalue' => $_POST['privateKey']));
			$stmt = $db->prepare('INSERT INTO `settings` (`key`, `value`) VALUES ("publicKey", :newvalue)');
			$stmt->execute(array(':newvalue' => $_POST['publicKey']));

			if(isset($_POST['site_title'])) {
				$stmt = $db->prepare('INSERT INTO `settings` (`key`, `value`) VALUES ("site_title", :newvalue)');
				$stmt->execute(array(':newvalue' => $_POST['site_title']));
			}
		}
	}



	if(!defined('AUTHENTICATED')) {
		// Chances are we haven't even tried to authenticate our user yet. We need to set this so the header view doesn't flip out
		define('AUTHENTICATED', false);
	}
	// We need to setup our application! Hurrah!
	require_once 'views/header.php';
	require_once 'views/setup.php';
	require_once 'views/footer.php';
	exit;
}
