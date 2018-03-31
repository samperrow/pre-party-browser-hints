if (typeof jQuery == 'undefined' || (!window.jQuery)) {
    var script = document.createElement('script');
    script.src = document.location.origin + '/wp-includes/js/jquery/jquery.js';
    document.getElementsByTagName('head')[0].appendChild(script);
}

var gktppDataObj = {
    action: 'gktpp_post_domain_names',
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
        
        if ( protocolAndDomain !== hostDomainName && gktppDataObj.urls.indexOf(protocolAndDomain) === -1 ) {
            gktppDataObj.urls.push( sanitizeURL(protocolAndDomain ) );
        }
    }
}

var scripts = document.getElementsByTagName('script');
var lastScript = scripts[scripts.length-1].src;

if ( lastScript.match(/find-external-domains.js/) ) {
    setTimeout( function() {
        findResourceSources();
        
        if ( gktppDataObj.urls.length > 0 ) {
            // jQuery.post(ajax_object.ajax_url, gktppDataObj);
            console.log(gktppDataObj.urls);
        }
    }, 1000);
}
