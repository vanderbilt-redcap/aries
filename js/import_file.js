XDRO.import_file_done = function(response) {
	// update button
	$("button#submit_file").html('Import')
	$("button#submit_file").removeClass('btn-primary')
	$("button#submit_file").addClass('btn-primary-outline')
	$("button#submit_file").removeAttr('disabled')
	
	XDRO.response = response
	console.log('import response', response)
	
	// show ignored columns
	if (response.ignored_cols) {
		XDRO.showIgnoredColumns(response.ignored_cols)
	}
	
	if (response.errors.length > 0) {
		var alertHtml = "<h6>Data import failed:</h6><br>"
		XDRO.showMessage(alertHtml + "<ul><li>" + response.errors.join('</li><li>') + "</li></ul>", 'danger')
	} else {
		// XDRO.showImportResults(response.row_error_arrays)
		XDRO.showImportResults(response.actions)
	}
}

XDRO.reset = function() {
	// clear alert
	var alert = $("div#notes")
	alert.html("")
	alert.hide()
	
	XDRO.hideIgnoredColumns()
	
	// clear table results
	// $("tbody").html("")
	XDRO.results_table.clear()
	$("#results_wrapper").hide()
}

XDRO.showImportResults = function(rows) {
	console.log('rows', rows)
	
	XDRO.results_table.rows.add(rows)
	XDRO.results_table.columns.adjust()
	XDRO.results_table.draw()
	
	$("#results_wrapper").show()
}

XDRO.showIgnoredColumns = function(cols) {
	cols = Object.keys(cols)
	if (cols.length > 0) {
		cols.forEach(function(col) {
			$("#ignored_cols ul").append("<li>" + col + "</li>")
		})
		
		$("#ignored_cols").css('display', 'flex')
	}
}

XDRO.hideIgnoredColumns = function(cols) {
	$("#ignored_cols ul").empty()
	$("#ignored_cols").hide()
}

XDRO.showMessage = function(txt, alert_class, wide) {
	//https://getbootstrap.com/docs/4.4/components/alerts/ see for alert_class types (primary, danger, warning, info etc)
	var alert = $("div#notes")
	alert.html(txt)
	alert.attr('class', "m-3 w-" + (wide ? "50" : "25") + " alert alert-" + alert_class)
	alert.show()
}

///////////////////////
///////////////////////

$(function() {
	XDRO.results_table = $("table#results").DataTable({
		columnDefs: [
			{className: "dt-center", target: "_all"}
		],
		pageLength: 25
	});
	XDRO.reset()
})

$('body').on('click', '#submit_file', function() {
	// update button
	$(this).html('<span class="spinner-border spinner-border-sm mr-3"></span>Importing')
	$(this).removeClass('btn-primary-outline')
	$(this).addClass('btn-primary')
	$(this).attr('disabled', true)
	
	XDRO.reset()
	
	if (!$("#import_file").prop('files'))
		return
	if (!$("#import_file").prop('files')[0])
		return
	
	var form_data = new FormData()
	form_data.append('client_file', $("#import_file").prop('files')[0])
	$.ajax({
		type: "POST",
		url: XDRO.import_ajax_url,
		data: form_data,
		success: XDRO.import_file_done,
		dataType: 'json',
		cache: false,
		contentType: false,
		processData: false
	})
	console.log('ajax sent to ' + XDRO.import_ajax_url)
})

$('body').on('change', ".custom-file-input", function() {
	var fileName = $(this).val().split('\\').pop()
	$('.custom-file-label').html(fileName)
})