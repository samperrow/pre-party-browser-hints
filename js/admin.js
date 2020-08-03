jQuery(document).ready(function($) {
	'use strict';

	var currentURL = document.location.href;
	var adminNoticeElem = document.getElementById('pprhNotice');
	var globalTable = $('table#pprh-enter-data');
	var emailSubmitBtn = document.getElementById('pprhSubmit');

	emailSubmitBtn.addEventListener("click", emailValidate);

	toggleDivs();
	function toggleDivs() {
		var tabs = $('a.nav-tab');
		var divs = $('div.pprh-content');

		$('a.insert-hints').toggleClass('nav-tab-active');
		$('#pprh-insert-hints').toggleClass('active');

		$.each(tabs, function() {
			$(this).on('click', function(e) {
				var className = e.currentTarget.classList[1];
				tabs.removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
				divs.removeClass('active');
				$('div#pprh-' + className ).toggleClass('active');
				e.preventDefault();
			});
		});

	}

	// used on all admin and modal screens w/ contact button.
	function emailValidate(e) {
		var emailAddr = document.getElementById("pprhEmailAddress");
		var emailMsg = document.getElementById("pprhEmailText");
		var emailformat = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;

		if (! emailformat.test(emailAddr.value) || emailMsg.value === "") {
			e.preventDefault();
			window.alert('Please enter a valid message and email address.');
		}
	}

	// update the hint table via ajax.
	function updateTable(response) {
		var table = $('table.pprh-post-table').first();
		var tbody = table.find('tbody');

		tbody.html('');

		if ( response.rows.length ) {
			tbody.html( response.rows );
		}

		if ( response.pagination.bottom.length ) {
			$('.tablenav.top .tablenav-pages').html( $(response.pagination.top).html() );
		}

		if ( response.pagination.top.length ) {
			$('.tablenav.bottom .tablenav-pages').html( $(response.pagination.bottom).html() );
		}

		if (response.total_pages === 1) {
			$('div.tablenav, div.alignleft.actions.bulkactions').removeClass('no-pages');
		}

		table.find('input:checkbox').attr('checked', false);
	}

	$('input#pprhSubmitHints').on("click", function(e) {
		createHint(e, 'pprh-enter-data', 'create');
	});


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

	function createHint(e, tableID, op) {
		var elems = getRowElems(tableID);
		var hint_url = encodeURIComponent( elems.url.val() );
		var hintType = getHintType.call(elems.hint_type);
		var hintObj = createHintObj();

		if (hint_url.length === 0 || ! hintType) {
			window.alert('Please enter a proper URL and hint type.');
		} else if (hintObj.hint_type === 'preload' && ! hintObj.as_attr) {
			window.alert("You must specify an 'as' attribute when using preload hints.");
		} else {
			createAjaxReq(hintObj);
		}

		function getHintType() {
			return this.find('input:checked').val();
		}

		function createHintObj() {
			return {
				url: hint_url,
				hint_type: hintType,
				crossorigin: elems.crossorigin.is(':checked') ? 'crossorigin' : '',
				as_attr: elems.as_attr.val(),
				type_attr: elems.type_attr.val(),
				action: op,
				hint_id: (op === 'update') ? tableID.split('pprh-edit-')[1] : null,
			};
		}

	}

	function getRowElems(tableID) {
		var table = $('table#' + tableID).find('tbody');
		return {
			url: table.find('input.pprh_url'),
			hint_type: table.find('tr.pprhHintTypes'),
			crossorigin: table.find('input.pprh_crossorigin'),
			as_attr: table.find('select.pprh_as_attr'),
			type_attr: table.find('select.pprh_type_attr'),
		};
	}

	function addDeleteHintListener() {
		$('span.delete').on('click', function(e) {
			e.preventDefault();

			if (confirm('Are you sure you want to delete this hint?')) {
				var hintID = e.target.id.split('pprh-delete-hint-')[1];
				return createAjaxReq({
					hint_ids: [hintID],
					action: 'delete',
				});
			}
		});
	}

	function createAjaxReq(dataObj) {
		var xhr = new XMLHttpRequest();
		var url = pprh_nonce.admin_url + 'admin-ajax.php';
		xhr.open('POST', url, true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		var json = JSON.stringify(dataObj);
		var paginationPage = getUrlValue.call('paged');
		var target = 'action=pprh_update_hints&pprh_data=' + json + '&val=' + pprh_nonce.val;

		if (paginationPage.length > 0) {
			target += '&paged=' + paginationPage;
		}

		xhr.send(target);

		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && xhr.status === 200) {
				if (xhr.response.length > 0) {
					var response = JSON.parse(xhr.response);
					clearHintTable();
					updateAdminNotice(response.result);
					updateTable(response);
					addEventListeners();
				} else {
					return updateAdminNotice(xhr);
				}

			} else if (xhr.status > 400) {
				return updateAdminNotice(xhr);
			}
		};
	}

	addEventListeners();
	function addEventListeners() {
		addDeleteHintListener();
		addEditRowEventListener();
	}

	function clearHintTable() {
		globalTable.find('tbody').find('select, input:text').val('');
		globalTable.find('tbody').find('input:checkbox, input:radio').attr('checked', false);
	}

	function updateAdminNotice(response) {
		var outcome = '';

		if (response.result && response.result.length > 0) {
			outcome = response.result;
		} else {
			outcome = 'error';
			response.msg = 'Error saving resource hint.';
		}

		toggleAdminNotice('add', outcome);
		adminNoticeElem.getElementsByTagName('p')[0].innerHTML = response.msg;

		setTimeout(function() {
			toggleAdminNotice('remove', outcome);
		}, 10000 );

	}

	function toggleAdminNotice(action, outcome) {
		adminNoticeElem.classList[action]('active');
		adminNoticeElem.classList[action]('notice-' + outcome);
	}

	addEditRowEventListener();
	function addEditRowEventListener() {
		$('span.edit').on('click', function() {
			var hintID = $(this).find('a').attr('id').split('pprh-edit-hint-')[1];
			var allRows = $('tr.pprh-row');
			allRows.removeClass('active');

			var rows = $('tr.pprh-row.' + hintID);
			rows.addClass('active');
			putHintInfoIntoElems(hintID);

			rows.find('button.button.cancel').first().on('click', function() {
				rows.removeClass('active');
			});

			$('tr.pprh-row.edit.' + hintID).find('button.pprh-update').on('click', function(e) {
				createHint(e, 'pprh-edit-' + hintID, 'update');
			});
		});
	}


	function putHintInfoIntoElems(hintID) {
		var json = $('input.pprh-hint-storage.' + hintID).val();
		var data = JSON.parse(json);

		var elems = getRowElems('pprh-edit-' + hintID);
		elems.url.val(data.url);
		elems.hint_type.find('input[value="' + data.hint_type + '"]').attr('checked', true);

		if (data['crossorigin']) {
			elems.crossorigin.attr('checked', true);
		}

		elems.as_attr.val( data['as_attr'] ? data['as_attr'] : '');
		elems.type_attr.val( data['type_attr'] ? data['type_attr'] : '')
	}

	// bulk deletes, enables/disables.
	$('input.pprhBulkAction').on('click', bulkUpdates);

	function bulkUpdates(e) {
		e.preventDefault();
		var idArr = [];
		var op = $(e.currentTarget).prev().val();
		var checkboxes = $('table.pprh-post-table tbody th.check-column input:checkbox');

		$.each(checkboxes, function() {
			if ($(this).is(':checked')) {
				return idArr.push($(this).val());
			}
		});

		if (idArr.length > 0) {
			return createAjaxReq({
				action: op,
				hint_ids: idArr,
			});
		} else {
			window.alert('Please select a row(s) for bulk updating.');
		}

	}

});
