(function (global, factory) {
	global.pprhAdminJS = factory();
}(this, function() {

	'use strict';
	var $ = jQuery;
	var currentURL = document.location.href;
	var adminNoticeElem = document.getElementById('pprhNotice');
	var checkoutModal = $('input#pprhOpenCheckoutModal');

	if (/pprh-plugin-settings/.test(currentURL)) {
		var emailSubmitBtn = document.getElementById('pprhSubmit');
		if (null !== emailSubmitBtn) {
			emailSubmitBtn.addEventListener("click", emailValidate);
		}
	}
	// else if ( /post\.php/.test(currentURL))  {
	// 	if (typeof checkoutModal === "object") {
	// 		checkoutModal.on('click', openCheckoutModal);
	// 	}
	// }


	addEventListeners();
	toggleDivs();

	function toggleDivs() {
		var tabs = $('a.nav-tab');
		var divs = $('div.pprh-content');

		tabs.first().toggleClass('nav-tab-active');
		$("#pprh-insert-hints").toggleClass('active');

		if (!tabs) {
			return;
		}

		$.each(tabs, function () {
			$(this).on('click', function (e) {
				var className = e.currentTarget.classList[1];
				divs.removeClass('active');
				$('div#pprh-' + className).addClass('active');
				e.preventDefault();

				tabs.removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
			});
		});
	}

	$('input.pprh-reset').each(function () {
		$(this).on('click', function (e) {
			var text = e.target.getAttribute('data-text');
			var res = confirm('Are you sure you want to ' + text);

			if (!res) {
				e.preventDefault();
			}
		});
	});

	// used on all admin and modal screens w/ contact button.
	function emailValidate(e) {
		var emailAddr = document.getElementById("pprhEmailAddress");
		var emailMsg = document.getElementById("pprhEmailText");
		var emailformat = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;

		if (!emailformat.test(emailAddr.value) || emailMsg.value === "") {
			e.preventDefault();
			window.alert('Please enter a valid message and email address.');
		}
	}

	// update the hint table via ajax.
	function updateTable(response) {
		var table = $('table.pprh-post-table').first();
		var table2 = document.getElementsByClassName('pprh-post-table')[0];
		var tbody = table.find('tbody');

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

		table2.querySelectorAll(':checked').forEach(function (item) {
			return item.checked = false;
		});
	}

	function toggleDisallowedElems() {
		var hintTypeElems = $('input[name="hint_type"]');

		$.each(hintTypeElems, function(item) {
			$(this).on('click', function() {
				var hintType = $(this).val();
				var elem = $( $(this).parentsUntil('table')[4] );
				var xoriginElem = elem.find('input.pprh_crossorigin').first();
				var mediaElem = elem.find('input.pprh_media').first();

				if ('preconnect' === hintType) {
					xoriginElem.prop('disabled', false);
					mediaElem.prop('disabled', true);
				} else if ('preload' === hintType) {
					xoriginElem.prop('disabled', true);
					mediaElem.prop('disabled', false);
				} else {
					xoriginElem.prop('disabled', true);
					mediaElem.prop('disabled', true);
				}

			});
		});
	}

	function prepareHint(tableId, operation) {
		var table = jQuery('#' + tableId);
		var rawData = configForHint(table);

		if (typeof rawData === "object") {
			var hint = pprhCreateHint.CreateHint(rawData);
			hint.op_code = operation;
			hint.hint_ids = (operation === 1) ? tableId.split('pprh-edit-')[1] : [];
			return createAjaxReq(hint, 'pprh_update_hints', pprh_data.nonce);
		}
	}

	$('input#pprhSubmitHints').on("click", function (e) {
		prepareHint('pprh-enter-data', 0);
	});

	function configForHint(table) {
		var elems = getRowElems(table);
		var rawHintType = elems.hint_type;
		var hintTypeVal = rawHintType.find('input:checked').val();

		var rawData = {
			url:         elems.url.val(),
			hint_type:   hintTypeVal,
			media:       elems.media.val(),
			as_attr:     elems.as_attr.val(),
			type_attr:   elems.type_attr.val(),
			crossorigin: elems.crossorigin.is(':checked'),
		}

		if (rawData.url.length === 0 || typeof hintTypeVal === "undefined") {
			window.alert('Please enter a proper URL and hint type.');
		} else if (hintTypeVal === 'preload' && !rawData.as_attr) {
			window.alert("You must specify an 'as' attribute when using preload hints.");
		} else {
			return rawData;
		}
	}

	function getRowElems(table) {
		var tbody = table.find('tbody');
		return {
			url:         tbody.find('input.pprh_url'),
			hint_type:   tbody.find('tr.pprhHintTypes'),
			crossorigin: tbody.find('input.pprh_crossorigin'),
			as_attr:     tbody.find('select.pprh_as_attr'),
			type_attr:   tbody.find('select.pprh_type_attr'),
			media:       tbody.find('input.pprh_media')
		};
	}


	function getUrlValue() {
		var val = '';

		if (currentURL.indexOf(this) > -1) {
			try {
				val = new URL(currentURL).searchParams.get(this);
			} catch (e) {
				val = currentURL.split(this + '=')[1].match(/^\d/)[0];
			}
		}

		return val;
	}


	function addDeleteHintListener() {
		$('span.delete').on('click', function (e) {
			e.preventDefault();

			if (confirm('Are you sure you want to delete this hint?')) {
				var hintID = e.target.id.split('pprh-delete-hint-')[1];
				return createAjaxReq({
					hint_ids: [hintID],
					op_code: 2,
				});
			}
		});
	}

	function createAjaxReq(dataObj, callback, nonce) {
		var xhr = new XMLHttpRequest();
		var url = pprh_data.admin_url + 'admin-ajax.php';
		xhr.open('POST', url, true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		var json = JSON.stringify(dataObj);
		var paginationPage = getUrlValue.call('paged');

		// for testing
		if ( ! callback ) callback = 'pprh_update_hints';
		if ( ! nonce ) nonce = pprh_data.nonce;

		var target = 'action=' + callback + '&pprh_data=' + json + '&nonce=' + nonce;

		if (paginationPage.length > 0) {
			target += '&paged=' + paginationPage;
		}

		xhr.send(target);

		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4 && xhr.status === 200) {
				if (xhr.response.length > 0) {
					var response = JSON.parse(xhr.response);
					clearHintTable();

					if ( typeof response.result === "object") {
						if (response.result.db_result) {
							updateAdminNotice(response.result.db_result);
							updateTable(response);
							addEventListeners();
							return;
						}
					} else {
						console.error(response);
					}
				}
			}
		};
	}

	function addEventListeners() {
		addDeleteHintListener();
		addEditRowEventListener();
		toggleDisallowedElems();
	}

	function clearHintTable() {
		var tbody = document.getElementById('pprh-enter-data').getElementsByTagName('tbody')[0];

		tbody.querySelectorAll('select, input').forEach(function (elem) {
			return elem[(/radio|checkbox/.test(elem.type)) ? 'checked' : 'value'] = '';
		});
	}

	function verifyResponse(response) {
		var status = (response.status) ? response.status : 'error';
		var msg = (response.msg) ? response.msg : 'Error updating hint. Please contact support or try again later.';

		if ( response.last_error && response.last_error !== '' && ! response.success ) {
			msg = response.last_error;
		}

		response.msg = msg;
		response.status = status;
		return response;
	}

	// TODO: remove previous notices which have a different status. i.e- removing an 'error' box after a successful notice
	function updateAdminNotice(response) {
		response = verifyResponse(response);
		adminNoticeElem.classList.remove('notice-');
		adminNoticeElem.classList.add('notice-' + response.status);
		adminNoticeElem.classList.add('active');
		adminNoticeElem.getElementsByTagName('p')[0].innerText = response.msg;

		setTimeout(function() {
			adminNoticeElem.classList.remove('active');
			adminNoticeElem.classList.remove('notice-' + response.status);
		}, 10000);
	}

	function addEditRowEventListener() {
		$('span.edit').on('click', function () {
			var hintID = $(this).find('a').attr('id').split('pprh-edit-hint-')[1];
			var allRows = $('tr.pprh-row');
			allRows.removeClass('active');

			var rows = $('tr.pprh-row.' + hintID);
			rows.addClass('active');
			putHintInfoIntoElems(hintID);

			rows.find('button.button.cancel').first().on('click', function () {
				rows.removeClass('active');
			});

			$('tr.pprh-row.edit.' + hintID).find('button.pprh-update').on('click', function (e) {
				prepareHint('pprh-edit-' + hintID, 1);
			});
		});
	}

	function putHintInfoIntoElems(hintID) {
		var json = $('input.pprh-hint-storage.' + hintID).val();
		var data = JSON.parse(json);
		var table = $('table#pprh-edit-' + hintID);
		var elems = getRowElems(table);

		elems.url.val(data.url);
		elems.hint_type.find('input[value="' + data.hint_type + '"]').attr('checked', true);

		if (data['crossorigin']) {
			elems.crossorigin.attr('checked', true);
		}

		elems.as_attr.val(data['as_attr'] ? data['as_attr'] : '');
		elems.type_attr.val(data['type_attr'] ? data['type_attr'] : '');
		elems.media.val(data['media'] ? data['media'] : '');
	}

	// bulk deletes, enables/disables.
	$('input.pprhBulkAction').on('click', bulkUpdates);

	function bulkUpdates(e) {
		e.preventDefault();
		var idArr = [];
		var op = $(e.currentTarget).prev().val();
		var opCode = ( 'delete' === op ) ? 2 : ( 'enable' === op ) ? 3 : ('disable' === op) ? 4 : 5;
		var checkboxes = $('table.pprh-post-table tbody th.check-column input:checkbox');

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

	if (typeof checkoutModal === "object") {
		checkoutModal.on('click', openCheckoutModal);
	}

	function licenseKeyStuff() {
		var licKeyElem = $('input#pprhLicenseKey');
		var activateLicBtn = $('input#pprhActivateLicense');
		// var purchaseLic = $('input#pprhOpenCheckoutModal');

		// purchaseLic.on('click', openCheckoutModal);

		licKeyElem.on('keyup', function() {
			if ( $(this).val().length === 23) {
				activateLicBtn.addClass('button-primary');
			}
		});
	}
	licenseKeyStuff();

	function openCheckoutModal() {
		// var windowWidth = window.innerWidth;
		// var windowHeight = window.innerHeight;
		// var leftSpace = (windowWidth - 700) / 2;
		// window.open( 'https://sphacks.io/checkout', '_blank', '', false );
		window.open( 'https://sphacks.io/checkout', '_blank', 'height=850,scrollbars=yes,width=700', false );
	}

	return {
		CreateAjaxReq: createAjaxReq,
		UpdateAdminNotice: updateAdminNotice,
	}

}));

