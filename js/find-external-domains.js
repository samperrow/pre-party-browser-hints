(function() {
    var scripts = document.getElementsByTagName('script');
    var host = document.location.origin;

    function sanitizeURL() {
        return this.replace(/[\[\]\{\}\<\>\'\"\\(\)\*\+\\^\$\|]/g, '');
    }

    function findResourceSources() {
        var resources = window.performance.getEntriesByType('resource');

        resources.forEach(function(item) {
            var newStr = item.name.split('/');
            var domain = newStr[0] + '//' + newStr[2];

            if (domain !== host && pprh_data.url.indexOf(domain) === -1 && ! /\.gravatar\.com/.test(domain) ) {
                pprh_data.url.push(sanitizeURL.call(domain));
            }
        });
    }

    // if this js code gets cached in another file, prevent it from firing every page load.
    if (/find-external-domains.js/i.test(scripts[scripts.length - 1].src)) {
        setTimeout(function() {
            findResourceSources();
            var json = JSON.stringify(pprh_data);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', host + '/wp-admin/admin-ajax.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
			xhr.send('action=pprh_post_domain_names&hint_data=' + json + '&nonce=' + pprh_data.nonce );
		}, 7000);
    }

})();