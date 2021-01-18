// jQuery(document).ready(function($) {

	(function (global, factory) {
		global.pprhAdminJS = factory();
	}(this, function() {

		'use strict';
		var $ = jQuery;
		var currentURL = document.location.href;
		var adminNoticeElem = document.getElementById('pprhNotice');

		if (/pprh-plugin-settings/.test(currentURL)) {
			var emailSubmitBtn = document.getElementById('pprhSubmit');
			emailSubmitBtn.addEventListener("click", emailValidate);
		} else if ( /post\.php/.test(currentURL))  {
			var checkoutModal = $('input#pprhOpenCheckoutModal');
			if (typeof checkoutModal === "object") {
				checkoutModal.on('click', openCheckoutModal);
			}
		}

		function openCheckoutModal() {
			// var windowWidth = window.innerWidth;
			// var windowHeight = window.innerHeight;
			// var leftSpace = (windowWidth - 700) / 2;
			window.open( 'https://sphacks.io/checkout', '_blank', '', false );
			// window.open( 'https://sphacks.io/checkout', '_blank', 'height=800,left=100,scrollbars=yes,top=300,width=700', false );
		}


		addEventListeners();
		toggleDivs();

		function toggleDivs() {
			var tabs = $('a.nav-tab');
			var divs = $('div.pprh-content');

			tabs.first().toggleClass('nav-tab-active');
			$("#pprh-insert-hints").toggleClass('active');
			// divs.first().toggleClass('active');
			$('table#general').addClass('active');

			if (!tabs) {
				return;
			}

			$.each(tabs, function () {
				$(this).on('click', function (e) {
					var className = e.currentTarget.classList[1];

					if (/general|preconnect|prefetch|prerender/.test(className)) {
						$('table.pprh-settings-table').not('#' + className).css({'display': 'none'});
						$('table#' + className).css({'display': 'block'});
					} else {
						divs.removeClass('active');
						$('div#pprh-' + className).addClass('active');
						e.preventDefault();
					}

					tabs.removeClass('nav-tab-active');
					$(this).addClass('nav-tab-active');
				});
			});
		}

		$('input.pprh-reset').each(function () {
			$(this).on('click', function (e) {
				var text = e.target.defaultValue;
				var res = confirm('Are you sure you want to ' + text + '?');

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

		$('input#pprhSubmitHints').on("click", function (e) {
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
			var hint_url = elems.url.val().replace(/'|"/g, '');
			var hintType = getHintType.call(elems.hint_type);
			var hintObj = createHintObj();

			if (hint_url.length === 0 || !hintType) {
				window.alert('Please enter a proper URL and hint type.');
			} else if (hintObj.hint_type === 'preload' && !hintObj.as_attr) {
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
					post_id: (typeof pprhProAdminJS === "object") ? pprhProAdminJS.GetPostId() : ""
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
			$('span.delete').on('click', function (e) {
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
			var url = pprh_data.admin_url + 'admin-ajax.php';
			xhr.open('POST', url, true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
			var json = JSON.stringify(dataObj);
			var paginationPage = getUrlValue.call('paged');
			var target = 'action=pprh_update_hints&pprh_data=' + json + '&val=' + pprh_data.val;

			if (paginationPage.length > 0) {
				target += '&paged=' + paginationPage;
			}

			xhr.send(target);

			xhr.onreadystatechange = function () {
				if (xhr.readyState === 4 && xhr.status === 200) {
					if (xhr.response.length > 0) {
						var resp = JSON.parse(xhr.response);
						clearHintTable();

						if (resp && resp.result && resp.result.db_result) {
							updateAdminNotice(resp.result.db_result);
							updateTable(resp);
							addEventListeners();
							return;
						}
					}
				}
				// updateAdminNotice('add', xhr);
			};
		}

		function addEventListeners() {
			addDeleteHintListener();
			addEditRowEventListener();
		}

		function clearHintTable() {
			var tbody = document.getElementById('pprh-enter-data').getElementsByTagName('tbody')[0];

			tbody.querySelectorAll('select, input').forEach(function (elem) {
				return elem[(/radio|checkbox/.test(elem.type)) ? 'checked' : 'value'] = '';
			});
		}

		function toggleAdminNotice(response) {
			response = verifyResponse(response);
			updateAdminNotice(response);
		}

		function verifyResponse(response) {
			// if (response.status === 'error') {
			// 	response.msg += response.last_error;
			// }

			var status = (response.status) ? response.status : 'error';
			var msg = (response.msg) ? response.msg : 'Error updating hint. Please contact support or try again later.';

			if ( response.last_error !== '' && ! response.success )   {
				msg = response.last_error;
			}

			response.msg = msg;
			response.status = status;
			// if (!response.success) {
			//
			// }
			// if (!response.success) {
			// 	if (response.status === 'error') {
			// 		response.msg += response.msg + ((response.last_error) ? response.last_error : '');
			// 	} else if (typeof response === "string" && /<code>(.*)?<\/code>/g.test(response)) {
			// 		response.msg += response.split('<code>')[0].split('</code>')[0];
			// 	} else if (response.msg === '') {
			// 		response.msg += 'Error updating hint. Please contact support or try again later.';
			// 	}
			// }

			return response;
		}

		function updateAdminNotice(response) {
			adminNoticeElem.classList.add('active');
			adminNoticeElem.classList.add('notice-' + response.status);
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

			elems.as_attr.val(data['as_attr'] ? data['as_attr'] : '');
			elems.type_attr.val(data['type_attr'] ? data['type_attr'] : '')
		}

		// bulk deletes, enables/disables.
		$('input.pprhBulkAction').on('click', bulkUpdates);

		function bulkUpdates(e) {
			e.preventDefault();
			var idArr = [];
			var op = $(e.currentTarget).prev().val();
			var checkboxes = $('table.pprh-post-table tbody th.check-column input:checkbox');

			$.each(checkboxes, function () {
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

		function licenseKeyStuff() {
			var licKeyElem = $('input#pprhLicenseKey');
			var activateLicBtn = $('input#pprhActivateLicense');
			var purchaseLic = $('input#pprhOpenCheckoutModal');

			licKeyElem.on('keyup', function() {
				if ( $(this).val().length === 23) {
					activateLicBtn.addClass('button-primary');
				}
			});
		}
		licenseKeyStuff();


		return {
			ToggleAdminNotice: toggleAdminNotice,
			CreateAjaxReq: createAjaxReq,
			UpdateAdminNotice: updateAdminNotice
		}

	}));

// });