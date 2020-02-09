jQuery(document).ready(function($) {

    var currentURL = document.location.href;
	var URLElem = document.getElementById('pprhURL');
	var location = document.getElementById('pprhHintLocation');

    if (/admin.php\?page=pprh-plugin-settings&tab=settings/ig.test(currentURL)) {
		verifyPreconnectResets();
		location.addEventListener('change', showCacheWarning);
	} else if (/admin.php\?page=pprh-plugin-settings&tab=pro/ig.test(currentURL)) {
		checkoutEvtListener();
	}

	function showCacheWarning() {
		var plugins = document.getElementById('pprhCachePlugins');
		var warning = document.getElementById('pprhBox');

		if (plugins && plugins.innerHTML.length > 0) {
			warning.innerHTML = 'The plugin ' + plugins.innerHTML + ' caches HTTP headers, <br/> it is advised that you load resource hints in your websites\'s HTML &lt;head&gt; instead, and then refresh your cache!';
			warning.style.display = (location.value === 'false') ? 'block' : 'none';
		}
	}


	function updateElem(elem, obj) {
		return elem.val(JSON.stringify(obj));
	}

    function getHintType() {
		return document.querySelector('input[name="hint_type"]:checked');
	}

	function getPostID() {
		var postID = new URL(currentURL).searchParams.get("post");
		if ( ! postID ) {
			postID = 'global';
		}
		return postID;
	}

	verifyNewHint();
	function verifyNewHint() {
		var insertElem = $("input#pprhInsertedHints");
		var insertBtn = $("input#pprhSubmitHints");
		var insertObj = {};

		insertBtn.on("click", function(e) {
			var hintType = getHintType();
			var asAttr = $('select#pprhAsAttr');

			if (URLElem.value.length === 0 || !hintType) {
				e.preventDefault();
				alert('Please enter a proper URL and hint type.');
			} else if (hintType.value === 'preload' && asAttr.val() === '') {
				e.preventDefault();
				alert('All preload hints require a proper `as` attribute.');	
			} else {
				insertObj.url = [ URLElem.value ];
				insertObj.hint_type = hintType.value;
				insertObj.post_id = getPostID();
				insertObj.crossorigin = (document.getElementById('pprhCrossorigin').checked) ? 'crossorigin' : '';
				insertObj.as_attr = document.getElementById('pprhAsAttr').value;
				insertObj.type_attr = document.getElementById('pprhTypeAttr').value;
				updateElem(insertElem, insertObj);
			}

		});
	}

	$('input#pprhSubmit').on("click", function(e) {
		return emailValidate(e);
	});

	function emailValidate(e) {
		var emailAddr = document.getElementById("pprhEmailAddress");
		var emailMsg = document.getElementById("pprhEmailText");
		var emailformat = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;

		if (emailformat.test(emailAddr.value) && emailMsg.value !== "") {
			return true;
		} else {
			alert('Please enter a valid message and email address.');
			e.preventDefault();
			return false;
		}
	}

	function showConfirmMsg(elem, msg) {
		return elem.addEventListener('click', function(e) {
			return (confirm(msg)) ? true : e.preventDefault();
		});
	}

	function verifyPreconnectResets() {
		var precReset = document.getElementById('pprhPreconnectReset');
		showConfirmMsg(precReset, 'Are you sure you want to reset automatically created preconnect hints?');
	}

	function checkoutEvtListener() {
		var ele = document.getElementById('pprh-checkout');
		ele.addEventListener('click', calcAndOpenCheckoutModal);
	}

	function calcAndOpenCheckoutModal() {
		var top = ((screen.height - 800) / 2) - 40;
		var left = (screen.width - 600) / 2;
		var url = 'https://sphacks.local/checkout';

		window.open(url, '_blank', 'height=800, width=800, top=' + top + ',left=' + left );
	}

});