(function() {
    var scripts = document.getElementsByTagName('script');
    var host = document.location.origin;

    function sanitizeURL() {
        return this.replace(/[\[\]\{\}\<\>\'\"\\(\)\*\+\\^\$\|]/g, '');
    }

    function findResourceSources() {
        var resources = window.performance.getEntriesByType('resource');

        for (var i = 0; i < resources.length; i++) {
            var newStr = resources[i].name.split('/');
            var protocolAndDomain = newStr[0] + '//' + newStr[2];

            if (protocolAndDomain !== host && hint_data.url.indexOf(protocolAndDomain) === -1) {
                hint_data.url.push(sanitizeURL.call(protocolAndDomain));
            }
        }
    }

    // if this js code gets cached in another file, prevent it from firing every page load.
    if (/find-external-domains.js/i.test(scripts[scripts.length - 1].src)) {
        setTimeout(function() {
            findResourceSources();
            var xhr = new XMLHttpRequest();
            xhr.open('POST', host + '/wp-admin/admin-ajax.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
			var json = JSON.stringify(hint_data);
			xhr.send('action=pprh_post_domain_names&hint_data=' + json + '&nonce=' + hint_data.nonce );
		}, 7000);
    }

})();
