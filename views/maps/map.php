<div class="col-lg-12">

	<div class="row">
		<div class="col-lg-2"></div>
		<div class="col-lg-8">
			<h1 class="text-center"><?=$t->map->name?></h1>
			<?php if(!empty($t->map->description)) { ?>
			<p class="text-center"><?=$t->map->description?></p>
			<?php } ?>
			<p class="text-center"><small>A map lovingly curated by <?=$t->map->users[0]->name?>.</small></p>
		</div>
		<div class="col-lg-2 text-right">
			<?php if(in_array('owns', $t->map_association) OR in_array('collaborating', $t->map_association)) { ?>
			<h1><a href="#"><i class="glyphicon glyphicon-cog"></i></a></h1>
			<?php } ?>
		</div>
	</div>

	<div class="row">

		<div class="col-lg-12" style="margin-bottom:2em;">
			<div id="map" style="width: 100%; height: 200px;"></div>
		</div>

		<?php if(in_array('owns', $t->map_association) AND count($t->unapproved_posts->posts) > 0) { ?>
		<div class="col-lg-12">
			<div class="alert alert-info">
				<h2 class="text-center">Hello, <?=$Me->Name()?>. You have posts to approve or deny.</h2>
			</div>
		</div>

		<div class="col-lg-12">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Posts Awaiting Moderation</h3>
				</div>
				<div class="panel-body">
				<?php foreach($t->unapproved_posts->posts AS $post) { ?>
					<?php
						//if($post->maps[0]->approved == false) continue;

						if($post->owner_map_id > 0) {
							// Owned by a map
							$avatar = $post->maps[0]->avatar;
							$avatar_url = '/map/'.$post->maps[0]->subdomain.'/';
							$name = 'the <a href="'.$avatar_url.'">'.$post->maps[0]->name.'</a> map';
						}else{
							// Owned by a person
							$avatar = $post->users[0]->avatar;
							$avatar_url = '/user/'.$post->users[0]->username.'/';
							$name = '<a href="'.$avatar_url.'">@'.$post->users[0]->username.'</a>';
						}

						$post->maps[0]->approved;
					?>
					<div class="row">
						<div class="col-lg-1 col-md-1 col-sm-2 col-xs-2">
							<a href="<?=$avatar_url?>"><img src="<?=$avatar?>" class="img-rounded" style="width:100%"></a>
						</div>
						<div class="col-lg-8">
							<?=$post->message?>
							<?php if(isset($post->externals[0]) AND !empty($post->externals[0]->embed_html)) {
								echo $post->externals[0]->embed_html;
							}elseif(isset($post->externals[0]) AND isset($post->externals[0]->images[0]) AND !empty($post->externals[0]->images[0]->url)){
								echo '<img src="'.$post->externals[0]->images[0]->url.'" style="width:100%">';
							} ?>
							<small class="text-muted">Posted <?=$t->time_elapsed_string($post->date_posted)?> by <?=$name?></small>
						</div>
						<div class="col-lg-3 text-right">
							<button type="button" class="btn btn-success btn"><i class="glyphicon glyphicon-ok"></i> Approve</button>
							<button type="button" class="btn btn-danger btn"><i class="glyphicon glyphicon-remove"></i> Deny</button>
						</div>
					</div>
					<hr/>
				<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>


		<div class="col-lg-4">

			<?php if(count($t->tags) > 0) { ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Tags</h3>
				</div>
				<div class="list-group">
				<?php foreach($t->tags AS $tag) { ?>
					<a class="list-group-item"><span class="badge"><?=$tag['count']?></span><?=$tag['tag']?></a>
				<?php } ?>
				</div>
			</div>
			<?php } ?>

			<?php if(count($t->posters) > 0) { ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Posters</h3>
				</div>
				<div class="list-group">
				<?php foreach($t->posters AS $poster) { ?>
					<a class="list-group-item"><span class="badge"><?=$poster['count']?></span><img src="<?=$poster['avatar']?>" class="img-rounded" style="width:30px"> <?=$poster['name']?></a>
				<?php } ?>
				</div>
			</div>
			<?php } ?>

		</div>

		<div class="col-lg-8">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Posts</h3>
				</div>
				<div class="panel-body">

				<div class="row text-center">
					<button type="button" class="btn btn-default btn-lg"><i class="glyphicon glyphicon-plus"></i> Add your own post!</button>
				</div>

				<hr/>


				<?php foreach($t->posts->posts AS $post) { ?>
					<?php
						//if($post->maps[0]->approved == false) continue;

						if($post->owner_map_id > 0) {
							// Owned by a map
							$avatar = $post->maps[0]->avatar;
							$avatar_url = '/map/'.$post->maps[0]->subdomain.'/';
							$name = 'the <a href="'.$avatar_url.'">'.$post->maps[0]->name.'</a> map';
						}else{
							// Owned by a person
							$avatar = $post->users[0]->avatar;
							$avatar_url = '/user/'.$post->users[0]->username.'/';
							$name = '<a href="'.$avatar_url.'">@'.$post->users[0]->username.'</a>';
						}

						$post->maps[0]->approved;
					?>
					<div class="row">
						<div class="col-lg-1 col-md-1 col-sm-2 col-xs-2">
							<a href="<?=$avatar_url?>"><img src="<?=$avatar?>" class="img-rounded" style="width:100%"></a>
						</div>
						<div class="col-lg-11">
							<?=$post->message?>
							<?php if(isset($post->externals[0]) AND !empty($post->externals[0]->embed_html)) {
								echo $post->externals[0]->embed_html;
							}elseif(isset($post->externals[0]) AND isset($post->externals[0]->images[0]) AND !empty($post->externals[0]->images[0]->url)){
								echo '<img src="'.$post->externals[0]->images[0]->url.'" style="width:100%">';
							} ?>
							<small class="text-muted">Posted <?=$t->time_elapsed_string($post->date_posted)?> by <?=$name?></small>
						</div>
					</div>
					<hr/>
				<?php } ?>

				<?php if(count($t->posts->posts) == 0) { ?>
				<div class="alert alert-warning"><strong>Hey!</strong> <?=$t->map->name?> doesn't have any posts just yet! How about you remedy that by posting something?</div>
				<?php } ?>
				</div>
			</div>
		</div>

	</div>

<pre>
<?php
	//var_dump($t->posts);
?>
</pre>

<script src="/assets/js/map.js"></script>