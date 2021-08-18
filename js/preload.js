(function (global, factory) {
    global.pprhPreloads = factory();
}(this, function() {

    let host = document.location.origin;
    // const TESTING = (/sphacks\.local/.test(host));

    function getCriticalResources() {
        let resources         = window.performance.getEntriesByType('resource');
        let headElems         = getHeadResources();
        let criticalResources = [];
        let i = 0;

        for (i; i < headElems.length; i++) {
            let j = 0;

            for (j; j < resources.length; j++) {

                if (typeof headElems[i].localName === "undefined" || typeof resources[j].name === "undefined") {
                    break;
                }

                let matchingCSS = ( ("link" === headElems[i].localName) && (resources[j].name === headElems[i].href) && ("text/css" === headElems[i].type || /\.css/.test(headElems[i].href)) );
                let matchingJS = ( (/script|img|document/.test(headElems[i].localName)) && (resources[j].name === headElems[i].src) );

                if ( matchingCSS && criticalResources.indexOf(headElems[i].href) === -1) {
                    criticalResources.push(headElems[i].href);
                } else if (matchingJS && criticalResources.indexOf(headElems[i].src) === -1) {
                    criticalResources.push(headElems[i].src);
                }
            }
        }

        return criticalResources;
    }

    function getHints() {
        let hints = getCriticalResources();
        let newHintArr = [];
        let i = 0;

        for (i; i < hints.length; i++) {
            let hint = pprhCreateHint.CreateHint({url: hints[0], hint_type: 'preload'});
            newHintArr.push(hint);
        }

        return newHintArr;
    }


    function getHeadResources() {
        let headElems = document.getElementsByTagName('head')[0].children;
        let arr = [];
        let i = 0;

        for (i; i < headElems.length; i++) {
            if ( /link|script|img|iframe|document|audio|video|track|picture/.test(headElems[i].localName)) {
                arr.push(headElems[i]);
            }
        }

        return arr;
    }

    function fireAjax() {
        pprh_data.hints = getHints();
        let json = JSON.stringify(pprh_data);
        let xhr = new XMLHttpRequest();

        // if (TESTING) {
        //     console.log(pprh_data);
        // }

        let destination = 'action=pprh_preload_callback&pprh_data=' + json + '&nonce=' + pprh_data.nonce;
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
        GetCriticalResources: getCriticalResources
    }

}));

// for testing
if (typeof module === "object") {
    module.exports = this.pprhPreloads;
}
