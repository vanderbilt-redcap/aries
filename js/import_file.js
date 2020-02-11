XDRO.import_file_done = function(response) {
	console.log('ajax received')
	console.log('import response', response)
}

XDRO.showMessage = function(txt, alert_class, wide) {
	//https://getbootstrap.com/docs/4.4/components/alerts/ see for alert_class types (primary, danger, warning, info etc)
	var alert = $("div#notes")
	alert.text(txt)
	alert.attr('class', "m-3 w-" + (wide ? "50" : "25") + " alert alert-" + alert_class)
}

$('body').on('click', '#import_file', function() {
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