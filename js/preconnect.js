(function (global, factory) {
    global.pprhPreconnects = factory();
}(this, function() {

    let host = document.location.origin;
    let altHostName = getAltHostName(host);

    if (typeof pprh_data === "undefined") {
        pprh_data = {
            admin_url: host + '/wp-admin/admin-ajax.php',
            nonce: '',
            hints: [],
            start_time: new Date().getTime() / 1000,
            timeout: 7000
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

    function sanitizeURL(url) {
        return url.replace(/[\[\]\{\}\<\>\'\"\\(\)\*\+\\^\$\|]/g, '');
    }

    function getDomain(url) {
        let domain = '';

        if (typeof window.URL === "function" && (0 !== url.indexOf('//') )) {
            domain = new URL(url).origin;
        } else {
            let newStr = url.split('/');
            domain = newStr[0] + '//' + newStr[2];
        }

        return domain;
    }

    function findResourceSources() {
        let resources = window.performance.getEntriesByType('resource');
        let newHintArr = [];
        let domains = [];

        if (resources.length === 0) {
            return newHintArr;
        }

        resources.forEach(function (item) {
            let cleanUrl = sanitizeURL(item.name);
            let domain = getDomain(cleanUrl);

            if (isValidHintDomain(domain, domains)) {
                let hint = {url: domain, hint_type: 'preconnect'};
                domains.push(domain);
                newHintArr.push(hint);
            }
        });

        return newHintArr;
    }

    function fireAjax() {
        pprh_data.hints = findResourceSources();
        let json = JSON.stringify(pprh_data);
        let xhr = new XMLHttpRequest();
        let destination = 'action=pprh_preconnect_callback&pprh_data=' + json + '&nonce=' + pprh_data.nonce;
        console.log(pprh_data);
        xhr.open('POST', pprh_data.admin_url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.send(destination);
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
        let timeout = Number(pprh_data.timeout);
        setTimeout(fireAjax, timeout);
    }

    return {
        IsValidHintDomain: isValidHintDomain,
        GetAltHostName: getAltHostName,
        ScriptSentWithinSixHours: scriptSentWithinSixHours
    }

}));

// for testing
if (typeof module === "object") {
    module.exports = this.pprhPreconnects;
}
