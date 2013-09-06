<div class="col-lg-12">
	<h1 class="text-center">So, you want to make a map.</h1>

	<div class="row">
		<div class="col-lg-6 col-md-offset-3">

			<?php if(isset($error) AND $error) { ?>
			<div class="alert alert-danger">Sorry, the subdomain <strong>"<?=strip_tags($subdomain)?>"</strong> was already taken. Please try another!</div>
			<?php } ?>

			<form role="form" action="/create/map/" method="POST" >
				<div class="form-group">
					<label for="name">Map Name</label>
					<input type="text" class="form-control" id="name" name="name" placeholder="Super Map" val="<?php if(isset($name)){ echo strip_tags($name); } ?>">
				</div>

				<div class="form-group">
					<label for="subdomain">Subdomain</label>
					<input type="text" class="form-control" id="subdomain" name="subdomain" placeholder="subdomain" val="<?php if(isset($subdomain)){ echo strip_tags($subdomain); } ?>">
					<p class="help-block">This builds the URL for your site on Crowdmap.com<br/><em>Example: https://[subdomain].crowdmap.com</em></p>
				</div>

				<button type="submit" class="btn btn-default">Make!</button>
			</form>

		</div>
	</div>

</div>