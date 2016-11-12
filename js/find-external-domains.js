function gktppFindExtDomains() {
     function getProtocolAndDomain(str, m, i) {
          return str.split(m, i).join(m).length;
     }

     function findDomain( url ) {
          var lastSlash = getProtocolAndDomain(url, "/", 3);
          return url.slice(0, lastSlash );
     }

     function combineAndVerifySources( elem ) {
          var newArr = [];
          var homeRegex = new RegExp( ajax_object.homeURL, "g");
          var checkCSS = new RegExp( ".css", "g");
          for (var i in elem) {
               if ( elem[i].src && (!elem[i].getAttribute("src").match(homeRegex)))  {
                    newArr.push(findDomain(elem[i].getAttribute("src")));
               }
               else if ( elem[i].href && (elem[i].getAttribute("href").match(checkCSS)) && (!elem[i].getAttribute("href").match(homeRegex)))  {
                    newArr.push(findDomain(elem[i].getAttribute("href")));
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
               pageOrPostIDValue : ajax_object.pagePostID
          };

          jQuery.post(ajax_object.ajax_url, data2 );
     }();
}

jQuery(document).ready(function($) {
     gktppFindExtDomains();
});
