<div class="col-lg-12 text-center">
	<h1>We just need your keys.</h1>

	<div class="alert alert-info col-lg-6 col-lg-offset-3">
		<strong>Keys? What keys?!</strong><br/>Crowdmap Lite runs on top of the Crowdmap API, which requires keys to identify your application. You can get your own set of keys on the <a href="https://api.crowdmap.com/developers/v1/">Crowdmap API</a> page.
	</div>

	<div class="col-lg-4 col-lg-offset-4 text-center">
		<form role="form" method="POST" action="/">
			<div class="form-group">
				<label for="site_title">Site Title</label>
				<input type="text" class="form-control" id="site_title" name="site_title" placeholder="Crowdmap Lite">
			</div>

			<div class="form-group">
				<label for="publicKey">Public Key</label>
				<input type="text" class="form-control" id="publicKey" name="publicKey">
			</div>

			<div class="form-group">
				<label for="privateKey">Private Key</label>
				<input type="text" class="form-control" id="privateKey" name="privateKey">
			</div>

			<p class="text-danger"><strong>This is currently a one way street!</strong><br/>For now there's no way to get back here. You will have to make modifications in your MySQL database to change these settings!</p>

			<div class="form-group">
				<label></label>
				<button type="submit" class="btn btn-success" style="width:100%">Submit</button>
			</div>
		</form>
	<div class="col-lg-12 text-center">

</div>