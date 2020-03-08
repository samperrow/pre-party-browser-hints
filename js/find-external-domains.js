(function() {

    var scripts = document.getElementsByTagName('script');
    var host = document.location.origin;
    var altDomain = getAltHostName.call(host);

    function getAltHostName() {
        var idx = this.indexOf("//");
        return (this.indexOf("www.") > 0) ? this.replace(/www\./, "") : this.slice(0, idx+2) + "www." + this.slice(idx+2, this.length);
    }

    function isValidHintDomain(domain) {
        return (domain !== host && pprh_data.url.indexOf(domain) === -1 && !/\.gravatar\.com/.test(domain) && domain !== altDomain);
    }

    function sanitizeURL() {
        return this.replace(/[\[\]\{\}\<\>\'\"\\(\)\*\+\\^\$\|]/g, '');
    }

    function getDomain(url) {
        if (typeof window.URL === "function") {
            return new URL(url.name).origin;
        } else {
            var newStr = item.name.split('/');
            return newStr[0] + '//' + newStr[2];
        }
    }

    function findResourceSources() {
        var resources = window.performance.getEntriesByType('resource');

        resources.forEach(function(item) {
            var domain = getDomain(item);

            if (isValidHintDomain(domain)) {
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
			console.log( pprh_data );
			xhr.send('action=pprh_post_domain_names&pprh_data=' + json + '&nonce=' + pprh_data.nonce );
		}, 7000);
    }

})();
