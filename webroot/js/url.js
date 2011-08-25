var $url = function(url) {
	if (url.match(/^http(s)?:/)) {
		return url;
	}

	if (url.substr(0,1) == '/') {
		url = url.substr(1);
	}

	var elements = document.head.getElementsByTagName("base");

	var prefix = "";

	if (elements[0] != undefined) {
		prefix = elements[0].getAttribute("href");
	}

	if (prefix.length < 6) {
		prefix = window.location.protocol+"//"+window.location.host+'/';
	}

	return prefix+url;
}