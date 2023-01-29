let pprhProAdminJS = function() {

	let $ = jQuery;
	let currentURL = document.location.href;

	if (/post\.php\?post=/i.test(currentURL)) {
		checkIfPostUrlChanges();
		resetAutoSetHints();
		$(document).ready(moveAdminNoticeToMetaBox);
	}

	function resetAutoSetHints() {
		let i = 0;
		let resetPreconnectsElem = document.getElementById('resetPostPreconnect');
		let resetPostPreloadElem = document.getElementById('resetPostPreload');
		let resetPrerenderElem = document.getElementById('resetPostPrerender');

		let arrayOfObjs = [
			{elem: resetPreconnectsElem, action: 'reset_post_preconnect', msg: 'Reset this post\'s preconnect hints?'},
			{elem: resetPostPreloadElem, action: 'reset_post_preload', msg: 'Reset this post\'s preload hints?'},
			{elem: resetPrerenderElem, action: 'reset_post_prerender', msg: 'Reset this post\'s prerender hint?'}
		];

		for (i; i < arrayOfObjs.length; i++) {
			sendRequest(arrayOfObjs[i]);
		}
	}

	function sendRequest(obj) {
		if ( ! pprhAdminJS.IsObjectAndNotNull(obj.elem)) {
			return;
		}

		obj.elem.addEventListener('click', function() {

			if (window.confirm(obj.msg)) {
				pprhAdminJS.UpdateAdminNotice('Retrieving API data and generating hints...', 'info');

				let dataObj = {
					action: obj.action, post_id: getPostId()
				};
				pprhAdminJS.CreateAjaxRequest(dataObj, 'pprh_update_hints', null);
			}
		});
	}

	// this will move the admin notice to the intended meta box, instead of at the top of post pages.
	function moveAdminNoticeToMetaBox() {
		let ele = $('div#pprhNoticeBox');
		let notice = $('div#pprhNotice');
		$(document).remove(notice);
		ele.append(notice);
	}


	function getPostId() {
		let matchArray = currentURL.match(/post\.php\?post=(\d*)/);
		let homeCheckedElem = $('input.pprhHomePostHints');
		let postId = 'global';

		if ( Array.isArray(matchArray) && (Number(matchArray[1]) > 0) ) {
			postId = matchArray[1];
		} else if ( homeCheckedElem.is(':checked') ) {
			postId = '0';
		}

		return postId;
	}

	function checkIfPostUrlChanges() {
		let btn = $('span#edit-slug-buttons button');
		let linkchangeEle = $('input#pprhLinkChanged');

		btn.on('click', function () {
			return linkchangeEle.val('true');
		});
	}

	return {
		GetPostId: getPostId
	}

}();
