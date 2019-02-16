var gktppFindExternalDomains = function() {
    var scripts = document.getElementsByTagName('script');
    var host = document.location.origin;
    var urls = [];
    
    function sanitizeURL(url) {
        return url.replace(/[\[\]\{\}\<\>\'\"\\(\)\*\+\\^\$\|]/g, '');
    }
    
    function findResourceSources() {
        var resources = window.performance.getEntriesByType('resource');
    
        for (var i = 0; i < resources.length; i++ ) {
            var newStr = resources[i].name.split('/');
            var protocolAndDomain = newStr[0] + '//' + newStr[2];
            
            if ( protocolAndDomain !== host && urls.indexOf(protocolAndDomain) === -1 ) {
                urls.push( sanitizeURL(protocolAndDomain) );
            }
        }
    }

    // if this js code gets cached in another file, prevent it from firing every page load.
    if (scripts[scripts.length-1].src.match(/find-external-domains.js/i) ) {
        setTimeout( function() {
            findResourceSources();
            console.log(urls);
            var xhr = new XMLHttpRequest();
                xhr.open('POST', host + '/wp-admin/admin-ajax.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                xhr.send('action=gktpp_post_domain_names&urls=' + JSON.stringify(urls));
        }, 6000);
    }

}();