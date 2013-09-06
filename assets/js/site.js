var generateSignature = function(method, url) {
	var timestamp  = Math.round(new Date().getTime() / 1000),
		method     = method.toUpperCase();

	var hashme = method+"\n"+timestamp+"\n"+url+"\n";

	return "A"+root.publicKey+CryptoJS.HmacSHA1(hashme, root.privateKey);
}