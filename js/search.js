
XDRO = {}
XDRO.shrinkSearch = function() {
	$("#search-info").html("<h6><b>Results for:</b></h6>")
	$("#search-info").css('padding', '20px 8px')
	$("#search-info").removeClass('col-4').addClass('col-2')
	$("#search-input").removeClass('col-8').addClass('col-10')
	$(".result-instructions").css('visibility', 'visible')
	$("#file-search").hide()
	$("#results").css('visibility', 'visible')
	$("#error_alert").hide()
}

XDRO.predictPatients = function() {
	var searchBar = $("#search-input input")
	var searchString = searchBar.val()
	
	$("#search-feedback").css('visibility', 'visible')
	
	$.ajax({
		url: XDRO.moduleAddress + "&action=predictPatients&searchString=" + encodeURI(searchString),
		dataType: 'json',
		complete: function(response) {
			XDRO.response = response
			// console.log('response', response)
			if (response.responseJSON && response.responseJSON.length > 0) {
				XDRO.showPredictions(response.responseJSON)
			} else {
				// no results so hide predictions
				$("#autocomplete").css('display', 'none')
			}
			
			$("#search-feedback").css('visibility', 'hidden')
		}
	})
}

XDRO.showPredictions = function(predictions) {
	// console.log('predictions', predictions)
	var items = ""
	
	predictions.forEach(function(patient) {
		items += '<span data-rid="' + patient.patientid + '">"<span class="predict-name">' + patient.patient_first_name + ' ' + patient.patient_last_name + '</span>" in record with Patient ID: # <b>' + patient.patientid + '</b> (<i>DOB: ' + patient.patient_dob + ', ' + patient.patient_current_sex + ', ' + patient.patient_street_address_1 + '</i>)</span>'
	})
	
	$("#autocomplete").html(items)
	$("#autocomplete").css('display', 'flex')
	
	var search = $("#search-input input")
	$("#autocomplete").css('top', search.position().top + search.height() + 'px')
	$("#autocomplete").css('left', search.position().left + 'px')
}

XDRO.submit_manual_query = function() {
	var searchBar = $("#search-input input")
	var searchString = searchBar.val()
	XDRO.query_string = searchString
	$("#search-feedback").css('visibility', 'visible')
	
	$.ajax({
		url: XDRO.moduleAddress + "&action=manualQuery&searchString=" + encodeURI(searchString),
		dataType: 'json',
		complete: function(response) {
			XDRO.response = response
			
			if (response.responseJSON) {
				var records = response.responseJSON
				XDRO.make_results_table(records)
				XDRO.shrinkSearch()
			}
			
			// update/re-draw results table
			// table.draw()
			if (records.length == 0) {
				$(".dataTables_empty").text("Search for '" + XDRO.query_string + "' yielded no record results")
			}
			
			$("#search-feedback").css('visibility', 'hidden')
		}
	})
}

XDRO.submit_file_query = function () {
	if (!$("#upload_csv").prop('files'))
		return
	if (!$("#upload_csv").prop('files')[0])
		return
	
	console.log('sending file query ajax')
	var form_data = new FormData()
	form_data.append('client_file', $("#upload_csv").prop('files')[0])
	$.ajax({
		type: "POST",
		url: XDRO.CSVSearchAddress,
		data: form_data,
		success: XDRO.file_search_done,
		dataType: 'json',
		cache: false,
		contentType: false,
		processData: false
	})
}

XDRO.file_search_done = function(response) {
	console.log('response', response)
	XDRO.response = response
	
	XDRO.shrinkSearch()
	
	// add record iterator buttons and info div
	XDRO.add_file_interface()
	// update results table
	XDRO.show_results_for_row_query(0)
}

XDRO.make_results_table = function(records) {
	// remove all rows from results table
	var table = $("div#results table").DataTable()
	table.rows().remove()
	
	var table = $("div#results table").DataTable()
	console.log('records seen by make_results_table', records)
	records.forEach(function (record, i) {
		var link = "<a href=" + XDRO.recordAddress + "&rid=" + record.patientid + ">" + record.patientid + "</a>"
		var node = table.row.add([
			link,
			record.patient_first_name + " " + record.patient_last_name,
			record.patient_dob,
			record.patient_current_sex,
			record.patient_street_address_1,
			"<input class='cbox' data-rid='" + record.patientid + "' type='checkbox'>",
		]).node()
		
		$(node).addClass('highlightable').attr('data-rid', record.patientid)
	})
	
	table.draw()
}

XDRO.add_file_interface = function() {
	// add buttons
	var leftChev = String.fromCharCode(0xf054);
	var rightChev = '\uF054';
	first = '<button type="button" class="btn btn-primary mx-1 neg_iter" onclick="XDRO.first_query()"><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i></button>'
	prev = '<button type="button" class="btn btn-primary mx-1 neg_iter" onclick="XDRO.prev_query()"><i class="fa fa-chevron-left"></i></button>'
	next = '<button type="button" class="btn btn-primary mx-1 pos_iter" onclick="XDRO.next_query()"><i class="fa fa-chevron-right"></i></button>'
	last = '<button type="button" class="btn btn-primary mx-1 pos_iter" onclick="XDRO.last_query()"><i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></button>'
	$("#search-input div:eq(0)").prepend(prev)
	$("#search-input div:eq(0)").prepend(first)
	$("#search-input div:eq(0)").append(next)
	$("#search-input div:eq(0)").append(last)
	
	// show file queries alert
	$("#file-queries").show()
	$("#file-queries .filename").text(XDRO.filename)
	$("#search").css('margin-top', '10px')
}

XDRO.prev_query = function() {
	XDRO.row_query_index -= 1
	XDRO.show_results_for_row_query(XDRO.row_query_index)
}

XDRO.next_query = function() {
	XDRO.row_query_index += 1
	XDRO.show_results_for_row_query(XDRO.row_query_index)
}

XDRO.first_query = function() {
	XDRO.row_query_index = 0
	XDRO.show_results_for_row_query(XDRO.row_query_index)
}

XDRO.last_query = function() {
	XDRO.row_query_index = XDRO.response.rows.length-1
	XDRO.show_results_for_row_query(XDRO.row_query_index)
}

XDRO.show_results_for_row_query = function(index) {
	XDRO.row_query_index = index
	
	// enable/disable seek buttons
	if (XDRO.row_query_index == XDRO.response.rows.length-1) {
		$(".pos_iter").attr('disabled', true)
	} else {
		$(".pos_iter").attr('disabled', false)
	}
	if (XDRO.row_query_index == 0) {
		$(".neg_iter").attr('disabled', true)
	} else {
		$(".neg_iter").attr('disabled', false)
	}
	
	var rows = XDRO.response.rows
	XDRO.make_results_table(rows[index].results)
	
	var query_string = ""
	for (const name in rows[index].query) {
		query_string += String(rows[index].query[name]) + " "
	}
	query_string = query_string.trimEnd()
	
	// update file queries area
	$("span.records").text(String(XDRO.row_query_index + 1) + " / " + String(rows.length))
	$("#search-input input").val(query_string)
	console.log('query_string', query_string)
	
	$("div#results table").DataTable().draw()
	if (rows[index].results.length == 0) {
		$(".dataTables_empty").text("Search for '" + query_string + "' yielded no record results")
	}
}

$(function() {
	// autocomplete prediction stuff
	$("#search-input input").on('input', function() {
		clearTimeout(XDRO.predict_timer)
		$("#error_alert").hide()
		XDRO.predict_timer = setTimeout(XDRO.predictPatients, 500)
	})
	
	// forward to patient record on prediction clicked
	$("#autocomplete").on('click', 'span', function(e) {
		var span = $(e.target)
		if (span.hasClass('predict-name')) {
			span = span.parent('span')
		}
		var rid = span.attr('data-rid')
		if (rid) {
			window.location.href = XDRO.recordAddress + "&rid=" + rid
		}
	})
	
	$("#error_alert").hide()
	$("#file-queries").hide()
	
	// make results table a DataTables table
	$("div#results table").DataTable({
		columnDefs: [
			{
				targets: [5],
				orderable: false
			}
		],
		pageLength: 15
	});
	
	// XDRO.shrinkSearch()
	// XDRO.add_file_interface()
})

// hide autocomplete predictions when click outside autocomplete div
$(document).mouseup(function (e){
	var container = $("#autocomplete");
	if (!container.is(e.target) && container.has(e.target).length === 0){
		container.hide();
	}
}); 

$("body").on("click", ".highlightable", function(e) {
	var rid = $(this).attr('data-rid')
	if (rid)
		window.location.href = XDRO.recordAddress + "&rid=" + rid;
})

$('body').on('change', ".custom-file-input", function() {
	var fileName = $(this).val().split('\\').pop()
	$('.custom-file-label').html(fileName)
	XDRO.filename = fileName
})