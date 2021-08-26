(function (global, factory) {
	global.pprhAdminJS = factory();
}(this, function() {

	'use strict';
	let $ = jQuery;
	const currentURL = document.location.href;
	const submitHintsBtn = document.getElementById('pprhSubmitHints');
	let resetButtonElems = document.querySelectorAll('input.pprh-reset');
	let bulkActionElems = document.querySelectorAll('input.pprhBulkAction');
	let newHintTable = document.getElementById('pprh-enter-data');

	toggleEmailSubmit();
	toggleDivs();
	// checkoutModals();
	resetButtons();
	addSubmitHintsListener();
	addEventListeners();
	addHintTypeListener(null);
	applyBulkActionListeners();



	function toggleEmailSubmit() {
		if (/pprh-plugin-settings/.test(currentURL)) {
			let emailSubmitBtn = document.getElementById('pprhSubmit');
			if (isObjectAndNotNull(emailSubmitBtn)) {
				emailSubmitBtn.addEventListener("click", emailValidate);
			}
		}

		// used on all admin and modal screens w/ contact button.
		function emailValidate(e) {
			let emailAddr = document.getElementById("pprhEmailAddress");
			let emailMsg = document.getElementById("pprhEmailText");
			let emailformat = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;

			if (!emailformat.test(emailAddr.value) || emailMsg.value === "") {
				e.preventDefault();
				window.alert('Please enter a valid message and email address.');
			}
		}
	}

	function toggleDivs() {
		let navTabs = document.querySelectorAll('a.nav-tab');
		let divs = document.querySelectorAll('div.pprh-content');

		if (isObjectAndNotNull(navTabs) && navTabs.length > 0) {
			navTabs[0].classList.toggle('nav-tab-active');
		}

		if (isObjectAndNotNull(divs) && divs.length > 0) {
			divs[0].classList.toggle('active');
		}

		navTabs.forEach(function(tab) {
			tab.addEventListener('click', tabClick);
		});

		function tabClick(e) {
			let className = e.currentTarget.classList[1];

			navTabs.forEach(function(tab, i) {
				let tabClassmatch = (tab.className.indexOf(className) > -1);
				let divClassMatch = (divs[i].className.indexOf(className) > -1);
				tab.classList[ (tabClassmatch) ? 'add' : 'remove']('nav-tab-active');
				divs[i].classList[ (divClassMatch) ? 'add' : 'remove']('active');
			});
			e.preventDefault();
		}
	}



	function checkoutModals() {
		let checkoutModalElems = document.getElementsByClassName('pprhOpenCheckoutModal');
		if (isObjectAndNotNull(checkoutModalElems)) {
			for (const checkoutModalElem of checkoutModalElems) {
				checkoutModalElem.addEventListener('click', openCheckoutModal);
			}
		}

		function openCheckoutModal() {
			let windowWidth = window.innerWidth;
			// let windowHeight = window.innerHeight;
			// let leftSpace = (windowWidth - 700) / 2;
			// // window.open( 'https://sphacks.io/checkout', '_blank', '', false );
			window.open( 'https://sphacks.io/checkout', '_blank', 'height=850,scrollbars=yes,width=700', false );
		}
	}

	function resetButtons() {
		if ( ! isObjectAndNotNull(resetButtonElems)) {
			return;
		}
		resetButtonElems.forEach( function (btn) {
			btn.addEventListener('click', addConfirmNotice);
		});

		function addConfirmNotice (e) {
			let text = e.target.getAttribute('data-text');
			let res = confirm('Are you sure you want to ' + text);
			if (!res) {
				e.preventDefault();
			}
		}
	}



	function addSubmitHintsListener() {
		if (! isObjectAndNotNull(submitHintsBtn)) {
			return;
		}

		submitHintsBtn.addEventListener("click", function(e) {
			prepareHint('pprh-enter-data', 0);
		});
	}


	function prepareHint(tableId, operation) {
		let table = $('#' + tableId);
		let rawHint = getHintValuesFromTable(table);

		if (isObjectAndNotNull(rawHint)) {
			let isHintValid = verifyHint(rawHint);

			if (isHintValid) {
				rawHint.op_code = operation;
				rawHint.hint_ids = (operation === 1) ? tableId.split('pprh-edit-')[1] : [];
				return createAjaxReq(rawHint, 'pprh_update_hints', pprh_data.nonce);
			}
		}

		function verifyHint(hint) {
			if (hint.url.length === 0 || typeof hint.hint_type === "undefined") {
				window.alert('Please enter a proper URL and hint type.');
				return false;
			} else if (hint.hint_type === 'preload' && !hint.as_attr) {
				window.alert("You must specify an 'as' attribute when using preload hints.");
				return false;
			}

			return true;
		}
	}

	function getHintValuesFromTable(table) {
		let elems = getRowElemsFromTable(table);
		let rawHintType = elems.hint_type;
		let hintTypeVal = rawHintType.find('input:checked').val();

		return {
			url:         elems.url.val(),
			hint_type:   hintTypeVal,
			media:       elems.media.val(),
			as_attr:     elems.as_attr.val(),
			type_attr:   elems.type_attr.val(),
			crossorigin: elems.crossorigin.is(':checked'),
		}
	}

	function getRowElemsFromTable(table) {
		let tbody = table.find('tbody');
		return {
			url:         tbody.find('input.pprh_url'),
			hint_type:   tbody.find('tr.pprhHintTypes'),
			crossorigin: tbody.find('input.pprh_crossorigin'),
			as_attr:     tbody.find('select.pprh_as_attr'),
			type_attr:   tbody.find('select.pprh_type_attr'),
			media:       tbody.find('input.pprh_media')
		};
	}


	function addHintTypeListener(editRow) {
		if (null === editRow) {
			editRow = $('table#pprh-enter-data');
			let xoriginElem = editRow.find('input.pprh_crossorigin').first();
			let mediaElem = editRow.find('input.pprh_media').first();

			xoriginElem.prop('disabled', true);
			mediaElem.prop('disabled', true);
		}

		let hintTypeRadios = editRow.find('tr.pprhHintTypes input.hint_type');

		$.each(hintTypeRadios, function() {
			$(this).on('click', function() {
				hintTypeListener($(this));
			});
		});

		function hintTypeListener(elem) {
			let parentTbody = editRow.find('tbody');
			let hintType = elem.val();
			let xoriginElem = parentTbody.find('input.pprh_crossorigin').first();
			let mediaElem = parentTbody.find('input.pprh_media').first();

			if ('preconnect' === hintType) {
				xoriginElem.prop('disabled', false);
				mediaElem.prop('disabled', true);
			} else if ('preload' === hintType) {
				xoriginElem.prop('disabled', false);
				mediaElem.prop('disabled', false);
			} else {
				xoriginElem.prop('checked', false);
				xoriginElem.prop('disabled', true);
				mediaElem.prop('disabled', true);
			}
		}
	}




	function addEventListeners() {
		getHintIdsAndInsertData();
		addDeleteHintListener();
		addEditRowEventListener();

		function getHintIdsAndInsertData() {
			let editRows = $('tr.pprh-row.edit');

			$.each(editRows, function() {
				addHintTypeListener($(this));
			});
		}

		function addDeleteHintListener() {
			$('span.delete').on('click', function (e) {
				e.preventDefault();

				if (confirm('Are you sure you want to delete this hint?')) {
					let hintID = e.target.id.split('pprh-delete-hint-')[1];
					return createAjaxReq({
						hint_ids: [hintID],
						op_code: 2,
					});
				}
			});
		}

		function addEditRowEventListener() {
			$('span.edit').on('click', function () {
				let hintID = $(this).find('a').attr('id').split('pprh-edit-hint-')[1];
				let allRows = $('tr.pprh-row');
				allRows.removeClass('active');

				let rows = $('tr.pprh-row.' + hintID);
				rows.addClass('active');

				rows.find('button.button.cancel').first().on('click', function() {
					rows.removeClass('active');
				});

				$('tr.pprh-row.edit.' + hintID).find('button.pprh-update').on('click', function(e) {
					prepareHint('pprh-edit-' + hintID, 1);
				});
			});
		}
	}

	function clearHintTable() {
		let tbody = newHintTable.getElementsByTagName('tbody')[0];

		tbody.querySelectorAll('select, input').forEach(function (elem) {
			if ( (/radio|checkbox/.test(elem.type)) ) {
				elem.checked = "";
			} else if ((/text|select/.test(elem.type))) {
				elem.value = "";
			}
		});
	}

	// TODO: remove previous notices which have a different status. i.e- removing an 'error' box after a successful notice
	function updateAdminNotice(msg, status) {
		let adminNoticeElem = document.getElementById('pprhNotice');

		if (typeof adminNoticeElem === "undefined" || adminNoticeElem === null) {
			let pprhNoticeBox = document.getElementById('pprhNoticeBox');
			pprhNoticeBox.innerHTML = '<div id="pprhNotice" class="notice notice-success is-dismissible"><p></p></div>';
			adminNoticeElem = document.getElementById('pprhNotice');
		}

		if ( '' !== msg) {
			adminNoticeElem.classList.add('active');
			adminNoticeElem.getElementsByTagName('p')[0].innerText = msg;
		}

		let statusText = ( status) ? 'success' : 'error';

		adminNoticeElem.classList.remove('notice-error');
		adminNoticeElem.classList.remove('notice-success');
		adminNoticeElem.classList.add('notice-' + statusText);

		setTimeout(function() {
			adminNoticeElem.classList.remove('active');
		}, 10000);
	}

	// xhr object
	function createAjaxReq(dataObj, callback, nonce) {
		let xhr = new XMLHttpRequest();
		let url = pprh_data.admin_url + 'admin-ajax.php';
		xhr.open('POST', url, true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		let json = JSON.stringify(dataObj);
		let paginationPage = getUrlValue.call('paged');

		// for testing
		if ( ! callback ) callback = 'pprh_update_hints';
		if ( ! nonce ) nonce = pprh_data.nonce;
		let target = 'action=' + callback + '&pprh_data=' + json + '&nonce=' + nonce;

		if (paginationPage.length > 0) {
			target += '&paged=' + paginationPage;
		}

		xhr.send(target);
		xhr.onreadystatechange = function() {
			xhrResponse();
		}

		function xhrResponse() {
			if (xhr.readyState === 4 && xhr.status === 200 && xhr.response && xhr.response !== "0") {

				if (xhr.response.indexOf('<div') === 0) {
					logError('error');
					return;
				}

				try {
					let response = JSON.parse(xhr.response);
					clearHintTable();
					let msg = response.result.db_result.msg;
					let status = response.result.db_result.status;
					updateAdminNotice(msg, status);
					updateTable(response);
					addEventListeners();
				} catch(e) {
					logError(e);
				}
			}
		}

		// update the hint table via ajax.
		function updateTable(response) {
			let table = $('table.pprh-post-table').first();
			let tbody = table.find('tbody');

			tbody.html('');

			if (response.rows.length) {
				tbody.html(response.rows);
			}

			if (response.pagination.bottom.length) {
				$('.tablenav.top .tablenav-pages').html($(response.pagination.top).html());
			}

			if (response.pagination.top.length) {
				$('.tablenav.bottom .tablenav-pages').html($(response.pagination.bottom).html());
			}

			if (response.total_pages === 1) {
				$('div.tablenav, div.alignleft.actions.bulkactions').removeClass('no-pages');
			}
		}

		function getUrlValue() {
			let val = '';

			if (currentURL.indexOf(this) > -1) {
				try {
					val = new URL(currentURL).searchParams.get(this);
				} catch (e) {
					val = currentURL.split(this + '=')[1].match(/^\d/)[0];
				}
			}

			return val;
		}

		function logError(err) {
			let error = (err.message) ? err.message : " Please clear your browser cache, refresh your page, or contact support to resolve the issue.";
			let msg = "Error updating resource hint. " + error;
			updateAdminNotice(msg, "error");
			console.error(err);
		}
	}

	function applyBulkActionListeners() {
		if ( ! isObjectAndNotNull(bulkActionElems)) {
			return;
		}

		bulkActionElems.forEach(function(elem) {
			elem.addEventListener('click', bulkUpdates);
		});

		// bulk deletes, enables/disables.
		function bulkUpdates(e) {
			e.preventDefault();
			let idArr = [];
			let op = $(e.currentTarget).prev().val();
			let opCode = (/2|3|4/.test(op)) ? Number(op) : 5;
			let checkboxes = $('table.pprh-post-table tbody th.check-column input:checkbox');

			$.each(checkboxes, function () {
				if ($(this).is(':checked')) {
					return idArr.push($(this).val());
				}
			});

			if (idArr.length > 0) {
				return createAjaxReq({
					op_code: opCode,
					hint_ids: idArr,
				});
			} else {
				window.alert('Please select a row(s) for bulk updating.');
			}
		}
	}


	function isObjectAndNotNull(obj) {
		return (typeof obj === "object" && obj !== null);
	}


	return {
		CreateAjaxReq: createAjaxReq,
		UpdateAdminNotice: updateAdminNotice,
		IsObjectAndNotNull: isObjectAndNotNull
	}

}));

// for testing
// if (typeof module === "object") {
// 	module.exports = this.pprhAdminJS;
// }
