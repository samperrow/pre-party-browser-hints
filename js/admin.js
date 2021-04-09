(function (global, factory) {
	global.pprhAdminJS = factory();
}(this, function() {

	'use strict';
	let $ = jQuery;
	const currentURL = document.location.href;
	const adminNoticeElem = document.getElementById('pprhNotice');

	if (/pprh-plugin-settings/.test(currentURL)) {
		let emailSubmitBtn = document.getElementById('pprhSubmit');
		if (null !== emailSubmitBtn) {
			emailSubmitBtn.addEventListener("click", emailValidate);
		}
	}

	toggleDivs();
	function toggleDivs() {
		let tabs = $('a.nav-tab');
		let divs = $('div.pprh-content');

		tabs.first().toggleClass('nav-tab-active');
		$("#pprh-insert-hints").toggleClass('active');

		if (!tabs) {
			return;
		}

		$.each(tabs, function () {
			$(this).on('click', function (e) {
				let className = e.currentTarget.classList[1];
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
			let text = e.target.getAttribute('data-text');
			let res = confirm('Are you sure you want to ' + text);

			if (!res) {
				e.preventDefault();
			}
		});
	});

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



	function configForHint(table) {
		let elems = getRowElems(table);
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

	function prepareHint(tableId, operation) {
		let table = $('#' + tableId);
		let rawHint = configForHint(table);

		if (typeof rawHint === "object") {
			let hint = pprhCreateHint.CreateHint(rawHint);
			let isHintValid = verifyHint(hint);

			if (isHintValid) {
				hint.op_code = operation;
				hint.hint_ids = (operation === 1) ? tableId.split('pprh-edit-')[1] : [];
				return createAjaxReq(hint, 'pprh_update_hints', pprh_data.nonce);
			} else {
				alert('Invalid hint.');
			}
		}
	}

	$('input#pprhSubmitHints').on("click", function (e) {
		prepareHint('pprh-enter-data', 0);
	});



	function verifyHint(hint) {
		if (hint.url.length === 0 || typeof hint.hint_type === "undefined") {
			window.alert('Please enter a proper URL and hint type.');
		} else if (hint.hint_type === 'preload' && !hint.as_attr) {
			window.alert("You must specify an 'as' attribute when using preload hints.");
		}

		return true;
	}

	function getRowElems(table) {
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









	addEventListeners();
	function addEventListeners() {
		getHintIdsAndInsertData();
		addDeleteHintListener();
		addEditRowEventListener();
		toggleElemIterator();

		function getHintIdsAndInsertData() {
			let editRows = $('tr.pprh-row.edit');

			$.each(editRows, function() {
				let id = $(this).attr('class').split(' ')[2];
				putHintInfoIntoElems(id);
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
				putHintInfoIntoElems(hintID);

				rows.find('button.button.cancel').first().on('click', function() {
					rows.removeClass('active');
				});

				$('tr.pprh-row.edit.' + hintID).find('button.pprh-update').on('click', function(e) {
					prepareHint('pprh-edit-' + hintID, 1);
				});
			});
		}

		function toggleElemIterator() {
			let hintTypeRows = $('tr.pprhHintTypes');

			$.each(hintTypeRows, function() {
				let parentTbody = $(this).parent('tbody');
				let hintTypeRadio = $(this).find('input.hint_type');

				$.each(hintTypeRadio, function() {

					$(this).on('click', function() {
						toggleBasedOnHintType(parentTbody, $(this));
					});

					if ( $(this).is(':checked')) {
						toggleBasedOnHintType( parentTbody, $(this) );
					}
				});
			});
		}

		function toggleBasedOnHintType(parentTbody, elem) {
			let hintType = elem.val();
			let xoriginElem = parentTbody.find('input.pprh_crossorigin').first();
			let mediaElem = parentTbody.find('input.pprh_media').first();

			if ('preconnect' === hintType) {
				xoriginElem.prop('disabled', false);
				mediaElem.prop('disabled', true);
				mediaElem.val('');
			} else if ('preload' === hintType) {
				xoriginElem.prop('disabled', false);
				mediaElem.prop('disabled', false);
			} else {
				xoriginElem.prop('checked', false);
				xoriginElem.prop('disabled', true);
				mediaElem.prop('disabled', true);
				mediaElem.val('');
			}
		}

		function putHintInfoIntoElems(hintID) {
			let json = $('input.pprh-hint-storage.' + hintID).val();
			let data = JSON.parse(json);
			let table = $('table#pprh-edit-' + hintID);
			let elems = getRowElems(table);

			elems.url.val(data.url);

			let hintTypeElem = elems.hint_type.find('input[value="' + data.hint_type + '"]');
			hintTypeElem.attr('checked', true);

			if (data['crossorigin']) {
				elems.crossorigin.attr('checked', true);
			}

			elems.as_attr.val(data['as_attr'] ? data['as_attr'] : '');
			elems.type_attr.val(data['type_attr'] ? data['type_attr'] : '');
			elems.media.val(data['media'] ? data['media'] : '');
		}
	}


	function clearHintTable() {
		let tbody = document.getElementById('pprh-enter-data').getElementsByTagName('tbody')[0];

		tbody.querySelectorAll('select, input').forEach(function (elem) {
			return elem[(/radio|checkbox/.test(elem.type)) ? 'checked' : 'value'] = '';
		});
	}

	function verifyResponse(response) {
		let status = (response.status) ? response.status : 'error';
		let msg = (response.msg) ? response.msg : 'Error updating hint. Please contact support or try again later.';

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



	// xhr object
	function createAjaxReq(dataObj, callback, nonce) {

		doAjax(dataObj, callback, nonce);
		function doAjax(dataObj, callback, nonce) {
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
				xhrResponse(xhr);
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

		// update the hint table via ajax.
		function updateTable(response) {
			let table = $('table.pprh-post-table').first();
			let postTable = document.getElementsByClassName('pprh-post-table')[0];
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

			postTable.querySelectorAll(':checked').forEach(function (item) {
				return item.checked = false;
			});
		}

		function xhrResponse(xhr) {
			if (xhr.readyState === 4 && xhr.status === 200) {
				if (xhr.response.length > 0) {
					let response = JSON.parse(xhr.response);
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
		}
	}



	// bulk deletes, enables/disables.
	$('input.pprhBulkAction').on('click', bulkUpdates);
	function bulkUpdates(e) {
		e.preventDefault();
		let idArr = [];
		let op = $(e.currentTarget).prev().val();
		let opCode = ( 'delete' === op ) ? 2 : ( 'enable' === op ) ? 3 : ('disable' === op) ? 4 : 5;
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

	return {
		CreateAjaxReq: createAjaxReq,
		UpdateAdminNotice: updateAdminNotice,
	}

}));

