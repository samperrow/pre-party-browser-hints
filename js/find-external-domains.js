if (typeof jQuery == 'undefined' || (!window.jQuery)) {
    var script = document.createElement('script');
    script.src = document.location.origin + '/wp-includes/js/jquery/jquery.js';
    document.getElementsByTagName('head')[0].appendChild(script);
}

var dataObj = {
    action: 'post_domain_names',
    urls: []
};

function sanitizeURL(url) {
    return url.replace(/[\[\]\{\}\<\>\'\"\\(\)\*\+\\^\$\|]/g, '');
}

function findResourceSources() {
    var resources = window.performance.getEntriesByType('resource');
    var hostDomainName = document.location.origin;

    for (var i = 0; i < resources.length; i++ ) {
        var newStr = resources[i].name.split('/');
        var protocolAndDomain = newStr[0] + '//' + newStr[2];           

        if ( protocolAndDomain !== hostDomainName && dataObj.urls.indexOf(protocolAndDomain) === -1 ) {
            dataObj.urls.push( sanitizeURL(protocolAndDomain ) );
        }
    }
}


function sendAjax() {
    findResourceSources();
    
    if ( dataObj.urls.length > 0 ) {
        jQuery.post(ajax_object.ajax_url, dataObj);
        console.log(dataObj);
    }
}

setTimeout( sendAjax, 1000);
