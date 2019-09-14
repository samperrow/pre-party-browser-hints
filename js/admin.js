jQuery(document).ready(function($) {

    var currentURL = document.location.href;
	var URLElem = document.getElementById('pprhURL');

    if (/post.php/ig.test(currentURL)) {
		updatePostHints();
		$('table#pprh-post-table tbody th.check-column:has(span)').css({ 'text-align': 'center' });
	} else if (/admin.php\?page=pprh-plugin-settings&tab=settings/ig.test(currentURL)) {
		verifyPreconnectResets();
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
				alert('All preload hints need a proper `as` attribute.');	
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

	function showConfirmMsg(elemId, msg) {
		return elemId.addEventListener('click', function(e) {
			return (confirm(msg)) ? true : e.preventDefault();
		});
	}

	function verifyPreconnectResets() {
		var globalReset = document.getElementById('pprhResetGlobalPreconnects');
		var postReset = document.getElementById('pprhResetPostPreconnects');
		var homeReset = document.getElementById('pprhHomeReset');

		showConfirmMsg(globalReset, 'Are you sure you want to reset the preconnect hints used on all posts and pages?');
		showConfirmMsg(postReset, 'Are you sure you want to reset all post/page specific preconnect hints?');
		showConfirmMsg(homeReset, 'Are you sure you want to reset custom preconnect hints used on the home page?');
	}

	
	// used on posts/pages
	function updatePostHints() {
		var obj = {};
		var autoHintResetElem = $('input#pprhPageResetValue');
		var bulkApplyBtn = $("input#PPRHApply");
		var updateHintsElem = $("input#pprhUpdateHints");
		var checkboxes = $("table#pprh-post-table tbody tr th input:checkbox");
		var actionElem = $("select#pprh-option-select");
	
		bulkApplyBtn.on("click", function(e) {
			obj.hint_ids = [];
	
			$.each(checkboxes, function() {
				if ($(this).is(":checked")) {
					obj.hint_ids.push( $(this).val() );
				}
			});
			obj.action = actionElem.val();

			if (obj.hint_ids.length > 0) {
				return updateElem(updateHintsElem, obj);
			} else {
				alert("There are no resource hints to update");
				e.preventDefault();
			}
		});
			
		$('input#pprhPageReset').on('click', function() {
			obj.reset = true;
			return updateElem(autoHintResetElem, obj);
		});
	   
	}

		
});


