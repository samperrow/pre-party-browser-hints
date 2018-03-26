if (typeof jQuery == 'undefined' || (!window.jQuery)) {
    var script = document.createElement('script');
    script.src = document.location.origin + '/wp-includes/js/jquery/jquery.js';
    document.getElementsByTagName('head')[0].appendChild(script);
}

var resourceArr = [];

function sanitizeURL(url) {
    return url.replace(/[\[\]\{\}\<\>\'\"\\(\)\*\+\\^\$\|]/g, '');
}

function findResourceSources() {
    var resources = window.performance.getEntriesByType('resource');
    resourceArr = [];
    var hostDomainName = document.location.origin;

    for (var i = 0; i < resources.length; i++ ) {
        var newStr = resources[i].name.split('/');
        var protocolAndDomain = newStr[0] + '//' + newStr[2];           

        if ( protocolAndDomain !== hostDomainName && resourceArr.indexOf(protocolAndDomain) === -1 ) {
            resourceArr.push( sanitizeURL(protocolAndDomain ) );
        }
    }
    return resourceArr;
}

function determineCrossorigin( domains ) {
    var crossoriginArr = [];
    for (var i = 0; i < domains.length; i++) {
        crossoriginArr.push( domains[i].match(/fonts.googleapis.com|fonts.gstatic.com/) ? 'crossorigin' : null );
    }
    return crossoriginArr;
}

function sendAjax() {
    var dataObj = {
        action: 'post_domain_names',
        data: findResourceSources(),
        crossorigin: determineCrossorigin(resourceArr)
    };

    if ( dataObj.data.length > 0 ) {
        jQuery.post(ajax_object.ajax_url, dataObj);
        console.log(dataObj.data);
    }
}

setTimeout( sendAjax, 1000);