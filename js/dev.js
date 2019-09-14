pprhAutoCollectHints(post_hint_data.targetURL);

function pprhAutoCollectHints(targetURL) {

	var hostName = document.location.origin;
	var scripts = document.getElementsByTagName('script');
	

	if (/dev.js/i.test(scripts[scripts.length - 1].src)) {
		setTimeout( function() {
			return getAjaxData(targetURL);
		}, 10000);
	}

	function getAjaxData(targetURL) {
		var xhr = new XMLHttpRequest();
	
		xhr.open('GET', targetURL, true);
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		// xhr.setRequestHeader('Timing-Allow-Origin', '*');
		xhr.onload = function(e) {
	
			var elems = htmlToElement(e.target.response);
			console.log( e.target);
			// console.log( elems);
			sortElems(elems.childNodes);
		}
		xhr.send();
	}
	
	
	function sortElems(nodes) {

		for (var i = 0; i < nodes.length; i++) {
	
			if (nodes[i].nodeName === 'SCRIPT') {
				var src = nodes[i].src;
	
				if ( /.js/ig.test(src) && src.length > 0 && src.indexOf(hostName) === -1 && post_hint_data.url.indexOf(src) === -1 ) {
					post_hint_data.url.push( nodes[i].src );
				}
			} else if (nodes[i].nodeName === 'LINK') {
				var src = nodes[i].href;
	
				if ( (nodes[i].rel === 'stylesheet') && src.length > 0 && src.indexOf(hostName) === -1 && post_hint_data.url.indexOf(src) === -1 ) {
					post_hint_data.url.push(nodes[i].href);
				}
			} else if (nodes[i].nodeName === 'IMG') {
				var src = nodes[i].href;
	
				if ( (nodes[i].rel === 'stylesheet') && src.length > 0 && src.indexOf(hostName) === -1 && post_hint_data.url.indexOf(src) === -1 ) {
					post_hint_data.url.push(nodes[i].href);
				}
			}
		}
		// console.log( post_hint_data );
		sendHintsBack()
	}
	
	function htmlToElement(html) {
		var template = document.createElement('template');
		template.innerHTML = html.trim();
		return template.content;
	}

	function sendHintsBack() {
		var xhr = new XMLHttpRequest();
		xhr.open('POST', hostName + '/wp-admin/admin-ajax.php', true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		// console.log( post_hint_data.url.length );
		var json = JSON.stringify(post_hint_data);
		console.log( json );
		xhr.send('action=pprh_collect_hints&post_hint_data=' + json );
	}

}

