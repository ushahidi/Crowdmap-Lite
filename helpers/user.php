<?php

$Me = new User(); // $Me will be reserved for the current, logged in user.

class UserAnonymous {
	function Authenticated() {
		return false;
	}

	function UserID() {
		return 1;
	}

	function SessionID() {
		return NULL;
	}

	function CrowdmapID() {
		return NULL;
	}

	function RecurlyID() {
		return NULL;
	}

	function InstagramUserID() {
		return NULL;
	}

	function InstagramAccessToken() {
		return NULL;
	}

	function TwitterAccessToken() {
		return NULL;
	}

	function Username() {
		return _("anonymous");
	}

	function Name() {
		return _("Anonymous");
	}

	function Media() {
		return array();
	}

	function Avatar() {
		return $config->static_url . '/assets/img/content/anonymous.png';
	}

	function Baselayer() {
		return $config->default_baselayer;
	}

	function InstagramAutoPost() {
		return false;
	}

	function TwitterAutoPost() {
		return false;
	}

	function TwitterAutoPostRetweets() {
		return false;
	}

	function TwitterAutoTweet() {
		return false;
	}

	function Badges() {
		return array();
	}

	function Biography() {
		return '';
	}

	function Notifications($markAsRead = FALSE) {
		return array();
	}

	function Email() {
		return '';
	}

	function Emails() {
		return array();
	}

	function PlusSubscriber() {
		return false;
	}

	function DateRegistered() {
		return false;
	}

	function Raw() {
		return array();
	}
}

class User {

	private $profileData = NULL;

	function __construct($user = null, $password = null, $userObject = null) {
		$this->profileData = null;

		if ($userObject && isset($userObject->user_id)) {
			$this->profileData = $userObject;
			return true;
		} elseif($user) {
			return $this->Set($user, $password);
		}
	}

	function Set($user, $password = null) {
		$this->profileData = null;

		if($user = getUser($user, $password)) {
			if(isset($user->user_id) && isset($user->session_id)) {
				$this->profileData = $user;
			}
		}

		return (bool)$this->profileData;
	}

	function Authenticated() {
		if(!$this->profileData) return false;
		return($this->profileData->authenticated);
	}

	function UserID() {
		if(!$this->profileData) return false;
		return($this->profileData->user_id);
	}

	function SessionID() {
		if(!$this->profileData) return false;
		return($this->profileData->session);
	}

	function CrowdmapID() {
		if( ! isset($this->profileData->crowdmap_id)) return false;
		return $this->profileData->crowdmap_id;
	}

	function RecurlyID() {
		if( ! isset($this->profileData->crowdmap_id)) return false;
		return md5($this->profileData->crowdmap_id);
	}

	function InstagramUserID() {
		// TODO: Do we need to implement this?
		return NULL;
	}

	function InstagramAccessToken() {
		if(!$this->Authenticated()) return false;

		$Crowdmap = new Crowdmap();

		if($response = $Crowdmap->__call("/session/instagramtoken/", array('parameters' => array('user_id' => $this->CrowdmapID(), 'session_id' => $this->CrowdmapSession())))) {
			if(isset($response->token)) {
				return $response->token;
			}
		}

		return false;
	}

	function TwitterAccessToken() {
		if(!$this->Authenticated()) return false;

		$Crowdmap = new Crowdmap();

		if($response = $Crowdmap->__call("/session/twittertoken/", array('parameters' => array('user_id' => $this->CrowdmapID(), 'session_id' => $this->CrowdmapSession())))) {
			if(isset($response->token)) {
				return $response->token;
			}
		}

		return false;
	}

	function CrowdmapSession() {
		if( ! isset($this->profileData->crowdmap_session)) return false;
		return $this->profileData->crowdmap_session;
	}

	function Username($change = NULL) {
		return $this->profileData->username;
	}

	function Name($change = NULL) {
		if(!isset($this->profileData->name) || !strlen($this->profileData->name)) return $this->Username();
		return $this->profileData->name;
	}

	function Media() {
		return $this->profileData->media;
	}

	function Avatar() {
		if(isset($this->profileData->avatar) && !is_null($this->profileData->avatar))
			return str_replace(array('http://', 'https://'), '//', $this->profileData->avatar);
		else
			return $config->static_url . '/assets/img/content/anonymous.png';
	}

	function Baselayer() {
		if(isset($this->profileData->baselayer) AND ! empty($this->profileData->baselayer))
			return $this->profileData->baselayer;
		else
			return $config->default_baselayer;
	}

	function InstagramAutoPost() {
		if(isset($this->profileData->instagram_auto_post) AND ! empty($this->profileData->instagram_auto_post))
			return (bool)$this->profileData->instagram_auto_post;
		else
			return false;
	}

	function TwitterAutoPost() {
		if(isset($this->profileData->twitter_auto_post) AND ! empty($this->profileData->twitter_auto_post))
			return (bool)$this->profileData->twitter_auto_post;
		else
			return false;
	}

	function TwitterAutoPostRetweets() {
		if(isset($this->profileData->twitter_auto_post_retweets) AND ! empty($this->profileData->twitter_auto_post_retweets))
			return (bool)$this->profileData->twitter_auto_post_retweets;
		else
			return false;
	}

	function TwitterAutoTweet() {
		if(isset($this->profileData->twitter_auto_tweet) AND ! empty($this->profileData->twitter_auto_tweet))
			return (bool)$this->profileData->twitter_auto_tweet;
		else
			return false;
	}

	function Badges() {
		return $this->profileData->badges;
	}

	function Biography() {
		return $this->profileData->bio;
	}

	function Email() {
		if($emails = $this->Emails()) {
			return $emails[0];
		}
		return '';
	}

	function Emails() {
		if(!$this->Authenticated()) return FALSE;

		$Crowdmap = new Crowdmap();

		if($response = $Crowdmap->__call("/session/emails/", array('parameters' => array('user_id' => $this->CrowdmapID(), 'session_id' => $this->CrowdmapSession())))) {
			if(isset($response->emails)) {
				return $response->emails;
			}
		}

		return array();
	}

	// Checks if the user is currently paying for a Plus subscription
	function PlusSubscriber() {
		if (isset($this->profileData->plus))
			return (bool)$this->profileData->plus;
		else
			return false;
	}

	function Raw() {
		return $this->profileData;
	}

	function DateRegistered() {
		return $this->profileData->date_registered;
	}

	/* Non-CMID related functions below */

	function Notifications($markAsRead = FALSE) {
		$Crowdmap = new Crowdmap();
		$uid = $this->UserID();
		$return = array();

		if($response = $Crowdmap->__call("/users/{$uid}/notifications/")) {
			if(isset($response->notifications)) {
				 $return = $response->notifications;
			}
		}

		if($markAsRead == TRUE) {
			$markAsRead = new Crowdmap();
			@$markAsRead->__call("/users/{$uid}/notifications/", array('method' => 'PUT'));
		}

		return $return;
	}

	function countPosts() {
		$Crowdmap = new Crowdmap();
		$uid = $this->UserID();
		$count = 0; // Default to none. Duh.

		if($response = $Crowdmap->__call("/users/{$uid}/posts/", array('parameters' => array('count' => true)))) {
			if(isset($response->count)) {
				$count = (int)$response->count;
			}
		}

		return $count;
	}

	function countMaps() {
		$Crowdmap = new Crowdmap();
		$uid = $this->UserID();
		$count = 0; // Default to none. Duh.

		if($response = $Crowdmap->__call("/users/{$uid}/maps/collaborating/", array('parameters' => array('count' => true)))) {
			if(isset($response->count)) {
				$count = $count + (int)$response->count;
			}
		}

		if($response = $Crowdmap->__call("/users/{$uid}/maps/owns/", array('parameters' => array('count' => true)))) {
			if(isset($response->count)) {
				$count = $count + (int)$response->count;
			}
		}

		return $count;
	}

	function countFollowing() {
		$Crowdmap = new Crowdmap();
		$uid = $this->UserID();
		$count = 0; // Default to none. Duh.

		if($response = $Crowdmap->__call("/users/{$uid}/follows/", array('parameters' => array('count' => true)))) {
			if(isset($response->count)) {
				$count = (int)$response->count;
			}
		}

		return $count;
	}

	function countFollowers() {
		$Crowdmap = new Crowdmap();
		$uid = $this->UserID();
		$count = 0; // Default to none. Duh.

		if($response = $Crowdmap->__call("/users/{$uid}/followers/", array('parameters' => array('count' => true)))) {
			if(isset($response->count)) {
				$count = (int)$response->count;
			}
		}

		return $count;
	}

	function isFollowing($user) {
		$Crowdmap = new Crowdmap();
		$uid = $this->UserID();
		$following = FALSE; // Default to not. Duh.

		if($response = $Crowdmap->__call("/users/{$uid}/follows/{$user}", array())) {
			if(isset($response->success)) {
				$following = (boolean)$response->success;
			}
		}

		return $following;
	}

}

function Authenticate() {
	global $Me;
	if(isset($Me) && $Me->Authenticated()) return true;

	$Crowdmap = new Crowdmap();

	$session = (isset($_REQUEST['session']) ? filter_var($_REQUEST['session'], FILTER_SANITIZE_STRING) : NULL);

	if(!$session)
		$session = (isset($_COOKIE['session']) ? filter_var($_COOKIE['session'], FILTER_SANITIZE_STRING) : NULL);

	if($session) {
		if($Me = getUser(NULL, NULL, $session)) {

			if(isset($Me->error)) {
				$expires = time() - 60*60*24*7;
				//file_put_contents("./debugging.txt", 'ERROR: ' . $Me->error . "\n\n", FILE_APPEND);
				setCookie('session', '', $expires, '/', ".{$_SERVER['HTTP_HOST']}");
			} else {
				$expires = time() + 60*60*24*7;
				setCookie('session', $Me->SessionID(), $expires, '/', ".{$_SERVER['HTTP_HOST']}");

				define('AUTHENTICATED', TRUE);
				return true;
			}
		}
	}

	define('AUTHENTICATED', FALSE);
	return false;
}

function registerUser($email, $password, $username, $name) {
	$Crowdmap = new Crowdmap();

	return $Crowdmap->__call("/session/register/", array(
		'method'     => 'POST',
		'parameters' => array(
			'email' => $email,
			'password' => $password,
			'username' => $username,
			'name' => $name
			)));
}

function confirmRegistration($code, $user_id) {
	$Crowdmap = new Crowdmap();

	$request = array(
		'method' => 'GET',
		'path'   => array('confirm',$user_id,$code)
		);
	return $Crowdmap->session($request);
}

function getUser($user, $password = NULL, $session = NULL) {
	$Crowdmap = new Crowdmap();

	if($password || $session) {

		if($session) {

			$request = array(
				'method'      => 'GET',
				'parameters'  => array(
					'session' => $session
					)
				);

			$u = $Crowdmap->session($request);

			if($u)
				$u->session = $session;

		} else {

			$request = array(
				'method'       => 'POST',
				'path'         => array('login'),
				'parameters'   => array(
					'username' => $user,
					'password' => $password
					)
				);

			$u = $Crowdmap->session($request);

		}

		if($u && isset($u->session)) {
			$u->authenticated = true;
			$p = $Crowdmap->__call("/users/me/", array('parameters' => array('session' => $u->session)));

			if($p && isset($p->users[0])) {
				$u = (object)array_merge((array)$u, (array)$p->users[0]);
			}
		}

	} else {

		if(!$user || $user === 1 || $user === 0)
			return new UserAnonymous();

		$u = $Crowdmap->__call("/users/{$user}/", array());
		if(isset($u->users[0])) {
			$u = $u->users[0];
			$u->authenticated = false;
		} else {
			return FALSE;
		}

	}

	return new User(NULL, NULL, $u);
}

function logout()
{
	$expires = time() - 60*60*24*7;
	setCookie('session', '', $expires, '/', ".{$_SERVER['HTTP_HOST']}");
}

Authenticate();
