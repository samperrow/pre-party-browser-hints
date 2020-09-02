(function() {

    var host = document.location.origin;
    var altDomain = getAltHostName.call(host);

    function getAltHostName() {
        var idx = this.indexOf("//");
        return (this.indexOf("www.") > 0) ? this.replace(/www\./, "") : this.slice(0, idx+2) + "www." + this.slice(idx+2, this.length);
    }

    function isValidHintDomain(domain) {
        return (domain !== host && pprh_data.hints.indexOf(domain) === -1 && !/\.gravatar\.com/.test(domain) && domain !== altDomain);
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
                pprh_data.hints.push(sanitizeURL.call(domain));
            }
        });
    }

    function fireAjax() {
        findResourceSources();
        var json = JSON.stringify(pprh_data);
        var xhr = new XMLHttpRequest();
        var url = pprh_data.admin_url;
        console.log(pprh_data);
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.send('action=pprh_post_domain_names&pprh_data=' + json + '&nonce=' + pprh_data.nonce );
    }

    // if this js code gets cached in another file, prevent it from firing every page load.
    setTimeout(fireAjax, 7000);

})();
