(function() {

    let host = document.location.origin;
    let altHostName = getAltHostName.call(host);
    const TESTING = (/sphacks\.local/.test(host));

    function getAltHostName() {
        let idx = this.indexOf("//");
        return (this.indexOf("www.") > 0) ? this.replace(/www\./, "") : this.slice(0, idx+2) + "www." + this.slice(idx+2, this.length);
    }

    function isValidHintDomain(domain, domainArr) {
        return (domain !== host && domainArr.indexOf(domain) === -1 && !/\.gravatar\.com/.test(domain) && domain !== altHostName);
    }

    function findResourceSources() {
        let resources = window.performance.getEntriesByType('resource');
        let newHintArr = [];
        let domains = [];

        resources.forEach(function(item) {
            let hintObj = {
                url: item.name,
                hint_type: pprh_data.hint_type,
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
        xhr.send('action=pprh_post_domain_names&pprh_data=' + json + '&nonce=' + pprh_data.nonce );
    }

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
