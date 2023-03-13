// Calendar
$(document).ready(function () {
	var from = null;
	var to = null;

	// Dashboard calendar click
	$('.calendar-container').calendar({
		prevButton: '<i class="fas fa-chevron-left"></i>',
		nextButton: '<i class="fas fa-chevron-right"></i>',
		monthYearSeparator: " ",
		showThreeMonthsInARow: true,
		enableMonthChange: true,
		enableYearView: true,
		showTodayButton: false,
		todayButtonContent: "Today",
		highlightSelectedWeekday: true,
		highlightSelectedWeek: true,
		showYearDropdown: false,
		min: null,
		max: null,
		weekDayLength: 3,
		startOnMonday: true,
		onClickDate: function (date) {

			var dayClass = $(event.target).parent();

			if (!dayClass.hasClass('disabled') && !dayClass.hasClass('active-day') && event.target.tagName == 'SPAN') {
				var total = $('.active-day').length;

				$(event.target).parent().addClass('active-day');

				if (total == 2) {
					$('.active-day').removeClass('active-day');
					$(event.target).parent().addClass('active-day');
					//clear from-to between days
					$(".filled-day").each(function (el) {
						$(this).removeClass("filled-day");
					});
					//clear from-to days
					$(".half").each(function (el) {
						$(this).remove();
					});
					from = null;
					to = null;
				}
				else if (total == 1) {
					dayFrom = $('.active-day').first().find('span').text(),
						dayTo = $('.active-day').last().find('span').text();
					// fill days between from-to
					$(".calendar-box .day").not(".disabled").each(function (el) {
						let day = parseInt(this.innerText);
						if (Math.floor(day) == day && $.isNumeric(day)) {
							if (day > dayFrom && day < dayTo) {
								$(this).addClass("filled-day");
							}
						}
					});
					// fill from-to days by half
					$('.active-day').first().css("position", "relative");
					$('.active-day').first().append('<div style="position:absolute; background-color:rgba(50, 50, 50, 0.05); right:0; top:0; bottom:0; width:50%" class="half"></div>');
					$('.active-day').last().css("position", "relative");
					$('.active-day').last().append('<div style="position:absolute; background-color:rgba(50, 50, 50, 0.05); left:0; top:0; bottom:0; width:50%" class="half"></div>');

					let d = new Date(date);
					let y = d.getYear() + 1900;
					let m = d.getMonth() + 1;
					from = y + '-' + (m < 10 ? '0' + m : m) + '-' + (parseInt(dayFrom) < 10 ? '0' + dayFrom : dayFrom);
					to = y + '-' + (m < 10 ? '0' + m : m) + '-' + (parseInt(dayTo) < 10 ? '0' + dayTo : dayTo);
					let url = getUrl(window.location.href);
					fetch_data(1, url, null, from, to);
				}
			}
		}
	});


	// Ajax Dropdown calendar filter
	$('#dtFrom, #dtTo').datepicker({ format: "yyyy-mm-dd", weekStart: 1, autoclose: true });
	$('#dateFrom, #dateTo').datepicker({ format: "yyyy-mm-dd", weekStart: 1, autoclose: true });
	$('#dateFrom, #dateTo').change(function (e) {
		from = $('#dateFrom').val();
		to = $('#dateTo').val();
		let find = $('#find').val();
		if (from && to && from <= to) {
			$('.btn-invoice').removeClass('disabled');
			let url = getUrl(window.location.href);
			fetch_data(1, url, find, from, to);
		} else {
			$('.btn-invoice').addClass('disabled');
		}
	});
	// Ajax pagination
	$(document).on('click', 'ul.pagination a', function (event) {
		event.preventDefault();
		let page = $(this).attr('href').split('page=')[1];
		let url = getUrl(window.location.href);
		let find = $('#find').val();
		fetch_data(page, url, find, from, to);
	});
	// Ajax request
	function fetch_data(page, url, find = null, startDate = null, endDate = null) {
		$.ajax({
			url: url + '?page=' + page,
			type: 'get',
			data: { find: find, from: startDate, to: endDate },
			beforeSend: function () {
				loader();
			},
			success: function (response) {
				console.log(response)
				if(response.data!== undefined){
					$('#pendingCnt').text(response.data.pending.current);
					$('#shippedCnt').text(response.data.shipped.current);
					$('#shippedPerc').text(response.data.shipped.ratio+'%');
					$('#newCnt').text(response.data.new.current);
					$('#newPerc>span').text(response.data.new.ratio);
					$('#newPerc').attr("class", response.data.new.color);
					$('#newPerc>i').attr("class", response.data.new.icon);
					$('#unffilledCnt').text(response.data.unfulfilled.current);
					$('#unffilledPerc').text(response.data.unfulfilled.ratio+'%');
				}
				$('#table').html(response);
				closeLoader();
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		});
	}

	function getUrl(url) {
		if (url.includes("orders")) return url.replace("/orders", "/ajaxorders");
		else if (url.includes("search")) return url.replace("/search", "/ajaxsearch");
		else return 'ajax-dashboard';
	}



	//REPORTS
	const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	const d = new Date();
	var month = d.getMonth();
	let year = d.getYear() + 1900;
	var weekStart = 1;
	var weekEnd = 7;
	$('#month').text(months[month] + ' ' + year);
	//week click
	var currentweek;
	// call report if url contains 'report'
	if ((window.location.href).includes("report")) {
		monthChanged();




		//function addData(chart, label, data) {
		// myChart.data.labels.push('aa','bb');
		// myChart.data.datasets[0].data.push(3, 4);
		// myChart.update();


		//function removeData(chart) {
		// chart.data.labels.pop();
		// chart.data.datasets.forEach((dataset) => {
		//     dataset.data.pop();
		// });
		// chart.update();

	}


	$('.week').click(function (e) {
		var el;
		if (e.target.tagName == 'SPAN') el = e.target.parentElement;
		else el = e.target;
		$('.week').removeClass('active');
		el.classList.add('active');
		if (currentweek != el.getAttribute('data-week')) {
			console.log(el.getAttribute('data-week'));
			currentweek = el.getAttribute('data-week');
			let c = currentweek.replace('week-', '');
			weekStart = c.split('-')[0];
			weekEnd = c.split('-')[1];
			console.log(weekStart, weekEnd);
			//....
		}
	});


	$('#prevMonth').click(function () {
		if (month == 0) {
			month = 11;
			year--;
		} else {
			month--;
		}
		monthChanged();
	});

	$('#nextMonth').click(function () {
		if (month == 11) {
			month = 0;
			year++;
		} else {
			month++;
		}
		monthChanged();
	});

	function monthChanged() {
		$('#month').text(months[month] + ' ' + year);
		$.ajax({
			url: '/get-report',
			type: 'get',
			data: { year: year, month: month },
			beforeSend: function () {
				loader();
			},
			success: function (response) {
				closeLoader();
				//console.log(response, response.byWeeks, response.byCountries);
				$('#week1').text(response.byWeeks[0] + ' Products');
				$('#week2').text(response.byWeeks[1] + ' Products');
				$('#week3').text(response.byWeeks[2] + ' Products');
				$('#week4').text(response.byWeeks[3] + ' Products');

				// if no data by countries 
				$('#country-data').html('');
				if (Object.keys(response.byCountries).length) {
					Object.keys(response.byCountries).forEach(key => {
						$('#country-data')
							.append($('<tr/>')
								.append($('<td/>', { text: response.byCountries[key].country }))
								.append($('<td/>', { text: response.byCountries[key].orders }))
								.append($('<td/>', { text: parseInt(response.byCountries[key].price) }))
							)
					});
				}
				myChart.data.labels = [];
				myChart.data.datasets[0].data = [];
				myChart.data.labels.push('01', '02', '03', '04', '05', '06', '07');
				response.byDays.slice(0, 7).forEach(e => myChart.data.datasets[0].data.push(e));
				myChart.update();
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		});
	}

	// Highlight active and expanded row
	$('body').on('click', '.listing-table .accordion-toggle', function () {
		$('.listing-table tr[id*="order-"]').removeClass('table-details');
		var ariaExpandedAttr = $(this).attr('aria-expanded');
		if ($(this).hasClass("collapsed") || (typeof ariaExpandedAttr == 'undefined' || ariaExpandedAttr === true)) {
			$(this).parent().addClass('table-details');
		}
	});

}); // End ready





// Show message
function showMessage(text = null) {
	$('.message').removeClass('d-none');
	$('.message b').html(text);
	$('.message').animate({ opacity: 1 });
}

// Close message
function closeMessage() {
	$(".message").animate({ opacity: 0 }, function () {
		$('.message b').html("");
	});
	setTimeout(function() { 
		$('.message').addClass('d-none');
   }, 500);
}

// Show fail message
function showFailMessage(text = null) {
	$('.fail-message').removeClass('d-none');
	$('.fail-message b').html(text);
	$('.fail-message').animate({ opacity: 1 });
}

// Close fail message
function closeFailMessage() {	
	$(".fail-message").animate({ opacity: 0 }, function () {
		$('.fail-message b').html("");
	});
	setTimeout(function() { 
		$('.fail-message').addClass('d-none');
   }, 500);
}

// Loader
function loader() {
	$('.loader').fadeIn();
}


// Close loader
function closeLoader() {
	$('.loader').fadeOut();
}


// Show tools
function showTools() {
	$('.tools').animate({ opacity: 1 });
}


// Hide tools
function hideTools() {
	$('.tools').animate({ opacity: 0 });
}


// Get all ids
function getAllIds() {
	var ids = [];

	$('.order-checker').each(function () {
		if ($(this).is(":checked")) {
			ids.push($(this).val());
		}
	});

	return ids;
}


// Remorve item
function removeIten(event) {
	event.preventDefault();

	// Show loader
	loader();

	$.ajax({
		url: '/remove-order',
		type: 'post',
		data: { ids: getAllIds() },
		success: function (response) {
			for (i = 0; i <= response.length; i++) {
				$('.order-' + response[i]).fadeOut();
			}
		}
	});

	// Closer loader
	closeLoader();
}


// Select all
var totalSum = 0; // for paypal payment
function selectAll(event) {
	totalSum = 0;
	if ($(event.target).is(':checked')) {

		$('.order-checker').each(function () {
			$(this).prop('checked', true);

			var attr = $(this).attr('data-paysum');
			if (typeof attr !== 'undefined' && attr !== false) {
				totalSum += parseFloat($(this).attr('data-paysum'));
			}
		});

		totalSum = (Math.round(totalSum * 100) / 100);
		$("#pay_paypal").html("Pay " + totalSum + "€");
		console.log("******");

		$('.buttons button').removeClass('disabled');

		showTools();
	} else {

		$('.order-checker').each(function () {
			$(this).prop('checked', false);
		});

		$('#pay_paypal').html('<i class="fab fa-paypal mr-2"></i> Pay');

		$('.buttons button').addClass('disabled');

		hideTools();
	}
}


// Check order
function checkOrder(event) {
	var total = $('input:checkbox:checked').length;
	if ($(event.target).is(":checked")) {
		$('.buttons button').removeClass('disabled');
		showTools();
	}
	else {
		if (total == 0) {
			$('.buttons button').addClass('disabled');
			hideTools();
			$('#pay_paypal').html('<i class="fab fa-paypal mr-2"></i> Pay');
		}
	}
	if (total > 0) {
		totalSum = 0;
		$('.order-checker').each(function () {
			var attr = $(this).attr('data-paysum');
			if (typeof attr !== 'undefined' && attr !== false && $(this).attr('data-paysum') > 0) {
				if ($(this).is(":checked")) {
					totalSum += parseFloat($(this).attr('data-paysum'));
					totalSum = (Math.round(totalSum * 100) / 100);
					$("#pay_paypal").html("Pay " + totalSum + "€");
				}
			}
		});
	}
	if (totalSum == 0) {
		$('#pay_paypal').html('<i class="fab fa-paypal mr-2"></i> Pay');
		$('#pay_paypal').addClass('disabled');
	}
}


// Move to
function moveTo(event, pending) {
	event.preventDefault();

	// Show loader
	loader();

	$.ajax({
		url: '/move-to',
		type: 'post',
		data: { ids: getAllIds(), pending: pending },
		success: function (response) {
			for (i = 0; i <= response.length; i++) {
				$('.order-' + response[i]).fadeOut();
			}
		}
	});

	// Closer loader
	closeLoader();
}


// Download
function download(type, action = null) {
	var orders = [];

	// Gte checked orders ids
	$(".order-checker").each(function () {
		var id = $(this).attr('id'),
			file = $(this).attr('data-file');

		if ($(this).is(':checked')) {
			orders.push({
				"id": id,
				"file": file
			});
		}
	});

	// Send request to download files
	if (orders != '') {
		$.ajax({
			url: '/download',
			type: 'post',
			data: { type: type, orders: orders },
			beforeSend: function () {
				loader();
			},
			success: function (response) {
				if (response['status'] == 'success') {
					// Hide download button
					$('.buttons button').addClass('disabled');

					// Hide tools panel
					// hideTools();

					// Hide orders
					for (i = 0; i < orders.length; i++) {
						$('input[type=checkbox]').prop("checked", false);

						if (action == 'hide') {
							$('.order-' + orders[i]['id']).hide().remove();
						}
					}

					// Get orders total
					var total = $('.list-group-item').length;

					if (total <= 1) {
						$('ul.list-group').last().append(`<h3 class="w-100 text-center my-4">Nothing here</h3>`);
					}

					// Show notification
					// showMessage(`Orders moved to <a href="/orders/pending">Pending</a>`);
					showMessage(`Print file successfully downloaded`);

					// Redirect to download url
					window.location.href = '/archive/' + response['file'];
				}

				// Closer loader
				closeLoader();
			}
		});
	}
}


// Call DHL
function callDHL() {
	var orders = [];

	// Gte checked orders ids
	$(".order-checker").each(function () {
		var id = $(this).attr('id'),
			file = $(this).attr('data-file');

		if ($(this).is(':checked')) {
			orders.push({
				"id": id,
				"file": file
			});
		}
	});

	// Send request to download files
	if (orders != '') {
		$.ajax({
			url: '/call-dhl',
			type: 'post',
			data: { orders: orders },
			beforeSend: function () {
				loader();
			},
			success: function (response) {
				// Hide button
				$('.buttons').animate({ 'right': '-300px' });

				// Hide tools panel
				hideTools();

				if (response['status'] == 'success') {
					// Hide orders
					for (i = 0; i < orders.length; i++) {
						$('input[type=checkbox]').prop("checked", false);

						$('.order-' + orders[i]['id']).hide().remove();
					}

					// Get orders total
					var total = $('.list-group-item').length;

					if (total <= 1) {
						$('ul.list-group').last().append(`<h3 class="w-100 text-center my-4">Nothing here</h3>`);
					}

					// Show notification
					showMessage(`Orders moved to <a href="/orders/dhl-pending">DHL</a>`);
				}
				else {
					// Show notification
					showFailMessage(response['status']);
				}

				// Closer loader
				closeLoader();
			}
		});
	}
}


// Show edit modal
function showEditModal(event, id) {
	event.preventDefault();
	$("#validationResult").removeClass('text-success');
	$("#validationResult").removeClass('text-warning');
	$("#validationResult").removeClass('text-danger');
	$("#validationResult").text('');

	$('[name=id]').val(id);

	$.ajax({
		url: '/get-order-data',
		type: 'post',
		data: { id: id },
		beforeSend: function () {
			loader();
		},
		success: function (response) {
			$('.dhl24').removeClass('d-none');
			if (response['status'] == 'success') {
				if(response['company_id'] > 2){
					$('[name=width]').val(response['dhl24data']?response['dhl24data']['width']:0);
					$('[name=height]').val(response['dhl24data']?response['dhl24data']['height']:0);
					$('[name=length]').val(response['dhl24data']?response['dhl24data']['length']:0);
					$('[name=weight]').val(response['dhl24data']?response['dhl24data']['weight']:0);
					$('[name=content]').val(response['dhl24data']?response['dhl24data']['content']:'');
					$('[name=packagetype]').val(response['dhl24data']?response['dhl24data']['packagetype']:1);
				}else{
					$('.dhl24').addClass('d-none');
				}
				$('[name=address1]').val(response['address1']);
				$('[name=address2]').val(response['address2']);
				$('[name=city]').val(response['city']);
				$('[name=zip]').val(response['zip']);
				$('[name=country]').val(response['country']);
				$('[name=country_code]').val(response['country_code']);

				let weights = response['weights'];
				$('#images').empty();
				let skuList = $('#sku');
				skuList.find('option').remove();
				weights.forEach((weight, i) => {
					skuList.append('<option value="' + (i + 1) + '">' + weight.quantity + ' X ' + weight.sku + '</option>')
					let winput = $('<input type="text">').attr({ id: 'weight-' + weight.id, name: 'weight-' + weight.id, value: weight.grams, class: 'edit-modal-input form-control form-control-sm mb-3 d-none' });
					$('#images').append(winput);
				});
				$('#images input:first').removeClass('d-none');
			}
			// Closer loader
			closeLoader();
		}
	});

	$('.edit-modal').animate({ right: 0 }, "slow");
}
// Select sku change event
$('#sku').change(function () {
	$('#images input').addClass('d-none');
	let el = $(this).val();
	$("#images input:nth-child(" + el + ")").removeClass('d-none')
});


// ValidateAddress
function validateAddress(id) {
	var data = $('#orderData').serializeArray();
	var orderid = $('[name=id]').val();
	$("#validationResult").removeClass('text-success');
	$("#validationResult").removeClass('text-warning');
	$("#validationResult").removeClass('text-danger');
	$("#validationResult").text('');
	$.ajax({
		url: '/validate-address',
		type: 'post',
		data: { data: data, id: id },
		beforeSend: function () {
			loader();
		},
		success: function (response) {
			// Closer loader
			closeLoader();

			// Show notification
			if (response['status'] = 'success') {
				//closeEditModal();
				let style = '';
				if (response.statusCode == 0 && response.statusText == 'ok') {
					style = 'text-success';
					$.ajax({
						url: '/clear-Dhl-error',
						type: 'post',
						data: { id: orderid },
						success: function (response) {
							$("tr#order-" + orderid).remove();
						}
					});
				} else if (response.statusCode == 0 && response.statusText.includes('Weak')) {
					style = 'text-warning';
				} else {
					style = 'text-danger';
				}
				$("#validationResult").addClass(style);
				$("#validationResult").text(response.statusMsg);
			}
			else {
				showMessage(`Can not get response from address validation`);
			}
		}
	});

	//$('.edit-modal').animate({right : 0}, "slow");
}


// Show edit modal
function saveEditModalData(id) {
	var data = $('#orderData').serializeArray();

	$.ajax({
		url: '/save-order-data',
		type: 'post',
		data: { data: data, id: id },
		beforeSend: function () {
			loader();
		},
		success: function (response) {
			// Closer loader
			closeLoader();
			// Show notification
			if (response['status'] == 'success') {
				showMessage(`Order content has been updated`);
				$(".edit-modal-input").val("");
				closeEditModal();
			}
			else {
				showMessage(`Order content could not be updated`);
			}
		}
	});

	$('.edit-modal').animate({ right: 0 }, "slow");
}


// Close edit modal
function closeEditModal() {
	$('.edit-modal').animate({ right: "-450px" }, "slow");
	$(".edit-modal-input").val("");
}


// HOLD
// Change hold image modal
$('#changeImageModal').on('show.bs.modal', function (event) {
	var img = $(event.relatedTarget);
	var orderDataId = img[0].dataset.imageid;
	$('#hsku').text('');
	$('#hqty').text('');
	$("#image").attr('src', '');
	$('#hname').text('');
	$('#haddress1').text('');
	$('#haddress2').text('');
	$('#hcity').text('');
	$('#hzip').text('');
	$('#hcountry').text('');
	$('#hphone').text('');

	$.ajax({
		url: '/get-hold-image-data',
		type: 'post',
		data: { id: orderDataId },
		beforeSend: function () {
			//loader();
		},
		success: function (response) {
			$('#hsku').text(response.orderData.sku);
			$('#hqty').text(response.orderData.quantity);
			$("#image").attr('src', 'data:image/png;base64,' + response.orderData.imgBase64);
			$('#hname').text(response.orderInfo.name);
			$('#haddress1').text(response.orderInfo.address1);
			$('#haddress2').text(response.orderInfo.address2);
			$('#hcity').text(response.orderInfo.city);
			$('#hzip').text(response.orderInfo.zip);
			$('#hcountry').text(response.orderInfo.country);
			$('#hphone').text(response.orderInfo.phone);
			$('#hid').val(orderDataId);
			$('#hordername').val(response.orderInfo.orderName);
		}
	});
});


$('#uploadHoldImage').on('show.bs.modal', function (event) {
	var img = $(event.relatedTarget);
	var orderDataId = img[0].dataset.imageid;
	var orderName = img[0].dataset.ordername;
	$('#itemid').val(orderDataId);
	$('#hordername').val(orderName);
	$('label[for="imgupload"]').html($('<span/>', { text: 'Click here to upload image' }));
});


function processupload() {
	var files = $('#imgupload')[0].files
	if (files.length > 0) {
		var data = new FormData();
		data.append('file', files[0]);
		data.append('_token', $('[name="csrf-token"]').attr('content'));
		data.append('orderName', $('#hordername').val());
		data.append('id', $('#itemid').val());
	}
	var url = '/upload-hold-image';
	$.ajax({
		url: url,
		method: 'post',
		data: data,
		processData: false,
		contentType: false,
		beforeSend: function () {
			$('label[for="imgupload"]').html($('<div>', { class: 'loader2' }));
		},
		success: function (response) {
			if (response['status'] == 'success') {
				$('label[for="imgupload"]').html($('<img>', { id: 'image', src: 'data:image/png;base64,' + response.result.imgBase64 }))//src:'theImg.png'
				var [file] = $('#imgupload')[0].files
				if (file) {
					$("#image").attr('src', URL.createObjectURL(file));
					$("#listing" + $('#hid').val()).attr('src', URL.createObjectURL(file));
					$("#pf_" + response.row_id).html($('<img>', { src: response.thumbpath }));
					$("#st_" + response.row_id).html('');
					$("#sku_" + response.row_id).html(response.sku);
					if (response.rowToRemove) {
						$("#order-" + response.rowToRemove).remove();
						$(".child-" + response.rowToRemove).remove();
					}
				}
			}
		}
	});
}


/// SKU
// Change product modal
$('#editProductModal').on('show.bs.modal', function (event) {
	var target = $(event.relatedTarget);
	var productId = target[0].dataset.productid;
	$('#skulist, #skubody, #skuheader').empty();
	$('input#sku_name').removeClass('display-none');
	$('input#sku_name, input#sku_size, input#sku_price, input#sku_print_price, input#sku_quantity, input#hsku, input#product_name, input#printfile_size, input#sku_weight').val('');
	$("#product_image").attr('src', '');
	$('#addeditsku').text("Add SKU");
	$('#hproductid').val(productId);
	$('#addeditsku').prop('disabled', true);
	if (productId > 0) {
		$('#addeditsku').prop('disabled', false);
		$.ajax({
			url: '/get-product-image-data',
			type: 'post',
			data: { id: productId },
			beforeSend: function () {/*loader();*/ },
			success: function (response) {
				// Load image
				if (response.productData.imgBase64)
					$("#product_image").attr('src', 'data:image/png;base64,' + response.productData.imgBase64);
				$("#product_name").val(response.productData.productName);
				if (response.skus.length) {
					$('#skuheader').append($('<th/>').text('SKU'))
						.append($('<th/>').text('Size'))
						.append($('<th/>').text('Weight'))
						.append($('<th/>').text('PF size'))
						.append($('<th/>').text('Price'))
						.append($('<th/>').text('Print price'))
						.append($('<th/>').text('Q-ty'))
						.append($('<th/>').text(''));
				}
				response.skus.forEach(sku => {
					$('#skubody').append($('<tr/>').attr('id', 'sku_row' + sku.id)
						.append($('<td/>').text(sku.name))
						.append($('<td/>').text(sku.size))
						.append($('<td/>').text(sku.weight))
						.append($('<td/>').text(sku.printfile_size))
						.append($('<td/>').text(sku.price))
						.append($('<td/>').text(sku.print_price))
						.append($('<td/>').text(sku.stock_quantity))
						.append($('<td/>')
							.append($('<i/>')
								.attr('class', 'editsku fa fa-pencil-alt text-warning cursor-pointer mr-2')
								.attr('data-sku_id', sku.id)
								.attr('data-product_id', sku.product_id)
							)
							.append($('<i/>')
								.attr('class', 'deletesku fa fa-trash-alt text-danger cursor-pointer')
								.attr('data-sku_id', sku.id)
							)
						)
					);
				});
			}
		});
	} else {
		// new sku dialog
		$('#productModalLabel').text('Create product');
	}
});


// Add or edit sku
$('#addeditsku').click(function () {
	let skuid = $('#hsku').val();
	let productid = $('#hproductid').val();
	$.ajax({
		url: '/save-sku',
		type: 'post',
		data: {
			productid: productid,
			skuid: skuid,
			sku_name: $('input#sku_name').val(),
			sku_size: $('input#sku_size').val(),
			sku_weight: $('input#sku_weight').val(),
			printfile_size: $('input#printfile_size').val(),
			sku_price: $('input#sku_price').val(),
			sku_print_price: $('input#sku_print_price').val(),
			sku_quantity: $('input#sku_quantity').val()
		},
		beforeSend: function () {/*loader();*/ },
		success: function (response) {
			if (response.status == "success") {
				if (response.action == "update") {
					//remove old rows
					$("#sku_row" + skuid).remove();
					$('#skurow' + skuid).remove();
				}
				if (response.skuid)
					skuid = response.skuid;
				//SKU page
				$('#product-' + productid + ' #sku_tbody-' + productid).append($('<tr/>').attr('id', 'skurow' + skuid)
					.append($('<td/>').text($('input#sku_name').val()).attr('class', 'col-2 align-middle'))
					.append($('<td/>').text($('input#sku_size').val()).attr('class', 'col-2 align-middle'))
					.append($('<td/>').text($('input#sku_weight').val()).attr('class', 'col-1 align-middle'))
					.append($('<td/>').text($('input#printfile_size').val()).attr('class', 'col-2 align-middle'))
					.append($('<td/>').text($('input#sku_price').val()).attr('class', 'col-1 align-middle'))
					.append($('<td/>').text($('input#sku_print_price').val()).attr('class', 'col-1 align-middle'))
					.append($('<td/>').text($('input#sku_quantity').val()).attr('class', 'col-1 align-middle'))
				);
				// Render header if not present
				if (!$('#skuheader th').length) {
					$('#skuheader').append($('<th/>').text('SKU'))
						.append($('<th/>').text('Size'))
						.append($('<th/>').text('Weight'))
						.append($('<th/>').text('PF size'))
						.append($('<th/>').text('Price'))
						.append($('<th/>').text('Print price'))
						.append($('<th/>').text('Q-ty'))
						.append($('<th/>').text(''));
				}
				//SKU modal
				$('#skubody').append($('<tr/>').attr('id', 'sku_row' + skuid)
					.append($('<td/>').text($('input#sku_name').val()))
					.append($('<td/>').text($('input#sku_size').val()))
					.append($('<td/>').text($('input#sku_weight').val()))
					.append($('<td/>').text($('input#printfile_size').val()))
					.append($('<td/>').text($('input#sku_price').val()))
					.append($('<td/>').text($('input#sku_print_price').val()))
					.append($('<td/>').text($('input#sku_quantity').val()))
					.append($('<td/>')
						.append($('<i/>')
							.attr('class', 'editsku fa fa-pencil-alt text-warning cursor-pointer mr-2')
							.attr('data-sku_id', skuid)
							.attr('data-product_id', $('#hproductid').val())
						)
						.append($('<i/>')
							.attr('class', 'deletesku fa fa-trash-alt text-danger cursor-pointer')
							.attr('data-sku_id', skuid)
						)
					)
				);
			}
			// clear inputs on successful save
			$('input#sku_name, input#sku_size, input#sku_price, input#sku_print_price, input#sku_quantity, input#printfile_size, input#sku_weight, #hsku').val('');
			// Show SKU input and update button text
			$('#sku_name').removeClass("display-none");
			$('#addeditsku').text("Add SKU");
		}
	});
});


// Edit SKU (pencil)
$(document).on('click', '.editsku', function (e) {
	let skuid = $(e.target)[0].dataset.sku_id;
	let prodid = $(e.target)[0].dataset.product_id;
	$.ajax({
		url: '/get-sku-data',
		type: 'post',
		data: { id: skuid },
		beforeSend: function () {/*loader();*/ },
		success: function (response) {
			console.log(response)
			$('input#sku_name').val(response.skuData.name);
			$('input#sku_name').addClass('display-none');
			$('input#sku_size').val(response.skuData.size);
			$('input#sku_weight').val(response.skuData.weight);
			$('input#printfile_size').val(response.skuData.printfile_size);
			$('input#sku_price').val(response.skuData.price);
			$('input#sku_print_price').val(response.skuData.print_price);
			$('input#sku_quantity').val(response.skuData.stock_quantity);
			$('input#hsku').val(response.skuData.id);
			$('#addeditsku').text("Update SKU");
		}
	});
});


// Delete SKU (bin)
$(document).on('click', '.deletesku', function (e) {
	let id = $(e.target)[0].dataset.sku_id;
	$.ajax({
		url: '/delete-sku',
		type: 'post',
		data: { id: id },
		beforeSend: function () {/*loader();*/ },
		success: function (response) {
			$(e.target).closest("tr").remove();
			$('#skurow' + id).remove();
			$('#hsku').val('');
		}
	});
});


// Save product name
$('#save_name').click(function () {
	let product_id = $('#hproductid').val();
	let product_name = $('#product_name').val();
	$.ajax({
		url: '/save-product-name',
		type: 'post',
		data: {
			product_id: product_id,
			product_name: product_name
		},
		beforeSend: function () {/*loader();*/ },
		success: function (response) {
			if (response.status == "success") {
				$('#addeditsku').prop('disabled', false);
				if (response.product_id) {
					// New order add row??????
					$('#hproductid').val(response.product_id);
					addRowSkuTable({ id: response.product_id, product_name: product_name });
				} else {
					// Change text on SKU page
					$('td#productname-' + product_id).text(product_name);
				}
			}
		}
	});
});


// Upload product image
function uploadProductImage() {
	var files = $('#imgupload')[0].files;
	let product_id = $('#hproductid').val();
	if (files.length > 0) {
		var data = new FormData();
		data.append('file', files[0]);
		data.append('_token', $('[name="csrf-token"]').attr('content'));
		data.append('product_id', product_id);
	}
	var url = '/upload-product-image';
	$.ajax({
		url: url,
		method: 'post',
		data: data,
		processData: false,
		contentType: false,
		success: function (response) {
			if (response.status == "success") {
				$('#addeditsku').prop('disabled', false);
				var [file] = $('#imgupload')[0].files
				if (file) {
					// Replace image on Modal
					$("#product_image").attr('src', URL.createObjectURL(file));
					if (response.product_id) {
						// New order add row??????
						$('#hproductid').val(response.product_id);
						addRowSkuTable({ id: response.product_id, image: URL.createObjectURL(file) });
					} else {
						// Change image on SKU page
						$('#productimage-' + product_id + ' img').attr('src', URL.createObjectURL(file));
					}
				}
			}
		}
	});
}

function addRowSkuTable(obj) {
	if ($('#main_table>tr').length < 30) {
		console.log($('#main_table>tr').length);
		$('#main_table').append($('<tr/>').attr('id', 'product-' + obj.id)
			.append($('<td/>')
				.attr('class', 'col-2 p-1 product-image align-middle')
				.attr('id', 'productimage-' + obj.id)
				.append($('<img/>'))
			)
			.append($('<td/>')
				.attr('class', 'col-3 align-middle text-secondary')
				.attr('id', 'productname-' + obj.id)
			)
			.append($('<td/>')
				.attr('colspan', '5')
				.attr('class', 'col-6 p-1 align-middle')
				.append($('<table/>')
					.attr('class', 'inner-table')
					.append($('<tbody/>')
						.attr('id', 'sku_tbody-' + obj.id)
					)
				)
			)
			.append($('<td/>')
				.attr('class', 'col-1 align-middle')
				.append($('<span/>')
					.attr('data-toggle', 'modal')
					.attr('data-target', '#editProductModal')
					.attr('data-productid', obj.id)
					.attr('class', 'cursor-pointer')
					.append($('<i/>')
						.attr('class', 'fa fa-pencil-alt text-warning')
					)
				)
			)
		);
		console.log(obj);
		if (obj.image)
			$('#productimage-' + obj.id + ' img').attr('src', obj.image);
		if (obj.product_name)
			$('#productname-' + obj.id).html(obj.product_name);
	}
}


// INVOICE
// generate invoice
function generateInvoice() {
	from = $('#dateFrom').val();
	to = $('#dateTo').val();
	$.ajax({
		type: 'GET',
		url: '/generate-invoice',
		data: { from: from, to: to },
		xhrFields: {
			responseType: 'blob'
		},
		success: function (response) {
			var blob = new Blob([response]);
			var link = document.createElement('a');
			link.href = window.URL.createObjectURL(blob);
			link.download = "Invoice.pdf";
			link.click();
		},
		error: function (blob) {
			//console.log(blob);
		}
	});
}


// Users
// Change user status
$('.user-status').on('click', function (event) {
	var checkBoxLabel = $(event.target);
	var user_id = checkBoxLabel[0].dataset.toggleid;
	var status = $('#switch' + user_id)[0].checked;
	$.ajax({
		url: '/toggle-user-status',
		type: 'post',
		data: { id: user_id, status: !status },
		beforeSend: function () {
			//loader();
		},
		success: function (response) {
			if (response['status'] == 'success') {


			}
		}
	});
});

// Get shop name
$('#editShopName').on('show.bs.modal', function (event) {
	var link = $(event.relatedTarget);
	var userId = link.data('userid');
	$('#shop-name').val('');
	$('#userId').val(userId);
	$.ajax({
		url: '/get-shop-name',
		type: 'get',
		data: { userId: userId },
		success: function (response) {
			if (response['status'] == 'success') {
				$('#shop-name').val(response.shopname);
			}
		}
	});
});

// Save shop name
$('#save-shopname').on('click', function (event) {
	userId = $('#userId').val();
	shopname = $('#shop-name').val();
	$.ajax({
		url: '/save-shop-name',
		type: 'post',
		data: { userId: $('#userId').val(), shopname: shopname },
		success: function (response) {
			if (response['status'] == 'success') {
				$('#shopname-' + userId).text(shopname);
				$('#editShopName').modal('hide');
			}
		}
	});
});



// Shipment archive > track order
$('#trackModal').on('show.bs.modal', function (event) {
	var link = $(event.relatedTarget);
	var trackingId = link.data('shipment');
	var modal = $(this);
	modal.find('#trackingid').text(trackingId);
	$('.timeline').html('');
	$('.loader2').removeClass('d-none');
	console.log(formatDateTime('2022-02-17T16:56:00'))
	$.ajax({
		url: '/track-package',
		type: 'post',
		data: { tracking_number: trackingId },
		beforeSend: function () {
			//loader();
		},
		success: function (response) {
			if (response['status'] == 'success') {
				$('.loader2').addClass('d-none');
				response.data.events.forEach(ev => {
					$('.timeline').append('<li><div><span>' + ev.statusCode.charAt(0).toUpperCase() + ev.statusCode.slice(1) + '</span><span>' + formatDateTime(ev.timestamp) + '</span></div><p>' + ev.description + '</p></li>');
				});
			}
		}
	});
})


// Shipment archive > order details
$('#orderDetails').on('show.bs.modal', function (event) {
	var link = $(event.relatedTarget);
	var orderid = link.data('orderid');
	var ordername = link.data('ordername');
	var modal = $(this);
	modal.find('#orderid').text(ordername);
	$('#tracking').text = '';
	$('#ordername').text = '';
	$('#calleddhl').text = '';
	$('#flname').text = '';
	$('#address12').text = '';
	$('#zipcity').text = '';
	$('#country').text = '';
	$('#phone').text = '';
	$('#email').text = '';
	$.ajax({
		url: '/get-order-details',
		type: 'post',
		data: { ordername: ordername },
		beforeSend: function () {
			//loader();
		},
		success: function (response) {
			if (response['status'] == 'success') {
				$('#tracking').text(response.data.order_id);
				$('#ordername').text(response.data.name);
				$('#calleddhl').text(response.data.calledDHL_at);
				$('#flname').text(response.data.first_name + " " + response.data.last_name);
				$('#address12').text(response.data.address1 + " " + (response.data.address2 === null ? "" : response.data.address2));
				$('#zipcity').text(response.data.zip + " " + response.data.city);
				$('#country').text(response.data.country);
				$('#phone').text(response.data.phone);
				$('#email').text(response.data.email);
			}
		}
	});
})


// Replace image (Home, Pending)
$('#replaceImage').on('show.bs.modal', function (event) {
	var link = $(event.relatedTarget);
	var orderdataid = link.data('orderdataid'); // order_item.id
	var key = link.data('key'); // column
	$("#imgPreview").attr('src', '');
	$("#itemid").val(orderdataid);        // order_item.id
	$("#itemn").val(link.data('imagen')); // '_x'
	$("#itemkey").val(key);  // column
	$.ajax({
		url: '/get-replace-image',
		method: 'post',
		data: { orderDataId: orderdataid, key: key },
		success: function (response) {
			//response = JSON.parse(response);
			if (response.status == "success") {
				if (response.result.imgBase64 !== null) {
					$('#uplspan').addClass('d-none');
					$("#imgPreview").removeClass('d-none');
					$("#imgPreview").attr('src', 'data:image/png;base64,' + response.result.imgBase64);
				} else {
					$('#uplspan').removeClass('d-none');
					$("#imgPreview").addClass('d-none');
				}
			}
		}
	});
});

$("#imgReplace").change(function () {
	var [file] = $('#imgReplace')[0].files
	if (file) {
		$('#uplspan').addClass('d-none');
		$("#imgPreview").removeClass('d-none');
		// Replace image on Modal
		$("#imgPreview").attr('src', URL.createObjectURL(file));

		var data = new FormData();
		data.append('file', file);
		data.append('_token', $('[name="csrf-token"]').attr('content'));
		data.append('orderDataId', $("#itemid").val());
		data.append('imagen', $("#itemn").val());
		data.append('key', $("#itemkey").val());

		$.ajax({
			url: '/upload-replace-image',
			method: 'post',
			data: data,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.status == "success") {
					$("#printfile_" + $("#itemid").val() + " a[data-key='" + $("#itemkey").val() + "']").parent().siblings('img').attr('src', URL.createObjectURL(file)).attr('width', 150);
				}
			}
		});
	}
});


// Get sku prices
$('#editCustomSku').on('show.bs.modal', function (event) {
	var link = $(event.relatedTarget);
	var userid = link.data('userid');
	$('#userid').val(userid);
	getUserSkus(userid);
});

// Edit custom SKU (print)Price (pencil edit)
$(document).on('click', '.editcsku', function (e) {
	$('#edit-sku-form').removeClass('d-none');
	$('#sku_price').val($(e.target).closest('tr').children('td.price')[0].innerHTML);
	$('#sku_printprice').val($(e.target).closest('tr').children('td.print_price')[0].innerHTML);
	$('#customskuid').val($(e.target).data('custom_sku_id'));
	$('#skuid').val($(e.target).data('sku_id'));
});

// Toggle custom SKU (eye)
$(document).on('click', '.tglcsku', function (e) {
	$.ajax({
		url: '/toggle-custom-sku',
		type: 'post',
		data: {skuId: $(e.target).data('sku_id'), userId: $('#userid').val()},
		beforeSend: function () {/*loader();*/ },
		success: function (response) {
			if (response.status == "success")
				getUserSkus($('#userid').val());
		}
	});
});

// update custom sku (print)price
$('#customizesku').on('click', function (e) {
	$.ajax({
		url: '/update-custom-sku',
		type: 'post',
		data: {
			skuId: $('#skuid').val(),
			userId: $('#userid').val(),
			price: $('#sku_price').val(),
			print_price: $('#sku_printprice').val()
		},
		beforeSend: function () {/*loader();*/ },
		success: function (response) {
			if (response.status == "success") {
				getUserSkus($('#userid').val());
				$('#edit-sku-form').addClass('d-none');
			}
		}
	});
});

// reload custom sku data by user id
function getUserSkus(id) {
	$('#cskubody').html('');
	$.ajax({
		url: '/get-sku-prices',
		type: 'get',
		data: { userid: id },
		beforeSend: function () {/*loader();*/ },
		success: function (response) {
			if (response.status == "success") {
				console.log(response);
				response.skus.forEach((sku, i) => {
					$('#cskubody').append(
						$('<tr/>').attr('id', 'sku_row' + sku.id)
							.append($('<td/>').text(i + 1))
							.append($('<td/>').text(sku.name))
							.append($('<td/>').attr('class', 'price').text(sku.custom_sku.length ? sku.custom_sku[0].price : sku.price))
							.append($('<td/>').attr('class', 'print_price').text(sku.custom_sku.length ? sku.custom_sku[0].print_price : sku.print_price))
							.append($('<td/>').attr('class', 'total_price').text(sku.custom_sku.length ? (parseFloat(sku.custom_sku[0].price)+parseFloat(sku.custom_sku[0].print_price)).toFixed(2) : (parseFloat(sku.price)+parseFloat(sku.print_price)).toFixed(2)))
							.append($('<td/>')
								.append($('<i/>')
									.attr({
										'class': 'editcsku cursor-pointer fa fa-pencil-alt text-warning mr-2',
										'data-sku_id': sku.id
									})
								)
								.append($('<i/>')
									.attr({
										'class': 'tglcsku cursor-pointer fa fa-eye text-' + (sku.custom_sku.length && sku.custom_sku[0].enabled ? 'success' : 'danger'),
										'data-sku_id': sku.id
									})
								)
							)
					);
				});
			}
		}
	});
}



// Confirm order delete
$('#delOrderModal').on('show.bs.modal', function (event) {
	var target = $(event.relatedTarget);
	var orderid = target[0].dataset.orderid;
	var ordername = target[0].dataset.ordername;
	$("#ordername").text(ordername);
	$("#order-id").val(orderid);
});

$(document).on('click', '#delete-order', function (event) {
	orderid = $("#order-id").val();
	$.ajax({
		url: '/delete-order',
		method: 'post',
		data: { orderid: orderid },
		success: function (response) {
			if (response.status == "success") {
				$('#order-' + orderid).closest('tr').remove();
			}
			$('#delOrderModal').modal('hide');
		}
	});
});



function fixOrder(id) {
	console.log(id);
	$.ajax({
		url: '/send-fix-request',
		method: 'post',
		data: { orderid: id },
		success: function (response) {
			if (response.status == "success") {
				showMessage('Request sent, refresh page in few seconds.')
			}
		}
	});
}


// Submit for print payment
$('#pay_paypal').click(function () {
	$("#pay-printfee").children("input[name='orders[]']").remove();
	$(".form-check-input:checkbox:checked").each(function () {
		$('<input>').attr({ 'type': 'hidden', 'name': 'orders[]' }).val($(this).val()).appendTo('#pay-printfee');
	});
	$('#pay-printfee').submit();
})














// collapse/expand app navmenu
$(".header").click(function () {
	$header = $(this);
	$content = $header.next();
	$content.slideToggle(500);
});


function formatDateTime(dt) {
	let d = new Date(dt);
	let year = d.getFullYear();
	let month = d.getMonth() + 1 < 10 ? '0' + (d.getMonth() + 1) : d.getMonth() + 1;
	let day = d.getDate() + 1 < 10 ? '0' + (d.getDate() + 1) : d.getDate() + 1;
	let hour = d.getHours() + 1 < 10 ? '0' + (d.getHours() + 1) : d.getHours() + 1;
	let min = d.getMinutes() + 1 < 10 ? '0' + (d.getMinutes() + 1) : d.getMinutes() + 1;
	return day + '.' + month + '.' + year + ' ' + hour + ':' + min;
}


// CSRF
$.ajaxSetup({
	headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	}
});