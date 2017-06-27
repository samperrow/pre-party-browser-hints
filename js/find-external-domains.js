function gktppFindExtDomains() {
     "use strict";
     function getProtocolAndDomain(str, m, i) {
          return str.split(m, i).join(m).length;
     }

     function findDomain( url ) {
          var lastSlash = getProtocolAndDomain(url, "/", 3);
          return url.slice(0, lastSlash );
     }

     function combineAndVerifySources( elem ) {
          var newArr = [];
          var homeURL = new RegExp( document.location.origin, "g");
          var checkCSS = new RegExp( ".css", "g");

          for (var i in elem) {
               if ( elem[i].src && (!elem[i].src.match(homeURL)) ) {
                    newArr.push(findDomain(elem[i].src));
               }
               else if ( elem[i].href && (elem[i].href.match(checkCSS)) && (!elem[i].href.match(homeURL)) ) {
                    newArr.push(findDomain(elem[i].href));
               }

          }
          return newArr;
     }

	function uniqueDomains(arr) {
		var a = [];
		for (var i=0, l=arr.length; i<l; i++)
			if (a.indexOf(arr[i]) === -1 && arr[i] !== '')
				a.push(arr[i]);
		return a;
	}

     function findScriptSources() {
          var scripts = combineAndVerifySources(document.getElementsByTagName("script"));
          var styles = combineAndVerifySources(document.getElementsByTagName("link"));
          var images = combineAndVerifySources(document.getElementsByTagName("img"));
          return uniqueDomains(scripts.concat( images, styles ) );
     }

     var sendAjax = function() {
          var domains = findScriptSources();

     	var data2 = {
               action: 'gktpp_post_domain_names',
               data : domains,
          };
          jQuery.post(ajax_object.ajax_url, data2 );
     }();
}
setTimeout( gktppFindExtDomains, 7500);
