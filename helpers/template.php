<?php

// Master class, the only class templates should be interacting with

class t {

	public $crowdmap;

	// RESS
	public $ress;
	public $featureCapabilities;
	public $UXprefix;

	public $site_name;
	public $page_title;
	public $defaultWidth;
	public $page_type;

	// Container for SEO-related header tags. Processed by header view.
	public $seo = array();

	// Array of all our map objects
	public $maps = array();

	// Passed parameters (GET, POST, etc)
	public $params;

	// This is the primary geojson URL that needs to be mapped.
	public $primary_geojson = '';

	// Simple per script execution "cache" for map settings
	public $map_settings = '';


	function __construct($bypass_construction=false) {

		global $app;

		// Include the arteries of our application
		$this->crowdmap = new Crowdmap;
		// Save params
		// TODO: Do we need to do sanitization here?
		$this->params = $app->request()->params();
	}

	// Returns a string for the key of the baselayer we should be showing
	public function display_baselayer()
	{
		global $Me;

		// This is the users preference as default baselayer
		$display_baselayer = $Me->Baselayer();

		$plus = false;
		if($Me->PlusSubscriber())
			$plus = true;

		$display_baselayer = $Me->Baselayer(); // will pull default from config if not authenticated

		if (isset($_GET['for_map_id']) AND ! is_null($_GET['for_map_id']) AND $_GET['for_map_id'] != 'null') {

			$map_id = $_GET['for_map_id'];
			// Hey, we're on a map. This map may have a paying subscriber who wants
			//  to show off their awesome map tiles. We need to allow these to be
			//  viewed by everyone, regardless of status.
			$req = array(
				'method' => 'GET',
				'path' => array($map_id,'settings/baselayer')
			);
			$resp = $this->crowdmap->maps($req);
			if (isset($resp->maps_settings[0]->value))
				$display_baselayer = $resp->maps_settings[0]->value;

			// If we can't find the baselayer, just default to users preference
		} else if (isset($_GET['for_user_id']) AND ! is_null($_GET['for_user_id']) AND $_GET['for_user_id'] != 'null') {

			// Set the baselayer if we are on an individuals page
			$user_id = $_GET['for_user_id'];
			$req = array(
				'method' => 'GET',
				'path' => array($user_id),
				'params' => 'users.baselayer'
			);
			$resp = $this->crowdmap->users($req);

			if (isset($resp->users[0]->baselayer))
				$display_baselayer = $resp->users[0]->baselayer;
		}

		return $display_baselayer;

	}

	// map is a map object
	public function save_map($map)
	{
		$this->maps[$map->id()] = $map;
	}

	public function visitors_ip()
	{
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] !== '127.0.0.1' && $_SERVER['HTTP_X_FORWARDED_FOR'] !== '::1' && $_SERVER['HTTP_X_FORWARDED_FOR'] !== '0:0:0:0:0:0:0:1')
		{
			return trim($_SERVER['HTTP_X_FORWARDED_FOR']);
		}

		return trim($_SERVER['REMOTE_ADDR']);
	}

	// Returns an object with location information for the users
	//   current IP address, false if we can't get it
	public function visitors_location()
	{
		$ip = $this->visitors_ip();

		if ($ip == '127.0.0.1')
		{
			// If you're coming from localhost, get the servers IP
			try {
				$ip = trim(file_get_contents('http://icanhazip.com/'));
			} catch(Exception $e) {
				// So things don't choke if I'm working offline. -Evan
				$ip = '166.147.104.163';
			}
		}

		$gi = geoip_open(__dir__."/../libraries/geoip/database/GeoLiteCity.dat",GEOIP_STANDARD);
		$record = geoip_record_by_addr($gi,$ip);
		geoip_close($gi);
		return $record;
	}




	// ***************************** Config Per Page Type ***************************

	// Render the template as a "post" page
	public function as_post()
	{
		$this->page_type = 'post';

		$post = $this->getPosts($this->post_id,array('users.name'));
		$this->page_title = $post['users']['name'];
	}

	// Render the template as a "map" page
	public function as_map()
	{
		$this->page_type = 'map';

		$map = $this->getMaps($this->map_id,array('maps.name'));

		$this->page_title = $map['name'];
	}

	// Render the template as a "profile" page
	public function as_profile()
	{
		$this->page_type = 'profile';
		$this->page_title = 'Derpy Herperton';
	}




	// ***************************** Blanket Grab Data ***************************
	public function get_post($post_id)
	{
		// Just make sure post_id is an int
		$post_id = (int)$post_id;

		$req = array(
			'method' => 'GET',
			'path' => array($post_id),
			//'parameters' => array('fields'=>'posts.message,users.username'),
		);
		$posts = $this->crowdmap->posts($req);

		if ( ! isset($posts->posts[0]))
		{
			// There is no post to show so put them on the homepage.
			global $app;
			$app->redirect('/');
		}

		// Just return the one post
		return $posts->posts[0];
	}

	public function get_post_maps($post_id)
	{
		$post_id = (int)$post_id;

		$req = array(
			'method' => 'GET',
			'path' => array($post_id,'maps'),
			//'parameters' => array('fields'=>'posts.message,users.username'),
		);
		$maps = $this->crowdmap->posts($req);

		// Return all the maps associated with this post
		return $maps->maps;
	}

	public function get_posts_from_map($map_id,$params=false)
	{
		$req = array(
			'method' => 'GET',
			'path' => array($map_id,'posts'),
			'parameters' => $params
		);
		$posts = $this->crowdmap->maps($req);

		// Return all the posts
		return $posts;
	}

	public function get_map($map_id)
	{
		$req = array(
			'method' => 'GET',
			'path' => array($map_id)
		);
		$maps = $this->crowdmap->maps($req);

		if (!isset($maps->maps[0]))
			return FALSE; // There's no map.

		// Just return the one map
		return $maps->maps[0];
	}

	public function get_map_tags($map_id)
	{
		$req = array(
			'method' => 'GET',
			'path' => array($map_id,'tags')
		);
		$maps_tags = $this->crowdmap->maps($req);

		return $maps_tags->maps_tags;
	}

	public function get_map_collaborators($map_id,$params=false)
	{
		if ($params == false) $params = array();
		$req = array(
			'method' => 'GET',
			'path' => array($map_id,'collaborators'),
			'parameters' => $params
		);
		$maps_collaborators = $this->crowdmap->maps($req);

		return $maps_collaborators->maps_collaborators;
	}

	public function get_map_settings($map_id)
	{
		if (isset($this->map_settings) AND !empty($this->map_settings))
			return $this->map_settings;

		$req = array(
			'method' => 'GET',
			'path' => array($map_id,'settings')
		);
		$maps_settings = $this->crowdmap->maps($req);

		$this->map_settings = $maps_settings->maps_settings;

		return $this->map_settings;
	}

	public function map_setting($map_id,$setting_name)
	{

		$settings = self::get_map_settings($map_id);
		foreach($settings AS $setting)
		{
			if ($setting->setting == $setting_name)
				return $setting->value;
		}

		// Check for defaults here
		if ($setting_name == 'baselayer')
			return $config->default_baselayer;

		return NULL;
	}

	public function get_media($media_id)
	{
		$media_id = (int)$media_id;

		if (empty($media_id))
			return false;

		$req = array(
			'method' => 'GET',
			'path' => array($media_id)
		);
		$media = $this->crowdmap->media($req);

		if (empty($media->media))
			return false;

		// Just return the one post
		return $media->media[0];
	}

	public function get_map_post_count($map_id)
	{
		$map_id = $map_id;

		$req = array(
			'method' => 'GET',
			'path' => array($map_id,'posts'),
			'parameters' => array('count'=>'true')
		);
		$count = $this->crowdmap->maps($req);

		// Just return the one post
		return $count->count;
	}

	public function get_map_followers_count($map_id)
	{
		$req = array(
			'method' => 'GET',
			'path' => array($map_id,'followers'),
			'parameters' => array('count'=>'true')
		);
		$count = $this->crowdmap->maps($req);

		// Just the map follower count
		return $count->count;
	}

	public function get_map_followers($map_id)
	{
		$req = array(
			'method' => 'GET',
			'path' => array($map_id,'followers'),
			'parameters' => array('default'=>'/assets/img/content/anonymous.png')
		);
		$followers = $this->crowdmap->maps($req);

		// Just the map follower count
		return $followers->following_maps;
	}

	public function get_maps()
	{
		//'/maps/'

		$req = array(
			'method' => 'GET'
		);
		$maps = $this->crowdmap->maps($req);

		// Return all associated maps
		return $maps->maps;
	}

	public function get_associated_maps($user_id,$params=false)
	{
		//'/users(/:user_id)/maps/associated/'
		if ($params == false) $params = array();

		$req = array(
			'method' => 'GET',
			'path' => array($user_id,'maps/associated'),
			'parameters' => $params
		);
		$maps = $this->crowdmap->users($req);

		// Return all associated maps
		return $maps->maps;
	}

	public function get_map_association($map_id,$user_id)
	{
		if ($map_id == FALSE OR $user_id == FALSE)
			return array();

		$assoc = $this->get_associated_maps($user_id,array('fields'=>'maps.map_id,maps.subdomain,maps.association','limit'=>1000));

		foreach ($assoc AS $map)
		{
			if (($map->map_id == $map_id OR $map->subdomain == $map_id) AND isset($map->association))
			{
				return $map->association;
			}
		}

		return array();
	}

	public function get_user_maps_associated($user_id,$params=false)
	{
		if ($params == false) $params = array();
		///users(/:user_id)/maps/associated/
		$req = array(
			'method' => 'GET',
			'path' => array($user_id,'maps','associated'),
			'parameters' => $params
		);
		$assoc = $this->crowdmap->users($req);

		return $assoc->maps;
	}

	public function get_user_maps_associated_count($user_id,$params=false)
	{
		if ($params == false) $params = array();
		$req = array(
			'method' => 'GET',
			'path' => array($user_id,'maps','associated'),
			'parameters' => array('count'=>'true')
		);
		$assoc = $this->crowdmap->users($req);

		return $assoc->maps;
	}

	public function get_user_following($user_id,$params=false)
	{
		if(!$user_id) return;

		$req = array(
			'method' => 'GET',
			'path' => array($user_id,'follows'),
			'parameters' => $params
		);
		$following = $this->crowdmap->users($req);

		if(isset($following->users))
			return $following->users;
		else
			return array();
	}

	public function get_user_followers($user_id,$params=false)
	{
		if ($params == false) $params = array();
		$req = array(
			'method' => 'GET',
			'path' => array($user_id,'followers'),
			'parameters' => $params
		);
		$following = $this->crowdmap->users($req);

		if(isset($following->users))
			return $following->users;
		else
			return array();
	}

	public function get_user_followers_count($user_id)
	{
		$req = array(
			'method' => 'GET',
			'path' => array($user_id,'followers'),
			'parameters' => array('count'=>'true')
		);
		$count = $this->crowdmap->users($req);

		// Just the map follower count
		return $count->count;
	}

	public function get_user_following_count($user_id)
	{
		$req = array(
			'method' => 'GET',
			'path' => array($user_id,'follows'),
			'parameters' => array('count'=>'true')
		);
		$count = $this->crowdmap->users($req);

		// Just the map follower count
		return $count->count;
	}

	public function get_user_maps_owns_count($user_id)
	{
		$req = array(
			'method' => 'GET',
			'path' => array($user_id,'maps','owns'),
			'parameters' => array('count'=>'true')
		);
		$count = $this->crowdmap->users($req);

		// Just the map follower count
		return $count->count;
	}

	public function get_user_posts_count($user_id)
	{
		$req = array(
			'method' => 'GET',
			'path' => array($user_id,'posts'),
			'parameters' => array('count'=>'true')
		);
		$count = $this->crowdmap->users($req);

		// Just the map follower count
		return $count->count;
	}

	public function pagination($currentpage, $totalpages, $pageurl = '', $linkclass = '')
	{
		if(!is_numeric($currentpage) || !is_numeric($totalpages)) return;

		if(!$pageurl) {
			global $app;
			$pageurl = $app->request()->getRootUri() . $app->request()->getResourceUri();
		}

		$out = '<ul class="pagination pagination-horizontal">';

		for($p = 0; $p < $totalpages; $p++) {
			if($p == $currentpage)
				$out .= '<li class="pagination-current-page">' . ($currentpage + 1) . '</li>';
			else
				$out .= '<li><a class="' . $linkclass . '" href="' . $pageurl . '?page=' . ($p + 1) . '">' . ($p + 1) . '</a></li>';
		}

		echo $out . '</ul>';
	}

	/*
	This transforms @username and #hashtag links to use the appropriate base URI.
	 */
	public function parseContentBody($text) {
		try {
			$doc = new DOMDocument('1.0', 'UTF-8');
			$doc->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $text);

			$anchors = $doc->getElementsByTagName('a');
			$i = $anchors->length - 1;
			while ($i > -1) {
				$anchor = $anchors->item($i);
				$link = $anchor->getAttribute('href');
				$rel = $anchor->getAttribute('rel');

				if($rel == 'username' || $rel == 'tag') {

					if($rel == 'username')
						$anchor->setAttribute('href', $config->base_url . '/user/' . urlencode(strtolower($link)));
					elseif($rel == 'tag')
						$anchor->setAttribute('href', $config->base_url . '/search/?q=%23' . urlencode($link));

					$anchor->setAttribute('class', $rel);
					$anchor->removeAttribute('rel');

				}

				$i--;
			}

			return trim(preg_replace(array("/^\<\!DOCTYPE.*?<body>/si", "!</body></html>$!si"), "", $doc->saveHTML()));
		} catch(Exception $e) {
			// If there's bad HTML then we can't process it. We'll have to deliver as-is.
			// This shouldn't happen often. Server-side HTML sanitization is really good about this.
		}

		return $text;
	}

	public function transformLinks($text)
	{
		// Transform URLs in descriptions into links.
		if(strlen($text)) {
			$regex = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

			if (preg_match_all($regex, $text, $urls)) {
				$matches = array_unique($urls[0]);

				$baseDomain = parse_url($config->base_url);
				$baseDomain = $baseDomain['host'];

				foreach($matches as $match) {

					// Open links outside of Crowdmap in a new tab.
					// Also apply nofollow to avoid people abusing this for Google Juice.
					$target = '';
					$_match = parse_url($match);
					if(isset($_match['host']) && substr($_match['host'], -1 * strlen($baseDomain)) !== $baseDomain) {
						$target = 'class="external" rel="nofollow" target="_blank"';
					}

					$linkShown = $match;

					if(substr($linkShown, -1) === '/')
						$linkShown = substr($linkShown,	0, -1);

					if(substr($linkShown, 0, 12) === 'https://www.')
						$linkShown = substr($linkShown,	12);

					if(substr($linkShown, 0, 11) === 'http://www.')
						$linkShown = substr($linkShown,	11);

					if(substr($linkShown, 0, 8) === 'https://')
						$linkShown = substr($linkShown,	8);

					if(substr($linkShown, 0, 7) === 'http://')
						$linkShown = substr($linkShown,	7);

					$text = str_replace($match, "<a href=\"{$match}\" {$target}>{$linkShown}</a>", $text);
				}
			}

			// @username
			$text = preg_replace('/(^|\s)@(\w+)/', '\1@<a href="' . $config->base_url . '/user/\2">\2</a>', $text);

			// #hashtag
			$text = preg_replace('/(^|\s)#(\w+)/', '\1#<a href="' . $config->base_url . '/search/?q=%23\2">\2</a>', $text);
		}

		return $text;
	}

	public function time_elapsed_string($ptime)
	{
		$etime = time() - $ptime;

		if ($etime < 1)
		{
			return '0 seconds';
		}

		$a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
					30 * 24 * 60 * 60       =>  'month',
					24 * 60 * 60            =>  'day',
					60 * 60                 =>  'hour',
					60                      =>  'minute',
					1                       =>  'second'
					);

		foreach ($a as $secs => $str)
		{
			$d = $etime / $secs;
			if ($d >= 1)
			{
				$r = round($d);
				return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
			}
		}
	}



}

// Prepare template helper
$t = new t;
