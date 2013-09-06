<?php

	require_once 'config/config.php';

	if(isset($_SERVER["DB1_HOST"]) OR 1==1){
		// Likely on Pagoda Box. Possible it's not but we're rolling with this for now.
		require_once 'setup.php';
	}

	// Composer Auto Loading
	require_once 'vendor/autoload.php';

	require_once 'libraries/Crowdmap/Crowdmap.php';

	$app = new \Slim\Slim();

	require_once 'helpers/template.php';
	require_once 'helpers/user.php';

	$app->get('/?', function () use ($t, $Me, $app, $config) {
		require_once 'views/header.php';
		require_once 'views/home.php';
		require_once 'views/footer.php';
	});

	$app->get('/create/map/?', function () use ($t, $Me, $app, $config) {
		require_once 'views/header.php';
		require_once 'views/maps/create.php';
		require_once 'views/footer.php';
	});

	$app->post('/create/map/?', function () use ($t, $Me, $app, $config) {
		$name      = $app->request()->params('name');
		$subdomain = $app->request()->params('subdomain');
		$data = array('apikey'    => $t->crowdmap->apikey('POST','/maps/'),
					  'session'   => $Me->SessionID(),
					  'name'      => $name,
					  'subdomain' => $subdomain);
		$response = web::post($config->api['endpoint'].'/maps/',$data,$_SERVER['HTTP_USER_AGENT']);

		$response = json_decode($response);

		if(isset($response->status) AND $response->status == 200 AND isset($response->maps[0]->subdomain)) {
			$app->redirect('/lite/map/'.$response->maps[0]->subdomain);
			exit;
		}

		$error = true;

		require_once 'views/header.php';
		require_once 'views/maps/create.php';
		require_once 'views/footer.php';
	});

	$app->get('/map/:subdomain/?', function ($subdomain) use ($t, $Me, $app, $config) {
		$t->map = $t->get_map($subdomain);

		if (!$t->map) $app->redirect('/');
		$t->map_association = $t->get_map_association($t->map->map_id,$Me->UserID());

		$t->posts = $t->get_posts_from_map($subdomain,array('fields'=>'posts.message,posts.date_posted,tags.tag,posts.owner_map_id,users.user_id,users.avatar,users.username,maps.name,maps.avatar,maps.subdomain,maps.approved'));

		$t->tags = array();
		$t->posters = array();
		//$t->unapproved_posts;
		foreach($t->posts->posts AS $key => $post) {

			if($post->maps[0]->approved == false) {
				$t->unapproved_posts->posts[] = $t->posts->posts[$key];
				unset($t->posts->posts[$key]);
				continue;
			}

			// Extract Tags
			foreach($post->tags AS $tag) {
				$k = $tag->tag;
				if(isset($t->tags[$k])) {
					$t->tags[$k]['count']++;
				}else{
					$t->tags[$k]['tag']   = $tag->tag;
					$t->tags[$k]['count'] = 1;
				}
			}

			// Extract Posters
			if($post->owner_map_id){
				$k = 'm'.$post->owner_map_id;
				if(isset($t->posters[$k])) {
					$t->posters[$k]['count']++;
				}else{
					$t->posters[$k]['name']    = $post->maps[0]->name;
					$t->posters[$k]['avatar']  = $post->maps[0]->avatar;
					$t->posters[$k]['count']   = 1;
				}
			}else{
				$k = 'u'.$post->users[0]->user_id;
				if(isset($t->posters[$k])) {
					$t->posters[$k]['count']++;
				}else{
					$t->posters[$k]['name']   = $post->users[0]->username;
					$t->posters[$k]['avatar'] = $post->users[0]->avatar;
					$t->posters[$k]['count']  = 1;
				}
			}
		}

		uasort($t->tags, function($a, $b){
			if ($a['count'] == $b['count']) return 0;
			return ($a['count'] > $b['count']) ? -1 : 1;
		});

		uasort($t->posters, function($a, $b){
			if ($a['count'] == $b['count']) return 0;
			return ($a['count'] > $b['count']) ? -1 : 1;
		});

		require_once 'views/header.php';
		require_once 'views/maps/map.php';
		require_once 'views/footer.php';
	});

	$app->run();
