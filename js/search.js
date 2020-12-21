
XDRO = {
	fileReader: new FileReader(),
	validCSVHeaders: [
		"patientid",
		"patient_dob",
		"patient_first_name",
		"patient_last_name",
		"patient_current_sex",
		"patient_street_address_1"
	]
}
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
	var searchBar = $("#query")
	var searchString = searchBar.val()
	
	$("#search-feedback").css('visibility', 'visible')
	
	$.ajax({
		url: XDRO.moduleAddress + "&action=predictPatients&searchString=" + encodeURI(searchString),
		dataType: 'json',
		complete: function(response) {
			XDRO.response = response
			console.log('response', response)
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

XDRO.submit_row_query = function(query_index = 0) {
	// console.log("query_index", query_index)
	if (XDRO.rowQueries == undefined || XDRO.rowQueries[query_index] == undefined) {
		alert("Please upload a .csv search file before searching.")
	} else {
		var rowQuery = XDRO.rowQueries[query_index]
		
		if (!rowQuery || rowQuery == undefined) {
			alert("There was an error finding a valid search query. Please send this error message to the XDRO administrators.")
		}
		
		
		// adding more params
		rowQuery.prefix = getQueryVariable('prefix')
		rowQuery.page = getQueryVariable('page')
		rowQuery.pid = getQueryVariable('pid')
		rowQuery.query_row = query_index
		
		// redirect
		var newUrl = location.protocol + '//' + location.host + location.pathname + "?" + $.param(rowQuery) + "&NOAUTH"
		
		// console.log('newUrl', newUrl)
		// debugger;
		
		window.location.href = newUrl
	}
}

XDRO.make_results_table = function(records) {
	// remove all rows from results table
	var table = $("div#results table").DataTable()
	table.rows().remove()
	
	var table = $("div#results table").DataTable()
	// console.log('records seen by make_results_table', records)
	records.forEach(function (record, i) {
		var link = "<a href=" + XDRO.recordAddress + "&rid=" + record.patientid + ">" + record.patientid + "</a>"
		var node = table.row.add([
			link,
			record.patient_first_name + " " + record.patient_last_name,
			record.patient_dob,
			record.patient_current_sex,
			record.patient_street_address_1,
			record.score.toFixed(1) + "%",
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
	
	// enable/disable seek buttons
	var query_row = Number(getQueryVariable("query_row"))
	if (query_row == XDRO.rowQueries.length - 1) {
		$(".pos_iter").attr('disabled', true)
	} else {
		$(".pos_iter").attr('disabled', false)
	}
	if (query_row == 0) {
		$(".neg_iter").attr('disabled', true)
	} else {
		$(".neg_iter").attr('disabled', false)
	}
}

XDRO.process_selected_file = function(file_text) {
	// make sure we get SOME text out of the selected file
	if (!file_text.length) {
		alert("When the XDRO module read your selected file, it couldn't find valid text data. Please select a .csv file to upload.")
		var input = $('.custom-file-label')
		input.html("Upload a CSV")
		input.val(null)
		return
	}
	
	var lines = file_text.split("\n")
	
	if (lines.length < 2) {
		alert("Selected .csv file must contain at least two rows of data, headers (first row) and at least one row of search parameters (correlating to header columns).")
		var input = $('.custom-file-label')
		input.html("Upload a CSV")
		input.val(null)
		return
	}
	
	var headers = lines[0].split(',').map(function(item) {
		return item.trim()
	})
	
	//	make sure we have at least one valid header (to correlate with searched patient info)
	var validHeaderFound = XDRO.validCSVHeaders.some(function(valid_header) {
		return headers.includes(valid_header)
	})
	if (!validHeaderFound) {
		alert("The XDRO module couldn't find a valid header in the selected .csv file.\nPlease ensure the first csv row in your file contains at least one of the following:\n\n" + XDRO.validCSVHeaders.join("\n"))
		var input = $('.custom-file-label')
		input.html("Upload a CSV")
		input.val(null)
		return
	}
	
	// no errors, get a map of valid header names to column indices (e.g. "patient_dob" maps to csv column 3)
	var headerIndices = {}
	XDRO.validCSVHeaders.forEach(function(validHeader) {
		var foundIndex = headers.findIndex(header => header == validHeader)
		if (foundIndex >= 0)
			headerIndices[validHeader] = foundIndex
	})
	
	// now we can process the remaining lines into structured query objects (held in rowQueries)
	var rowQueries = []
	lines.forEach(function(line, row) {
		// skip header row
		if (row == 0)
			return
		
		var rowQuery = {}
		var lineValues = line.split(',').map(function(item) {
			return item.trim()
		})
		for (const header in headerIndices) {
			var col = headerIndices[header]
			if (lineValues[col])
				rowQuery[header] = lineValues[col]
		}
		
		if (!$.isEmptyObject(rowQuery))
			rowQueries.push(rowQuery)
	})
	XDRO.rowQueries = rowQueries
	
	// store queries in local storage
	try {
		localStorage.setItem('xdro_csv_search_row_queries', JSON.stringify(rowQueries))
		localStorage.setItem('xdro_csv_filename', XDRO.filename)
	} catch(err) {
		alert("REDCap's XDRO module couldn't save the file to local (browser) storage -- try clearing your local storage or restarting your browser in non-private mode. Error message: " + String(err))
	}
	
	XDRO.processedFileThisSession = true
}

XDRO.prev_query = function() {
	XDRO.submit_row_query(Number(getQueryVariable("query_row")) - 1)
}

XDRO.next_query = function() {
	XDRO.submit_row_query(Number(getQueryVariable("query_row")) + 1)
}

XDRO.first_query = function() {
	XDRO.submit_row_query(0)
}

XDRO.last_query = function() {
	XDRO.submit_row_query(XDRO.rowQueries.length - 1)
}

// on document ready
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
			window.location.href = XDRO.recordAddress + "&rid=" + rid + "&NOAUTH"
		}
	})
	
	$("#error_alert").hide()
	$("#file-queries").hide()
	
	// make results table a DataTables table
	$("div#results table").DataTable({
		order: [[5, 'desc']],
		columnDefs: [
			{className: "dt-center", targets: "_all"}
		],
		autoWidth: true,
		pageLength: 15
	});
	
	// if a file has been previously selected/loaded, use rowQueries from that
	try {
		XDRO.rowQueries = JSON.parse(localStorage.getItem("xdro_csv_search_row_queries"))
		XDRO.filename = localStorage.getItem("xdro_csv_filename")
	} catch(exception) {
		
	}
	
	// show search results (should be present if query parameters are set)
	var url_query = getQueryVariable('query');
	if (XDRO.search_results) {
		XDRO.shrinkSearch()
		$("#search-feedback").css('visibility', 'hidden')
		
		if (XDRO.use_file_interface) {
			XDRO.add_file_interface()
			var query_string = ""
			for(const i in XDRO.validCSVHeaders) {
				var field = XDRO.validCSVHeaders[i]
				var param = getQueryVariable(field)
				if (param)
					query_string += param + ", "
			}
			if (query_string.length) {
				$("#query").val(query_string.slice(0, -2))
			}
		} else if (url_query.length) {
			$("#query").val(decodeURIComponent(url_query.replace(/\+/g, " ")))
		}
		
		if (XDRO.search_results.length) {
			XDRO.make_results_table(XDRO.search_results)
		} else {
			$(".dataTables_empty").text("Search for '" + url_query + "' yielded no matching results")
		}
	} else {
		$("#query").val("")
	}
	
	// scroll to top
	window.scroll(0, 0)
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
		window.location.href = XDRO.recordAddress + "&rid=" + rid + "&NOAUTH";
})

$('body').on('change', ".custom-file-input", function(e) {
	var fileName = $(this).val().split('\\').pop()
	XDRO.filename = fileName
	var input = $('.custom-file-label')
	input.html(fileName)
	
	if (e.target.files[0]) {
		XDRO.fileReader.readAsText(e.target.files[0])
	}
})

// load file into localstorage on upload
XDRO.fileReader.addEventListener("load", function() {
	XDRO.process_selected_file(this.result)
})

// helper functions
function getQueryVariable(variable) {	// from: (https://stackoverflow.com/questions/827368/using-the-get-parameter-of-a-url-in-javascript)
	var query = window.location.search.substring(1);
	var vars = query.split("&");
	for (var i=0;i<vars.length;i++) {
		var pair = vars[i].split("=");
		pair = [decodeURIComponent(pair[0]), decodeURIComponent(pair[1])]
		if (pair[0] == variable) {
			return pair[1];
		}
	}
}