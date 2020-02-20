XDRO.import_file_done = function(response) {
	console.log('import response', response)
	if (response.errors.length > 0) {
		var alertHtml = "<h6>Data import failed:</h6><br>"
		XDRO.showMessage(alertHtml + "<ul><li>" + response.errors.join('</li><li>') + "</li></ul>", 'danger')
	} else {
		XDRO.showImportResults(response.row_error_arrays)
	}
}

XDRO.reset = function() {
	// clear alert
	var alert = $("div#notes")
	alert.html("")
	alert.hide()
	
	// clear table results
	$("tbody").html("")
	$("#results_wrapper").hide()
}

XDRO.showImportResults = function(rows) {
	console.log('rows', rows)
	// rows.forEach(function(error_arr, index) {
	for (var index in rows) {
		var error_arr = rows[index]
		if (error_arr.length == 0) {
			var td = "Row imported"
		} else {
			var td = "<ul><li>" + error_arr.join('</li><li>') + "</li></ul>"
		}
		$("#results > tbody").append("<tr><td>" + index + "</td><td>" + td + "</td></tr>")
	}
	
	XDRO.results_table.columns.adjust()
	// XDRO.results_table.css('width', 'none')
	$("#results_wrapper").show()
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
		pageLength: 50
	});
	XDRO.reset()
})

$('body').on('click', '#submit_file', function() {
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