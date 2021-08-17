(function (global, factory) {
    global.pprhPreconnects = factory();
}(this, function() {

    let host = document.location.origin;
    let altHostName = getAltHostName(host);
    const TESTING = (/sphacks\.local/.test(host));

    if (typeof pprh_data === "undefined") {
        var pprh_data = setPprhData();
    }

    function setPprhData() {
         return {
            admin_url: host + '/wp-admin/admin-ajax.php',
            start_time: new Date().getTime() / 1000,
            hints: [],
            nonce: ''
        }
    }

    function getAltHostName(hostname) {
        let idx = hostname.indexOf("//");
        let strippedWWW = hostname.replace(/www\./, "");
        let withWWW = hostname.slice(0, idx + 2) + "www." + hostname.slice(idx + 2, hostname.length);
        return (hostname.indexOf("www.") > 0) ? strippedWWW : withWWW;
    }

    function isValidHintDomain(domain, domainArr) {
        let domainNotHost = (domain !== host);
        let disAllowedDomains = (!/\.gravatar\.com|data:application/.test(domain));
        let domainNotAltDomain = (domain !== altHostName);
        let domainNotInArr = (domainArr.indexOf(domain) === -1);
        return (domainNotHost && domainNotInArr && domainNotAltDomain && disAllowedDomains);
    }

    function findResourceSources() {
        let resources = window.performance.getEntriesByType('resource');
        let newHintArr = [];
        let domains = [];

        if (typeof pprhCreateHint === "undefined" || resources.length === 0) {
            return newHintArr;
        }

        resources.forEach(function (item) {
            let hint = pprhCreateHint.CreateHint({url: item.name, hint_type: 'preconnect'});
            let domain = pprhCreateHint.GetDomain(hint.url);
            if (isValidHintDomain(domain, domains)) {
                domains.push(hint.url);
                newHintArr.push(hint);
            }
        });

        return newHintArr;
    }

    function fireAjax(resources = null) {
        if (resources === null) {
            resources = findResourceSources();
        }

        pprh_data.hints = resources;
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
    function scriptSentWithinSixHours(timeInitialized) {
        let currentDT = new Date();
        let startTime = Number(timeInitialized) * 1000;
        let inSixHours = new Date(startTime + 21600000);     // six hours from time script was initiated.
        return (inSixHours > currentDT);
    }

    // sometimes this file can be cached, and this prevents it from constantly firing ajax requests.
    if (scriptSentWithinSixHours(pprh_data.start_time)) {
        let timer = (TESTING) ? 1000 : 7000;
        setTimeout(fireAjax, timer);
    }

    return {
        IsValidHintDomain: isValidHintDomain,
        GetAltHostName: getAltHostName,
        ScriptSentWithinSixHours: scriptSentWithinSixHours,
        FireAjax: fireAjax,
        FindResourceSources: findResourceSources
    }

}));

// for testing
if (typeof module === "object") {
    module.exports = this.pprhPreconnects;
}
