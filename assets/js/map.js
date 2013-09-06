$(document).ready(function() {
	var map = L.map('map', {
					scrollWheelZoom: false
					}).fitWorld().setZoom(1);

	L.tileLayer('http://{s}.tile.cloudmade.com/{key}/22677/256/{z}/{x}/{y}.png', {
		attribution: 'Map data &copy; 2011 OpenStreetMap contributors, Imagery &copy; 2012 CloudMade',
		key: 'BC9A493B41014CAABB98F0471D759707'
	}).addTo(map);

	var sig = generateSignature('GET','/maps/'+root.subdomain+'/posts/');
	$.get(root.endpoint+"/maps/"+root.subdomain+"/posts/", {apikey:sig,format:'geojson'}, function(geojson) {
		L.geoJson(geojson, {
			onEachFeature: function(feature, layer) {
				var popupContent = feature.properties.popupContent;

				if(popupContent === '') {
					popupContent = '[No Message]';
				}

				layer.bindPopup(popupContent);
			}
		}).addTo(map);
	});

});