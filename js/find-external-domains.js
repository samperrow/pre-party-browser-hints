(function() {

    let host = document.location.origin;
    let altHostName = getAltHostName.call(host);
    const TESTING = (/sphacks\.local/.test(host));

    function getAltHostName() {
        let idx = this.indexOf("//");
        return (this.indexOf("www.") > 0) ? this.replace(/www\./, "") : this.slice(0, idx+2) + "www." + this.slice(idx+2, this.length);
    }

    function isValidHintDomain(domain, domainArr) {
        let domainNotHost = (domain !== host);
        let disAllowedDomains = (! /\.gravatar\.com|data:application/.test(domain));
        let domainNotAltDomain = (domain !== altHostName);
        let domainNotInArr = (domainArr.indexOf(domain) === -1);
        return ( domainNotHost && domainNotInArr && domainNotAltDomain && disAllowedDomains );
    }

    function findResourceSources() {
        let resources = window.performance.getEntriesByType('resource');
        let newHintArr = [];
        let domains = [];

        if (typeof pprhCreateHint === "undefined" || resources.length === 0) {
            return newHintArr;
        }

        resources.forEach(function(item) {
            let hintObj = {
                url: item.name,
                hint_type: 'preconnect'
            };

            let hint = pprhCreateHint.CreateHint(hintObj);
            let domain = pprhCreateHint.GetDomain(hint.url);

            if (isValidHintDomain(domain, domains)) {
                domains.push(hint.url);
                newHintArr.push(hint);
            }
        });

        return newHintArr;
    }

    function fireAjax() {
        pprh_data.hints = findResourceSources();
        let json = JSON.stringify(pprh_data);
        let xhr = new XMLHttpRequest();
        if (TESTING) {
            console.log(pprh_data);
        }

        xhr.open('POST', pprh_data.admin_url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.send('action=pprh_post_domain_names&pprh_data=' + json + '&nonce=' + pprh_data.nonce);
    }

    // sometimes this script can become cached in other JS files due to caching plugins. This prevents a request from being sent constantly when not desired.
    function scriptSentWithinSixHours() {
        let currentDT = new Date();
        let startTime = Number(pprh_data.start_time) * 1000;
        let inSixHours = new Date(startTime + (6*3600000));     // six hours from time script was initiated.
        return (inSixHours > currentDT);
    }

    // sometimes this file can be cached, and this prevents it from constantly firing ajax requests.
    if (scriptSentWithinSixHours()) {
        let timer = (TESTING) ? 1000 : 7000;
        setTimeout(fireAjax, timer);
    }
})();
