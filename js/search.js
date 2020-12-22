
ARIES = {
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
ARIES.shrinkSearch = function() {
	$("#search-info").html("<h6><b>Results for:</b></h6>")
	$("#search-info").css('padding', '20px 8px')
	$("#search-info").removeClass('col-4').addClass('col-2')
	$("#search-input").removeClass('col-8').addClass('col-10')
	$(".result-instructions").css('visibility', 'visible')
	$("#file-search").hide()
	$("#results").css('visibility', 'visible')
	$("#error_alert").hide()
}

ARIES.predictPatients = function() {
	var searchBar = $("#query")
	var searchString = searchBar.val()
	
	$("#search-feedback").css('visibility', 'visible')
	
	$.ajax({
		url: ARIES.moduleAddress + "&action=predictPatients&searchString=" + encodeURI(searchString) + "&NOAUTH",
		dataType: 'json',
		complete: function(response) {
			ARIES.response = response
			// console.log('response', response)
			if (response.responseJSON && response.responseJSON.length > 0) {
				ARIES.showPredictions(response.responseJSON)
			} else {
				// no results so hide predictions
				$("#autocomplete").css('display', 'none')
			}
			
			$("#search-feedback").css('visibility', 'hidden')
		}
	})
}

ARIES.showPredictions = function(predictions) {
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

ARIES.submit_row_query = function(query_index = 0) {
	// console.log("query_index", query_index)
	if (ARIES.rowQueries == undefined || ARIES.rowQueries[query_index] == undefined) {
		alert("Please upload a .csv search file before searching.")
	} else {
		var rowQuery = ARIES.rowQueries[query_index]
		
		if (!rowQuery || rowQuery == undefined) {
			alert("There was an error finding a valid search query. Please send this error message to the ARIES administrators.")
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

ARIES.make_results_table = function(records) {
	// remove all rows from results table
	var table = $("div#results table").DataTable()
	table.rows().remove()
	
	var table = $("div#results table").DataTable()
	// console.log('records seen by make_results_table', records)
	records.forEach(function (record, i) {
		var link = "<a href=" + ARIES.recordAddress + "&rid=" + record.patientid + ">" + record.patientid + "</a>"
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

ARIES.add_file_interface = function() {
	// add buttons
	var leftChev = String.fromCharCode(0xf054);
	var rightChev = '\uF054';
	first = '<button type="button" class="btn btn-primary mx-1 neg_iter" onclick="ARIES.first_query()"><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i></button>'
	prev = '<button type="button" class="btn btn-primary mx-1 neg_iter" onclick="ARIES.prev_query()"><i class="fa fa-chevron-left"></i></button>'
	next = '<button type="button" class="btn btn-primary mx-1 pos_iter" onclick="ARIES.next_query()"><i class="fa fa-chevron-right"></i></button>'
	last = '<button type="button" class="btn btn-primary mx-1 pos_iter" onclick="ARIES.last_query()"><i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></button>'
	$("#search-input div:eq(0)").prepend(prev)
	$("#search-input div:eq(0)").prepend(first)
	$("#search-input div:eq(0)").append(next)
	$("#search-input div:eq(0)").append(last)
	
	// show file queries alert
	$("#file-queries").show()
	$("#file-queries .filename").text(ARIES.filename)
	$("#search").css('margin-top', '10px')
	
	// enable/disable seek buttons
	var query_row = Number(getQueryVariable("query_row"))
	if (query_row == ARIES.rowQueries.length - 1) {
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

ARIES.process_selected_file = function(file_text) {
	// make sure we get SOME text out of the selected file
	if (!file_text.length) {
		alert("When the ARIES module read your selected file, it couldn't find valid text data. Please select a .csv file to upload.")
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
	var validHeaderFound = ARIES.validCSVHeaders.some(function(valid_header) {
		return headers.includes(valid_header)
	})
	if (!validHeaderFound) {
		alert("The ARIES module couldn't find a valid header in the selected .csv file.\nPlease ensure the first csv row in your file contains at least one of the following:\n\n" + ARIES.validCSVHeaders.join("\n"))
		var input = $('.custom-file-label')
		input.html("Upload a CSV")
		input.val(null)
		return
	}
	
	// no errors, get a map of valid header names to column indices (e.g. "patient_dob" maps to csv column 3)
	var headerIndices = {}
	ARIES.validCSVHeaders.forEach(function(validHeader) {
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
	ARIES.rowQueries = rowQueries
	
	// store queries in local storage
	try {
		localStorage.setItem('aries_csv_search_row_queries', JSON.stringify(rowQueries))
		localStorage.setItem('aries_csv_filename', ARIES.filename)
	} catch(err) {
		alert("REDCap's ARIES module couldn't save the file to local (browser) storage -- try clearing your local storage or restarting your browser in non-private mode. Error message: " + String(err))
	}
	
	ARIES.processedFileThisSession = true
}

ARIES.prev_query = function() {
	ARIES.submit_row_query(Number(getQueryVariable("query_row")) - 1)
}

ARIES.next_query = function() {
	ARIES.submit_row_query(Number(getQueryVariable("query_row")) + 1)
}

ARIES.first_query = function() {
	ARIES.submit_row_query(0)
}

ARIES.last_query = function() {
	ARIES.submit_row_query(ARIES.rowQueries.length - 1)
}

// on document ready
$(function() {
	// autocomplete prediction stuff
	$("#search-input input").on('input', function() {
		clearTimeout(ARIES.predict_timer)
		$("#error_alert").hide()
		ARIES.predict_timer = setTimeout(ARIES.predictPatients, 500)
	})
	
	// forward to patient record on prediction clicked
	$("#autocomplete").on('click', 'span', function(e) {
		var span = $(e.target)
		if (span.hasClass('predict-name')) {
			span = span.parent('span')
		}
		var rid = span.attr('data-rid')
		if (rid) {
			window.location.href = ARIES.recordAddress + "&rid=" + rid + "&NOAUTH"
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
		ARIES.rowQueries = JSON.parse(localStorage.getItem("aries_csv_search_row_queries"))
		ARIES.filename = localStorage.getItem("aries_csv_filename")
	} catch(exception) {
		
	}
	
	// show search results (should be present if query parameters are set)
	var url_query = getQueryVariable('query');
	if (ARIES.search_results) {
		ARIES.shrinkSearch()
		$("#search-feedback").css('visibility', 'hidden')
		
		if (ARIES.use_file_interface) {
			ARIES.add_file_interface()
			var query_string = ""
			for(const i in ARIES.validCSVHeaders) {
				var field = ARIES.validCSVHeaders[i]
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
		
		if (ARIES.search_results.length) {
			ARIES.make_results_table(ARIES.search_results)
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
		window.location.href = ARIES.recordAddress + "&rid=" + rid + "&NOAUTH";
})

$('body').on('change', ".custom-file-input", function(e) {
	var fileName = $(this).val().split('\\').pop()
	ARIES.filename = fileName
	var input = $('.custom-file-label')
	input.html(fileName)
	
	if (e.target.files[0]) {
		ARIES.fileReader.readAsText(e.target.files[0])
	}
})

// load file into localstorage on upload
ARIES.fileReader.addEventListener("load", function() {
	ARIES.process_selected_file(this.result)
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