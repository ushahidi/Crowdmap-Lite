<!DOCTYPE html>
<html lang="en">
<head>
	<title>Crowdmap Lite</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link href="/assets/css/bootstrap.6.min.css" rel="stylesheet" media="screen">
	<link href="/assets/css/site.css" rel="stylesheet" media="screen">

	<script src="/assets/js/jquery.js"></script>
	<script src="/assets/js/bootstrap.js"></script>

	<script>
		var root = {};
		root.publicKey  = "<?=config::$api['publicKey']?>";
		root.privateKey = "<?=config::$api['privateKey']?>";
		root.endpoint   = "<?=config::$api['endpoint']?>";
		<?php if(isset($t->map->subdomain)) { ?>
		root.subdomain  = "<?=$t->map->subdomain?>"
		<?php } ?>
	</script>

	<script src="/assets/js/hmac-sha1.js"></script>
	<script src="/assets/js/site.js"></script>

	<?php if(isset($t->map)) { ?>
	<link rel="stylesheet" href="/assets/leaflet/leaflet.css" />
	<!--[if lte IE 8]>
		<link rel="stylesheet" href="/assets/leaflet/leaflet.ie.css" />
	<![endif]-->
	<script src="/assets/leaflet/leaflet.js"></script>
	<?php } ?>
</head>
<body>

<div class="container">

	<nav class="navbar navbar-default navbar-fixed-top" role="navigation">

		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="/"><span class="glyphicon glyphicon-map-marker"></span> Crowdmap Lite</a>
		</div>

		<?php if(!AUTHENTICATED): ?>

		<p class="navbar-text navbar-right"><a href="/login/?redirect=<?=urlencode('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])?>"><button type="submit" class="btn btn-default">Log in @ Crowdmap.com</button></a>

		<?php else: ?>

		<p class="navbar-text navbar-right">Hello, <a href="#"><?=$Me->Username(); ?></a>. <small><a href="/logout/" class="text-muted">Log out</a></small></p>

		<?php endif; ?>

	</nav>

	<div class="row">

