ARIES.import_file_done = function(response) {
	// update button
	$("button#submit_file").html('Import')
	$("button#submit_file").removeClass('btn-primary')
	$("button#submit_file").addClass('btn-primary-outline')
	$("button#submit_file").removeAttr('disabled')
	
	ARIES.response = response
	// console.log('import response', response)
	
	// show ignored columns
	if (response.ignored_cols) {
		ARIES.showIgnoredColumns(response.ignored_cols)
	}
	
	if (response.errors.length > 0) {
		var alertHtml = "<h6>Data import failed:</h6><br>"
		ARIES.showMessage(alertHtml + "<ul><li>" + response.errors.join('</li><li>') + "</li></ul>", 'danger')
	} else {
		// ARIES.showImportResults(response.row_error_arrays)
		ARIES.showImportResults(response.actions)
	}
}

ARIES.reset = function() {
	// clear alert
	var alert = $("div#notes")
	alert.html("")
	alert.hide()
	
	ARIES.hideIgnoredColumns()
	
	// clear table results
	// $("tbody").html("")
	ARIES.results_table.clear()
	$("#results_wrapper").hide()
}

ARIES.showImportResults = function(rows) {
	// console.log('rows', rows)
	
	ARIES.results_table.rows.add(rows)
	ARIES.results_table.columns.adjust()
	ARIES.results_table.draw()
	
	$("#results_wrapper").show()
}

ARIES.showIgnoredColumns = function(cols) {
	cols = Object.keys(cols)
	if (cols.length > 0) {
		cols.forEach(function(col) {
			$("#ignored_cols ul").append("<li>" + col + "</li>")
		})
		
		$("#ignored_cols").css('display', 'flex')
	}
}

ARIES.hideIgnoredColumns = function(cols) {
	$("#ignored_cols ul").empty()
	$("#ignored_cols").hide()
}

ARIES.showMessage = function(txt, alert_class, wide) {
	//https://getbootstrap.com/docs/4.4/components/alerts/ see for alert_class types (primary, danger, warning, info etc)
	var alert = $("div#notes")
	alert.html(txt)
	alert.attr('class', "m-3 w-" + (wide ? "50" : "25") + " alert alert-" + alert_class)
	alert.show()
}

///////////////////////
///////////////////////

$(function() {
	ARIES.results_table = $("table#results").DataTable({
		columnDefs: [
			{className: "dt-center", target: "_all"}
		],
		pageLength: 25
	});
	ARIES.reset()
})

$('body').on('click', '#submit_file', function() {
	// update button
	$(this).html('<span class="spinner-border spinner-border-sm mr-3"></span>Importing')
	$(this).removeClass('btn-primary-outline')
	$(this).addClass('btn-primary')
	$(this).attr('disabled', true)
	
	ARIES.reset()
	
	if (!$("#import_file").prop('files'))
		return
	if (!$("#import_file").prop('files')[0])
		return
	
	var form_data = new FormData()
	form_data.append('client_file', $("#import_file").prop('files')[0])
	$.ajax({
		type: "POST",
		url: ARIES.import_ajax_url,
		data: form_data,
		success: ARIES.import_file_done,
		dataType: 'json',
		cache: false,
		contentType: false,
		processData: false
	})
	// console.log('ajax sent to ' + ARIES.import_ajax_url)
})

$('body').on('change', ".custom-file-input", function() {
	var fileName = $(this).val().split('\\').pop()
	$('.custom-file-label').html(fileName)
})